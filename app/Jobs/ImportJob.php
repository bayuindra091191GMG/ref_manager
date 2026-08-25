<?php

namespace App\Jobs;

use App\Imports\UserImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */

    private $filePath;
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fullPath = storage_path('app/public/' . $this->filePath);

        // Periksa apakah file ada
        if (!file_exists($fullPath)) {
            Log::error("File tidak ditemukan: " . $fullPath);
            return;
        }

        Excel::import(new UserImport, $fullPath);

        // Hapus file setelah diimport (Opsional)
        Storage::disk('public')->delete($this->filePath);
    }
}
