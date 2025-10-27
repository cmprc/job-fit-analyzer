<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    private $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Extract text from PDF file
     */
    public function extractText(UploadedFile $file): string
    {
        try {
            $pdf = $this->parser->parseFile($file->getPathname());
            return $pdf->getText();
        } catch (\Exception $e) {
            throw new \Exception('Failed to extract text from PDF: ' . $e->getMessage());
        }
    }

    /**
     * Store PDF file and return the path
     */
    public function storePdf(UploadedFile $file, string $directory = 'pdfs'): string
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs($directory, $filename, 'public');
        return $path;
    }

    /**
     * Process PDF file: store it and extract text
     */
    public function processPdf(UploadedFile $file, string $directory = 'pdfs'): array
    {
        $path = $this->storePdf($file, $directory);
        $text = $this->extractText($file);

        return [
            'path' => $path,
            'text' => $text
        ];
    }
}
