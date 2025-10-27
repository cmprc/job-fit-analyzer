<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CandidateController extends Controller
{
    private $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Display a listing of candidates
     */
    public function index(): JsonResponse
    {
        $candidates = Candidate::with(['analyses.job'])->get();
        return response()->json($candidates);
    }

    /**
     * Store a newly created candidate
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'pdf' => 'required|file|mimes:pdf|max:10240' // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $pdfData = $this->pdfService->processPdf($request->file('pdf'), 'candidates');

            $candidate = Candidate::create([
                'name' => $request->name,
                'email' => $request->email,
                'pdf_path' => $pdfData['path'],
                'extracted_text' => $pdfData['text']
            ]);

            return response()->json($candidate, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified candidate
     */
    public function show($id): JsonResponse
    {
        $candidate = Candidate::with(['analyses.job'])->findOrFail($id);
        return response()->json($candidate);
    }

    /**
     * Update the specified candidate
     */
    public function update(Request $request, $id): JsonResponse
    {
        $candidate = Candidate::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $candidate->update($request->only(['name', 'email']));
        return response()->json($candidate);
    }

    /**
     * Remove the specified candidate
     */
    public function destroy($id): JsonResponse
    {
        $candidate = Candidate::findOrFail($id);
        $candidate->delete();

        return response()->json(['message' => 'Candidate deleted successfully']);
    }
}
