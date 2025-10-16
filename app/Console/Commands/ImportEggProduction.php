<?php

namespace App\Console\Commands;

use App\Models\EggDailyProduction;
use App\Models\EggDispatch;
use App\Models\Flock;
use App\Models\Farm;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportEggProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:egg-production {file} {--flock-id=} {--farm-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import egg production and dispatch records from CSV file';

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

        // Get or prompt for farm_id (needed for dispatches)
        $farmId = $this->option('farm-id');

        if (!$farmId) {
            $farms = Farm::all();

            if ($farms->isEmpty()) {
                $this->error('No farms found in the database. Please create a farm first.');
                return 1;
            }

            $this->info('Available farms:');
            foreach ($farms as $farm) {
                $this->line("ID: {$farm->id} - {$farm->name}");
            }

            $farmId = $this->ask('Enter the farm ID for dispatch records');
        }

        // Verify farm exists
        $farm = Farm::find($farmId);
        if (!$farm) {
            $this->error("Farm with ID {$farmId} not found.");
            return 1;
        }

        $this->info("Importing records for flock batch: {$flock->batch_number}");
        $this->info("Dispatches will be associated with farm: {$farm->name}");

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

        $productionImported = 0;
        $dispatchesImported = 0;
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

                    // Check if production record already exists
                    $existingProduction = EggDailyProduction::where('flock_id', $flockId)
                        ->where('date', $date->format('Y-m-d'))
                        ->first();

                    if ($existingProduction) {
                        $skipped++;
                        continue;
                    }

                    // Create egg production record
                    $production = EggDailyProduction::create([
                        'flock_id' => $flockId,
                        'date' => $date->format('Y-m-d'),
                        'opening_stock' => (int)$row[1], // OPENING STOCK
                        'eggs_produced' => (int)$row[2], // EGGS PRODUCED
                        'damaged' => (int)$row[3],       // DAMAGED
                        'closing_stock' => (int)$row[5], // CLOSING STOCK
                    ]);

                    $productionImported++;

                    // Create dispatch record if there are dispatches
                    $dispatched = (int)$row[4]; // DISPATCHED
                    if ($dispatched > 0) {
                        // Check if dispatch already exists for this date
                        $existingDispatch = EggDispatch::where('farm_id', $farmId)
                            ->where('date', $date->format('Y-m-d'))
                            ->where('quantity', $dispatched)
                            ->exists();

                        if (!$existingDispatch) {
                            EggDispatch::create([
                                'farm_id' => $farmId,
                                'date' => $date->format('Y-m-d'),
                                'quantity' => $dispatched,
                                'dispatch_type' => 'sale',
                                'dispatch_reason' => 'imported_from_csv',
                                'recipient_name' => 'To be updated',
                            ]);

                            $dispatchesImported++;
                        }
                    }

                } catch (\Exception $e) {
                    $errors[] = "Error on row " . ($productionImported + $skipped + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();
            fclose($file);

            $this->info("Import completed!");
            $this->line("Production records imported: {$productionImported}");
            $this->line("Dispatch records imported: {$dispatchesImported}");

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
