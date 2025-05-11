<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Máximo 10MB
        ]);

        // Almacenar el archivo en la carpeta 'public/documents'
        $path = $request->file('file')->store('documents', 'public');

        // Devolver la ruta del archivo
        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => asset('storage/' . $path)
        ]);
    }
}
