<?php

namespace App\Http\Controllers;

use App\Http\Resources\ResumeResource;
use App\Models\Candidate;
use App\Models\Resume;
use App\Repositories\Resume\ResumeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\Candidate\CandidateStatus;

class ResumeController extends Controller
{
    protected ResumeRepositoryInterface $resumeRepository;

    public function __construct(ResumeRepositoryInterface $resumeRepository) {
        $this->resumeRepository = $resumeRepository;
    }

    public function index()
    {
        $candidate = Auth::user()->candidate;
        if (!$candidate) {
            Auth::user()->candidate()->create([
                'status' => CandidateStatus::NEW,
            ]);
            Auth::user()->refresh();
            $candidate = Auth::user()->candidate;
        }
        $resumes = $candidate->resumes;

        return ResumeResource::collection($resumes);
    }

    public function store(Request $request)
    {
        $resume = $this->resumeRepository->create([
            'candidate_id' => Auth::user()->candidate->id,
            'template_id' => $request->input('resumeTemplateId'),
        ]);

        return ResumeResource::make($resume);
    }

    public function show(Resume $resume)
    {
        return ResumeResource::make($resume);
    }

    public function destroy(Resume $resume)
    {
        return $resume->delete();
    }

    public function saveContent(Request $request, Resume $resume)
    {
        $resume->update([
            'content' => json_encode($request->all()),
        ]);

        return response()->json(json_decode($resume->content));
    }

    public function loadContent(Resume $resume)
    {
        return response()->json(json_decode($resume->content));
    }
}
