<?php

namespace App\Http\Controllers\Job;

use App\Enums\Candidate\CandidateStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GenAIController;
use App\Http\Requests\Job\StoreCandidateRequest;
use App\Http\Resources\CandidateResource;
use App\Models\Education;
use App\Models\Job;
use App\Repositories\Candidate\CandidateRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CreateCandidateController extends Controller
{
    protected CandidateRepositoryInterface $candidateRepository;

    public function __construct(CandidateRepositoryInterface $candidateRepository)
    {
        $this->candidateRepository = $candidateRepository;
    }

    /**
     * Xử lý ứng viên tự nộp hồ sơ
     * Controller này được sử dụng khi ứng viên tự đăng nhập và nộp CV của họ
     * vì vậy, tài khoản Auth::user() ở đây chính là tài khoản của ứng viên
     */
    public function __invoke(StoreCandidateRequest $request, Job $job)
    {
        try {
            DB::beginTransaction();

            $file = $request->file('resume');
            $filePath = $file->store('public/resumes');
            // $file = $request->file('resume');
            // $filePath = $file->store('uploads', 'public');
            $authUser = optional(Auth::user());
            $pathResume = Storage::url($filePath);
            $candidate = Auth::user()->candidate;
            $response = GenAIController::sendToGenerativeAI($file);
            // return $response;
            $educationData = $response['education'] ?? [];
            $workExperienceData = $response['work_experience'] ?? [];
            $personalInfo = $response['personal_info'] ?? [];

            $authUser->update([
                // 'name' => $personalInfo['full_name'],
                // 'email' =>  $personalInfo['email'],
                'phone_number' => $personalInfo['phone_number'],
                'gender' => $personalInfo['gender'],
                'address' => $personalInfo['address'],
            ]);
            
            // Tạo candidate trước nếu chưa tồn tại
            if (!$authUser->candidate) {
                $authUser->candidate()->create([
                    'resume_url' => $pathResume,
                    'status' => CandidateStatus::NEW,
                ]);
                // Refresh để lấy candidate sau khi tạo
                $authUser->refresh();
                $candidate = $authUser->candidate;
            } else {
                $authUser->candidate->update([
                    'resume_url' => $pathResume,
                    'status' => CandidateStatus::NEW,
                ]);
            }

            if (!empty($educationData)) {
                foreach ($educationData as $edu) {
                    $startDate = null;
                    $endDate = null;
                    try {
                        $startDate = Carbon::parse($edu['start_date'] ?? '')->toDateString();
                        $endDate = Carbon::parse($edu['end_date'] ?? '')->toDateString();
                    } catch (Exception $e) {
                    }

                    Education::create([
                        'school_name' => $edu['school_name'] ?? 'Unknown School',
                        'field_of_study' => $edu['field_of_study'] ?? 'General Studies',
                        'degree' => $edu['degree'] ?? 'No Degree Specified',
                        'grade' => $edu['grade'] ?? 'Not Available',
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'candidate_id' => $candidate->id,
                    ]);
                }
            }

            if (!empty($workExperienceData)) {
                foreach ($workExperienceData as $workExperience) {
                    $startDate = null;
                    $endDate = null;
                    try {
                        $startDate = Carbon::parse($workExperience['start_date'] ?? '')->toDateString();
                        $endDate = Carbon::parse($workExperience['end_date'] ?? '')->toDateString();
                    } catch (Exception $e) {
                    }

                    $candidate->experiences()->create(
                        [
                            'company_name' => $workExperience['company_name'] ?? 'Unknown Company',
                            'position' => $workExperience['position'] ?? 'Unknown Position',
                            'summary' => $workExperience['summary'] ?? 'No details provided',
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'candidate_id' => $candidate->id,
                        ]
                    );
                }
            }

            $stage = optional($job->pipeline)->stages[0];
            
            // Đánh dấu tất cả candidate_job cũ là không active
            DB::table('candidate_jobs')
                ->where('candidate_id', $candidate->id)
                ->where('job_id', $job->id)
                ->update(['is_active' => false]);
                
            // Tạo bản ghi mới với is_active = true
            $job->candidateJobs()->create([
                'candidate_id' => $candidate->id,
                'job_id' => $job->id,
                'stage_id' => optional($stage)->id,
                'is_active' => true
            ]);

            DB::commit();
            return CandidateResource::make($candidate->load('user'));
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }
    }
}
