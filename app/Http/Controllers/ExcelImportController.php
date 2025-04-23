<?php

namespace App\Http\Controllers;

use App\Imports\MultiSheetImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImportController extends Controller
{
    public function import(Request $request)
    {
        // $request->validate([
        //     'file' => 'required|mimes:xlsx,xls'
        // ]);

        try {
            // $file = $request->file('file');

            Excel::import(new MultiSheetImport, 'Prueba.XLS');

            return response()->json([
                'message' => 'Datos importados correctamente a múltiples modelos',
                'success' => true
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al importar datos: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
}
