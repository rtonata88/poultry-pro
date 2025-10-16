<?php

namespace App\Console\Commands;

use App\Models\BirdDailyRecord;
use App\Models\Flock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportBirdRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:bird-records {file} {--flock-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import bird daily records from CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        // Get or prompt for flock_id
        $flockId = $this->option('flock-id');

        if (!$flockId) {
            $flocks = Flock::all();

            if ($flocks->isEmpty()) {
                $this->error('No flocks found in the database. Please create a flock first.');
                return 1;
            }

            $this->info('Available flocks:');
            foreach ($flocks as $flock) {
                $this->line("ID: {$flock->id} - Batch: {$flock->batch_number} - Breed: {$flock->breed} - Placed: {$flock->placement_date->format('Y-m-d')}");
            }

            $flockId = $this->ask('Enter the flock ID to associate with these records');
        }

        // Verify flock exists
        $flock = Flock::find($flockId);
        if (!$flock) {
            $this->error("Flock with ID {$flockId} not found.");
            return 1;
        }

        $this->info("Importing records for flock batch: {$flock->batch_number}");

        // Read and parse CSV
        $file = fopen($filePath, 'r');

        // Skip BOM if present
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }

        // Read header
        $header = fgetcsv($file);

        if (!$header) {
            $this->error('Failed to read CSV header.');
            fclose($file);
            return 1;
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($file)) !== false) {
                if (empty($row[0])) {
                    continue; // Skip empty rows
                }

                try {
                    // Parse the date - handle d-M-y format (e.g., 11-Aug-25)
                    $date = Carbon::createFromFormat('d-M-y', $row[0]);

                    // Check if record already exists
                    $exists = BirdDailyRecord::where('flock_id', $flockId)
                        ->where('date', $date->format('Y-m-d'))
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    BirdDailyRecord::create([
                        'flock_id' => $flockId,
                        'date' => $date->format('Y-m-d'),
                        'age_in_weeks' => (int)$row[1],
                        'opening_stock' => (int)$row[2],
                        'mortality' => (int)$row[3],
                        'closing_stock' => (int)$row[4],
                        'culled' => 0,
                        'sold' => 0,
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Error on row " . ($imported + $skipped + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();
            fclose($file);

            $this->info("Import completed!");
            $this->line("Imported: {$imported} records");

            if ($skipped > 0) {
                $this->line("Skipped: {$skipped} records (already exist)");
            }

            if (!empty($errors)) {
                $this->warn("Errors encountered:");
                foreach ($errors as $error) {
                    $this->error($error);
                }
            }

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($file);
            $this->error("Import failed: " . $e->getMessage());
            return 1;
        }
    }
}
