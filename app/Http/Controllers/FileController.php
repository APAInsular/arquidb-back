<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\DB;
use App\Models\Phase;
use App\Models\Document;

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

    public function addPhaseDocuments(Request $request)
    {
        // Validar que se envíe un array de archivos
        $request->validate([
            'phase_id' => ['required', 'exists:phases,id'],
            'files' => ['required', 'array'],
            'files.*' => ['file', 'mimes:pdf', 'max:10240'],
        ]);

        // Buscar la fase
        $phase = Phase::findOrFail($request->phase_id);

        // Opcional: define una carpeta usando el ID de la fase o el slug, por ejemplo:
        $folderPath = "documents/{$phase->id}/files";

        $storedDocuments = [];

        DB::beginTransaction();

        try {
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    // Almacenar el archivo en el disco S3 en la carpeta designada
                    $filePath = $file->store($folderPath, 'public');

                    if (!$filePath) {
                        throw new \Exception('Error al almacenar el archivo');
                    }

                    // Guardar la ruta en la base de datos (se recomienda guardar solo la ruta relativa)
                    $document = Document::create([
                        'name' => $filePath,
                        'phase_id' => $phase->id,
                    ]);

                    $storedDocuments[] = $document;
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Imágenes añadidas correctamente.',
                'images' => $storedDocuments,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al añadir las imágenes.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function erase(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        // Extrae solo el nombre del archivo de la ruta completa
        $relativePath = $request->path;
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
