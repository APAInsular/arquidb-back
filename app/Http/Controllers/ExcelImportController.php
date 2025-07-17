<?php

namespace App\Http\Controllers;

use App\Imports\MultiSheetImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExcelImportController extends Controller
{
    public function import(Request $request)
    {
        DB::beginTransaction();

        try {
            $filePath = storage_path('app/private/Prueba.xlsx');

            if (!file_exists($filePath)) {
                throw new \Exception("Archivo no encontrado: $filePath");
            }

            Log::info("Starting import from file: $filePath");

            $import = new MultiSheetImport();
            Excel::queueImport($import, $filePath)->onQueue('imports');

            DB::commit();

            Log::info("Import completed successfully", [
                'processed' => $import->getRowCount(),
                'successful' => $import->getSuccessCount(),
                'failed' => $import->getErrorCount()
            ]);

            return response()->json([
                'message' => 'Importación completada',
                'success' => true,
                'results' => [
                    'processed' => $import->getRowCount(),
                    'successful' => $import->getSuccessCount(),
                    'failed' => $import->getErrorCount(),
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Import failed: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error en la importación',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
