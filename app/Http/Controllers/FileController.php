<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');

        if (!$file->isValid()) {
            return response()->json(['message' => 'El archivo no es válido'], 400);
        }

        $filename = time() . '_' . $file->getClientOriginalName(); // o solo getClientOriginalName()
        $path = 'documents/' . $filename;

        // Usa el disco 'public' pero manualmente mueve el archivo
        $file->move(storage_path('app/public/documents'), $filename);

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => asset('storage/' . $path),
            'filename' => $filename,
        ]);
    }
}
