<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400',
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
        ]);
    }

    public function erase(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        // Extrae solo el nombre del archivo de la ruta completa
        $filename = basename($request->path);
        $relativePath = 'documents/' . $filename;
        $fullPath = storage_path('app/public/' . $relativePath);

        // Verificación adicional de seguridad
        if (strpos($relativePath, '..') !== false) {
            return response()->json([
                'success' => false,
                'message' => 'Ruta inválida'
            ], 400);
        }

        if (!file_exists($fullPath)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe en: ' . $fullPath
            ], 404);
        }

        if (!unlink($fullPath)) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el archivo. Verifica los permisos.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Archivo eliminado correctamente'
        ]);
    }
}
