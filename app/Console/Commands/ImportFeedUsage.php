<?php

namespace App\Console\Commands;

use App\Models\FeedType;
use App\Models\FeedInventory;
use App\Models\FeedDailyUsage;
use App\Models\FeedReceipt;
use App\Models\Flock;
use App\Models\Farm;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportFeedUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:feed-usage {file} {--flock-id=} {--farm-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import feed usage records from CSV file';

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

        // Get or prompt for farm_id
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

            $farmId = $this->ask('Enter the farm ID for feed inventory');
        }

        // Verify farm exists
        $farm = Farm::find($farmId);
        if (!$farm) {
            $this->error("Farm with ID {$farmId} not found.");
            return 1;
        }

        // Check if Layer Mash feed type exists, create if not
        $feedType = FeedType::firstOrCreate(
            ['name' => 'Layer Mash'],
            ['description' => 'Layer Mash feed for laying hens']
        );

        $this->info("Using feed type: {$feedType->name} (ID: {$feedType->id})");

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

        $usageImported = 0;
        $receiptsImported = 0;
        $skipped = 0;
        $errors = [];
        $inventoryCreated = false;

        DB::beginTransaction();

        try {
            // Check if inventory record exists
            $inventory = FeedInventory::where('feed_type_id', $feedType->id)
                ->where('farm_id', $farmId)
                ->first();

            while (($row = fgetcsv($file)) !== false) {
                if (empty($row[0])) {
                    continue; // Skip empty rows
                }

                try {
                    // Parse the date - handle d-M-y format (e.g., 11-Aug-25)
                    $date = Carbon::createFromFormat('d-M-y', $row[0]);
                    $openingStock = (float)$row[1];
                    $received = (float)$row[2];
                    $used = (float)$row[3];
                    $closingStock = (float)$row[4];

                    // Create inventory record if this is the first row and inventory doesn't exist
                    if (!$inventory && !$inventoryCreated) {
                        $inventory = FeedInventory::create([
                            'feed_type_id' => $feedType->id,
                            'farm_id' => $farmId,
                            'current_stock' => $openingStock,
                            'reorder_level' => 100, // Default reorder level
                        ]);
                        $inventoryCreated = true;
                        $this->info("Created initial inventory record with opening stock: {$openingStock}");
                    }

                    // Create feed receipt if there was a delivery
                    if ($received > 0) {
                        $existingReceipt = FeedReceipt::where('feed_type_id', $feedType->id)
                            ->where('farm_id', $farmId)
                            ->where('date', $date->format('Y-m-d'))
                            ->where('quantity', $received)
                            ->exists();

                        if (!$existingReceipt) {
                            FeedReceipt::create([
                                'feed_type_id' => $feedType->id,
                                'farm_id' => $farmId,
                                'date' => $date->format('Y-m-d'),
                                'quantity' => $received,
                                'supplier' => 'Imported from CSV',
                            ]);
                            $receiptsImported++;
                        }
                    }

                    // Check if usage record already exists
                    $existingUsage = FeedDailyUsage::where('flock_id', $flockId)
                        ->where('feed_type_id', $feedType->id)
                        ->where('date', $date->format('Y-m-d'))
                        ->exists();

                    if ($existingUsage) {
                        $skipped++;
                        continue;
                    }

                    // Create feed usage record with stock columns
                    FeedDailyUsage::create([
                        'flock_id' => $flockId,
                        'feed_type_id' => $feedType->id,
                        'date' => $date->format('Y-m-d'),
                        'opening_stock' => $openingStock,
                        'received' => $received,
                        'quantity_used' => $used,
                        'closing_stock' => $closingStock,
                    ]);

                    $usageImported++;

                } catch (\Exception $e) {
                    $errors[] = "Error on row " . ($usageImported + $skipped + 2) . ": " . $e->getMessage();
                }
            }

            // Update final inventory stock to match last closing stock
            if ($inventory && $closingStock) {
                $inventory->update(['current_stock' => $closingStock]);
                $this->info("Updated inventory closing stock to: {$closingStock}");
            }

            DB::commit();
            fclose($file);

            $this->info("Import completed!");
            $this->line("Feed usage records imported: {$usageImported}");
            $this->line("Feed receipts imported: {$receiptsImported}");

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
