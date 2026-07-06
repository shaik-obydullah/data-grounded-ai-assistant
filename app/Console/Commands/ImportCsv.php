<?php

namespace App\Console\Commands;

use App\Services\CsvImportService;
use Illuminate\Console\Command;

class ImportCsv extends Command
{
    protected $signature = 'csv:import {file : Path to CSV file}';
    protected $description = 'Import a CSV file into the companies table';

    public function handle(CsvImportService $service): int
    {
        $path = $this->argument('file');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return Command::FAILURE;
        }

        $this->info("Importing {$path}...");
        $start = microtime(true);

        try {
            $import = \App\Models\CsvImport::create([
                'filename' => basename($path),
                'file_path' => $path,
                'status' => 'processing',
                'progress' => 0,
            ]);
            $import = $service->import($path, $import);
            $elapsed = round(microtime(true) - $start, 2);
            $this->info("Done in {$elapsed}s");
            $this->table(['Metric', 'Value'], [
                ['Total', $import->total_rows],
                ['New', $import->new_rows],
                ['Updated', $import->updated_rows],
                ['Removed', $import->removed_rows],
                ['Unchanged', $import->unchanged_rows],
            ]);
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error(get_class($e) . ': ' . $e->getMessage());
            $this->error('Line: ' . $e->getLine() . ' in ' . $e->getFile());
            return Command::FAILURE;
        }
    }
}
