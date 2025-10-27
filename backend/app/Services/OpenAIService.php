<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private $apiKey;
    private $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
    }

    /**
     * Analyze resume against job description
     */
    public function analyzeResume(string $jobDescription, string $resumeText): array
    {
        if (!$this->apiKey) {
            throw new \Exception('OpenAI API key not configured');
        }

        $prompt = $this->buildAnalysisPrompt($jobDescription, $resumeText);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert recruiter and HR professional. Analyze resumes against job descriptions and provide honest, constructive feedback.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 1000,
                'temperature' => 0.3
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'];
                return $this->parseAnalysisResponse($content);
            } else {
                throw new \Exception('OpenAI API request failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build the analysis prompt
     */
    private function buildAnalysisPrompt(string $jobDescription, string $resumeText): string
    {
        return "Please analyze this resume against the job description and provide:

JOB DESCRIPTION:
{$jobDescription}

RESUME:
{$resumeText}

Please provide your analysis in the following JSON format:
{
    \"fit_score\": [number between 0-100],
    \"strengths\": [array of 3-5 key strengths],
    \"weaknesses\": [array of 3-5 key weaknesses],
    \"analysis_details\": \"Brief summary of the analysis\"
}

Focus on:
- Technical skills alignment
- Experience relevance
- Education requirements
- Soft skills match
- Overall fit for the role

Be honest and constructive in your assessment.";
    }

    /**
     * Parse the OpenAI response
     */
    private function parseAnalysisResponse(string $content): array
    {
        // Try to extract JSON from the response
        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}') + 1;

        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($content, $jsonStart, $jsonEnd - $jsonStart);
            $decoded = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Fallback: create a basic response if JSON parsing fails
        return [
            'fit_score' => 50,
            'strengths' => ['Resume submitted successfully'],
            'weaknesses' => ['Analysis incomplete - manual review needed'],
            'analysis_details' => 'Unable to parse AI analysis. Please review manually.'
        ];
    }
}
