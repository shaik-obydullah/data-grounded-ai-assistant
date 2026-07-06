<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CsvImport;
use Illuminate\Support\Facades\DB;

class CsvImportService
{
    public function import(string $filePath, CsvImport $import): CsvImport
    {
        set_time_limit(600);

        $import->update(['status' => 'processing', 'progress' => 0]);

        $csvChecksum = md5_file($filePath);

        $existingImport = CsvImport::where('checksum', $csvChecksum)->where('id', '!=', $import->id)->latest()->first();
        $existingChecksums = [];
        if ($existingImport) {
            $existingChecksums = Company::where('csv_import_id', $existingImport->id)
                ->pluck('csv_checksum')
                ->toArray();
            $existingChecksums = array_flip($existingChecksums);
        }

        $existingNames = Company::pluck('id', 'organisation_name')->toArray();

        $totalRows = $this->countRows($filePath);
        $import->update(['total_rows' => $totalRows]);

        $newRows = 0;
        $updatedRows = 0;
        $unchangedRows = 0;
        $currentChecksums = [];
        $toInsert = [];

        $handle = fopen($filePath, 'r');
        $headers = $this->parseHeaders(fgetcsv($handle, escape: ''));

        $batchSize = 500;
        $progressInterval = max(1, (int)($totalRows / 100));
        $count = 0;

        DB::beginTransaction();

        try {
            while (($data = fgetcsv($handle, escape: '')) !== false) {
                $row = $this->mapRow($headers, $data);
                if (empty($row['organisation_name'])) {
                    continue;
                }

                $rowChecksum = md5(json_encode($row));
                $currentChecksums[] = $rowChecksum;

                if (isset($existingChecksums[$rowChecksum])) {
                    Company::where('csv_checksum', $rowChecksum)
                        ->where('csv_import_id', $existingImport->id)
                        ->update(['change_type' => 'unchanged']);
                    $unchangedRows++;
                } elseif (isset($existingNames[$row['organisation_name']])) {
                    Company::where('organisation_name', $row['organisation_name'])
                        ->update(array_merge($row, [
                            'csv_checksum' => $rowChecksum,
                            'change_type' => 'updated',
                        ]));
                    $updatedRows++;
                } else {
                    $toInsert[] = array_merge($row, [
                        'csv_checksum' => $rowChecksum,
                        'change_type' => 'new',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $newRows++;
                }

                $count++;

                if (count($toInsert) >= $batchSize) {
                    Company::insert($toInsert);
                    $toInsert = [];
                }

                if ($count % 5000 === 0) {
                    DB::commit();
                    DB::beginTransaction();
                }

                if ($count % $progressInterval === 0) {
                    $import->update(['progress' => (int)($count / $totalRows * 100)]);
                }
            }

            if (!empty($toInsert)) {
                Company::insert($toInsert);
            }

            $removedRows = 0;
            if ($existingImport) {
                $removedChecksums = array_diff_key($existingChecksums, array_flip($currentChecksums));
                if (!empty($removedChecksums)) {
                    $removedRows = Company::where('csv_import_id', $existingImport->id)
                        ->whereIn('csv_checksum', array_keys($removedChecksums))
                        ->delete();
                }

                Company::where('csv_import_id', $existingImport->id)
                    ->where('change_type', 'updated')
                    ->update(['change_type' => 'updated']);
            }

            $import->update([
                'checksum' => $csvChecksum,
                'filename' => $import->filename ?: basename($filePath),
                'new_rows' => $newRows,
                'updated_rows' => $updatedRows,
                'removed_rows' => $removedRows,
                'unchanged_rows' => $unchangedRows,
                'summary' => [
                    'new' => $newRows,
                    'updated' => $updatedRows,
                    'removed' => $removedRows,
                    'unchanged' => $unchangedRows,
                    'total' => $count,
                ],
                'status' => 'completed',
                'progress' => 100,
            ]);

            Company::whereNull('csv_import_id')
                ->orWhere('csv_import_id', '<>', $import->id)
                ->update(['csv_import_id' => $import->id]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $import->update(['status' => 'failed']);
            throw $e;
        } finally {
            fclose($handle);
        }

        return $import->fresh();
    }

    public function countRows(string $filePath): int
    {
        $count = 0;
        $handle = fopen($filePath, 'r');
        while (fgets($handle) !== false) {
            $count++;
        }
        fclose($handle);
        return max(0, $count - 1);
    }

    public function parseHeaders(array $headers): array
    {
        $headerMap = [
            'organisation name' => 'organisation_name',
            'town/city' => 'town_city',
            'town city' => 'town_city',
            'county' => 'county',
            'type & rating' => 'type_rating',
            'type rating' => 'type_rating',
            'route' => 'route',
        ];

        return array_map(function ($h) use ($headerMap) {
            $normalized = trim($h);
            $lower = strtolower($normalized);
            return $headerMap[$lower] ?? str_replace([' ', '/', '&'], '_', $lower);
        }, $headers);
    }

    public function mapRow(array $headers, array $data): array
    {
        $row = [];
        foreach ($headers as $i => $field) {
            $row[$field] = isset($data[$i]) ? trim($data[$i]) : null;
        }
        return $row;
    }

    public function getImportHistory()
    {
        return CsvImport::orderBy('created_at', 'desc')->get();
    }
}
