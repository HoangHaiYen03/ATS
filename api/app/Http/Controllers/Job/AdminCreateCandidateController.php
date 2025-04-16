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
use App\Models\User;

class AdminCreateCandidateController extends Controller
{
    protected CandidateRepositoryInterface $candidateRepository;

    public function __construct(CandidateRepositoryInterface $candidateRepository)
    {
        $this->candidateRepository = $candidateRepository;
    }

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

            // $authUser->update([
            //     'name' => $request['name'],
            //     'email' =>  $request['email'],
            //     'phone_number' => $request['phoneNumber'],
            // ]);

            $newUser = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => bcrypt('Admin@123'),
                'phone_number' => $request['phoneNumber'],
            ]);

            $newUser->update([
                'phone_number' => $personalInfo['phone_number'],
                'gender' => $personalInfo['gender'],
                'address' => $personalInfo['address'],
            ]);

            $newUser->candidate()->create([
                'resume_url' => $pathResume,
                'status' => CandidateStatus::NEW,
            ]);

            $candidate = $newUser->candidate;

            if (!empty($educationData)) {
                foreach ($educationData as $edu) {
                    $startDate = null;
                    $endDate = null;
                    try {
                        $startDate = Carbon::parse($workExperience['start_date'] ?? '')->toDateString();
                        $endDate = Carbon::parse($workExperience['end_date'] ?? '')->toDateString();
                    } catch (Exception $e) {
                    }
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

            // if (!$authUser->candidate) {
            //     $authUser->candidate()->create([
            //         'resume_url' => $pathResume,
            //         'status' => CandidateStatus::NEW,
            //     ]);
            // }

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
            $job->candidateJobs()->create([
                'candidate_id' => $newUser->candidate->id,
                'job_id' => $job->id,
                'stage_id' => optional($stage)->id,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();

            throw $e;
        }
    }
}
