<?php

namespace App\Http\Controllers;

use App\Models\CsvImport;
use App\Services\CsvImportService;
use Illuminate\Http\Request;

class CsvImportController extends Controller
{
    protected CsvImportService $csvImportService;

    public function __construct(CsvImportService $csvImportService)
    {
        $this->csvImportService = $csvImportService;
    }

    public function index()
    {
        $imports = $this->csvImportService->getImportHistory();
        return view('imports.index', compact('imports'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'csv_file' => 'required|file|mimes:csv,txt|max:102400',
            ]);

            $file = $request->file('csv_file');
            $path = $file->store('imports');

            $import = CsvImport::create([
                'filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'status' => 'uploaded',
                'progress' => 0,
            ]);

            return response()->json([
                'id' => $import->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function process(CsvImport $import)
    {
        try {
            session()->save();

            $filePath = storage_path('app/private/' . $import->file_path);

            if (!file_exists($filePath)) {
                return response()->json(['error' => 'File not found'], 404);
            }

            $import = $this->csvImportService->import($filePath, $import);

            return response()->json([
                'redirect' => route('imports.show', $import),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function progress(CsvImport $import)
    {
        return response()->json([
            'progress' => $import->progress,
            'status' => $import->status,
        ]);
    }

    public function show(\App\Models\CsvImport $import)
    {
        $companies = \App\Models\Company::where('csv_import_id', $import->id)
            ->orderBy('change_type')
            ->paginate(50);

        return view('imports.show', compact('import', 'companies'));
    }
}
