<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Analysis;
use App\Models\Job;
use App\Models\Candidate;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AnalysisController extends Controller
{
    private $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Display a listing of analyses for a specific job
     */
    public function index(Request $request): JsonResponse
    {
        $jobId = $request->query('job_id');

        if (!$jobId) {
            return response()->json(['error' => 'job_id parameter is required'], 400);
        }

        $analyses = Analysis::with(['candidate', 'job'])
            ->where('job_id', $jobId)
            ->orderBy('fit_score', 'desc')
            ->get();

        return response()->json($analyses);
    }

    /**
     * Analyze a candidate against a job
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id',
            'candidate_id' => 'required|exists:candidates,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $job = Job::findOrFail($request->job_id);
            $candidate = Candidate::findOrFail($request->candidate_id);

            // Check if analysis already exists
            $existingAnalysis = Analysis::where('job_id', $job->id)
                ->where('candidate_id', $candidate->id)
                ->first();

            if ($existingAnalysis) {
                return response()->json($existingAnalysis);
            }

            // Perform AI analysis
            $analysisResult = $this->openAIService->analyzeResume(
                $job->extracted_text,
                $candidate->extracted_text
            );

            // Create analysis record
            $analysis = Analysis::create([
                'job_id' => $job->id,
                'candidate_id' => $candidate->id,
                'fit_score' => $analysisResult['fit_score'],
                'strengths' => json_encode($analysisResult['strengths']),
                'weaknesses' => json_encode($analysisResult['weaknesses']),
                'analysis_details' => $analysisResult['analysis_details']
            ]);

            $analysis->load(['candidate', 'job']);

            return response()->json($analysis, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Analyze all candidates for a job
     */
    public function analyzeAll(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $job = Job::findOrFail($request->job_id);
            $candidates = Candidate::all();

            if ($candidates->isEmpty()) {
                return response()->json(['error' => 'No candidates available for analysis'], 400);
            }

            $analyses = [];
            $newAnalysesCount = 0;

            // Get all existing analyses for this job in one query
            $existingAnalyses = Analysis::where('job_id', $job->id)
                ->with(['candidate', 'job'])
                ->get()
                ->keyBy('candidate_id');

            foreach ($candidates as $candidate) {
                // Check if analysis already exists
                if ($existingAnalyses->has($candidate->id)) {
                    $analyses[] = $existingAnalyses[$candidate->id];
                    continue;
                }

                try {
                    // Perform AI analysis
                    $analysisResult = $this->openAIService->analyzeResume(
                        $job->extracted_text,
                        $candidate->extracted_text
                    );

                    // Create analysis record
                    $analysis = Analysis::create([
                        'job_id' => $job->id,
                        'candidate_id' => $candidate->id,
                        'fit_score' => $analysisResult['fit_score'],
                        'strengths' => json_encode($analysisResult['strengths']),
                        'weaknesses' => json_encode($analysisResult['weaknesses']),
                        'analysis_details' => $analysisResult['analysis_details']
                    ]);

                    $analysis->load(['candidate', 'job']);
                    $analyses[] = $analysis;
                    $newAnalysesCount++;
                } catch (\Exception $e) {
                    // Log the error but continue with other candidates
                    Log::error("Failed to analyze candidate {$candidate->id}: " . $e->getMessage());

                    // Create a fallback analysis record
                    $analysis = Analysis::create([
                        'job_id' => $job->id,
                        'candidate_id' => $candidate->id,
                        'fit_score' => 0,
                        'strengths' => json_encode(['Analysis failed']),
                        'weaknesses' => json_encode(['Unable to analyze']),
                        'analysis_details' => 'Analysis failed: ' . $e->getMessage()
                    ]);

                    $analysis->load(['candidate', 'job']);
                    $analyses[] = $analysis;
                }
            }

            // Sort by fit score
            usort($analyses, function($a, $b) {
                return $b->fit_score <=> $a->fit_score;
            });

            return response()->json([
                'analyses' => $analyses,
                'summary' => [
                    'total_candidates' => $candidates->count(),
                    'new_analyses' => $newAnalysesCount,
                    'existing_analyses' => $candidates->count() - $newAnalysesCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified analysis
     */
    public function show($id): JsonResponse
    {
        $analysis = Analysis::with(['candidate', 'job'])->findOrFail($id);
        return response()->json($analysis);
    }

    /**
     * Remove the specified analysis
     */
    public function destroy($id): JsonResponse
    {
        $analysis = Analysis::findOrFail($id);
        $analysis->delete();

        return response()->json(['message' => 'Analysis deleted successfully']);
    }
}
