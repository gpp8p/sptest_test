<?php

namespace App\Classes;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DocumentConverter
{
    public function convertDocToHtml($inputPath, $outputDir = null)
    {
        $outputDir = $outputDir ?: storage_path('app/converted');

        // Ensure output directory exists
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $command = [
            '/Applications/LibreOffice.app/Contents/MacOS/soffice',
            '--headless',
            '--convert-to',
            'html',
            '--outdir',
            $outputDir,
            $inputPath
        ];

        $process = new Process($command);
        $process->setTimeout(300); // 5 minute timeout

        try {
            $process->mustRun();

            // Return path to converted file
            $basename = pathinfo($inputPath, PATHINFO_FILENAME);
            return $outputDir . '/' . $basename . '.html';

        } catch (ProcessFailedException $exception) {
            throw new \Exception('Document conversion failed: ' . $exception->getMessage());
        }
    }
}
