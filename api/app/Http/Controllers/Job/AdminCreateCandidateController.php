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
            // $response = GenAIController::sendToGenerativeAI($file);
            // return $response;
            $educationData = $response['education'] ?? [];
            $workExperienceData = $response['work_experience'] ?? [];
            $personalInfo = $response['personal_info'] ?? [];

            $authUser->update([
                'name' => $request['name'],
                'email' =>  $request['email'],
                'phone_number' => $request['phoneNumber'],
            ]);

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
            }

            if (!$authUser->candidate) {
                $authUser->candidate()->create([
                    'resume_url' => $pathResume,
                    'status' => CandidateStatus::NEW,
                ]);
            }

            $stage = optional($job->pipeline)->stages[0];
            $job->candidateJobs()->create([
                'candidate_id' => $authUser->candidate->id,
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
