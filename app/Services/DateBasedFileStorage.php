<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DateBasedFileStorage
{
    /**
     * Base storage path
     */
    private string $basePath;

    /**
     * Storage disk to use
     */
    private string $disk;

    public function __construct(string $basePath = 'files', string $disk = 'local')
    {
        $this->basePath = rtrim($basePath, '/');
        $this->disk = $disk;
    }

    /**
     * Create a date-based directory structure and return the path
     * Format: basePath/YYYY/MM/DD
     *
     * @param Carbon|null $date If null, uses current date
     * @return string The created directory path
     */
    public function createDateDirectory(?Carbon $date = null): string
    {
        if ($date === null) {
            $date = Carbon::now();
        }

        $directoryPath = $this->getDatePath($date);

        // Create the directory if it doesn't exist
        if (!Storage::disk($this->disk)->exists($directoryPath)) {
            Storage::disk($this->disk)->makeDirectory($directoryPath, 0755, true);
        }

        return $directoryPath;
    }

    /**
     * Get the date-based path without creating it
     *
     * @param Carbon|null $date
     * @return string
     */
    public function getDatePath(?Carbon $date = null): string
    {
        if ($date === null) {
            $date = Carbon::now();
        }

        return sprintf(
            '%s/%04d/%02d/%02d',
            $this->basePath,
            $date->year,
            $date->month,
            $date->day
        );
    }

    /**
     * Create directory and return full file path for a given filename
     *
     * @param string $filename
     * @param Carbon|null $date
     * @return string Full path including filename
     */
    public function getFilePathWithDirectory(string $filename, ?Carbon $date = null): string
    {
        $directoryPath = $this->createDateDirectory($date);
        return $directoryPath . '/' . $filename;
    }

    /**
     * Get the absolute filesystem path (useful for direct file operations)
     *
     * @param Carbon|null $date
     * @return string
     */
    public function getAbsolutePath(?Carbon $date = null): string
    {
        $relativePath = $this->createDateDirectory($date);
        return Storage::disk($this->disk)->path($relativePath);
    }

    /**
     * Create directory structure for a specific month (useful for bulk operations)
     * Creates all days for the given month
     *
     * @param Carbon $date
     * @return array Array of created directory paths
     */
    public function createMonthDirectories(Carbon $date): array
    {
        $createdPaths = [];
        $daysInMonth = $date->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayDate = $date->copy()->day($day);
            $createdPaths[] = $this->createDateDirectory($dayDate);
        }

        return $createdPaths;
    }
}

