<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    private $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Display a listing of jobs
     */
    public function index(): JsonResponse
    {
        $jobs = Job::with(['analyses.candidate'])->get();
        return response()->json($jobs);
    }

    /**
     * Store a newly created job
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pdf' => 'required|file|mimes:pdf|max:10240' // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $pdfData = $this->pdfService->processPdf($request->file('pdf'), 'jobs');

            $job = Job::create([
                'title' => $request->title,
                'description' => $request->description,
                'pdf_path' => $pdfData['path'],
                'extracted_text' => $pdfData['text']
            ]);

            return response()->json($job, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified job
     */
    public function show($id): JsonResponse
    {
        $job = Job::with(['analyses.candidate'])->findOrFail($id);
        return response()->json($job);
    }

    /**
     * Update the specified job
     */
    public function update(Request $request, $id): JsonResponse
    {
        $job = Job::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $job->update($request->only(['title', 'description']));
        return response()->json($job);
    }

    /**
     * Remove the specified job
     */
    public function destroy($id): JsonResponse
    {
        $job = Job::findOrFail($id);
        $job->delete();

        return response()->json(['message' => 'Job deleted successfully']);
    }
}
