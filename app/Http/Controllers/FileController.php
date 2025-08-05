<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Phase;
use App\Models\Document;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB
        ]);

        $file = $request->file('file');

        if (!$file->isValid()) {
            return response()->json(['message' => 'El archivo no es válido'], 400);
        }

        $filename = time() . '_' . $file->getClientOriginalName();
        $path = 'documents/' . $filename;

        // Subir a S3 y hacerlo público
        Storage::disk('s3')->put($path, file_get_contents($file), 'public');

        // Generar una URL temporal válida por 10 minutos
        $tempUrl = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(10));


        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => $tempUrl,
            //'url' => Storage::disk('s3')->url($path),
        ]);
    }

    public function getDocumentUrlById($id)
    {
        try {
            // Buscar el documento
            $document = Document::findOrFail($id);

            // Obtener la URL pública desde S3
            $url = Storage::disk('s3')->url($document->path);

            return response()->json([
                'message' => 'URL obtenida correctamente.',
                'url' => $url,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Documento no encontrado.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener la URL del documento.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function addPhaseDocuments(Request $request)
    {
        try {
            $request->validate([
                'phase_id' => ['required', 'exists:phases,id'],
                'files' => ['required', 'array', 'min:1'],
                'files.*' => ['file', 'mimes:pdf', 'max:10240'],
            ]);

            $phase = Phase::findOrFail($request->phase_id);
            $folderPath = "documents/{$phase->id}/files";
            $storedDocuments = [];

            DB::beginTransaction();

            foreach ($request->file('files') as $file) {
                if (!$file->isValid()) {
                    throw new \Exception('Archivo inválido: ' . $file->getClientOriginalName());
                }

                // Almacenar el archivo en S3
                $filePath = Storage::disk('s3')->putFile($folderPath, $file, 'public');

                if (!$filePath) {
                    throw new \Exception('No se pudo guardar el archivo en S3: ' . $file->getClientOriginalName());
                }

                // Crear el registro en la base de datos
                $document = Document::create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $filePath,
                    'phase_id' => $phase->id,
                ]);

                // Añadir la URL pública
                $document->url = Storage::disk('s3')->temporaryUrl($filePath, now()->addMinutes(10));
                $storedDocuments[] = $document;
            }

            DB::commit();

            return response()->json([
                'message' => 'Documentos añadidos correctamente.',
                'documents' => $storedDocuments,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Error de validación.',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en addPhaseDocuments', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error al añadir los documentos.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function erase(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->path;

        if (strpos($path, '..') !== false) {
            return response()->json([
                'success' => false,
                'message' => 'Ruta inválida'
            ], 400);
        }

        if (!Storage::disk('s3')->exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe en S3.'
            ], 404);
        }

        if (!Storage::disk('s3')->delete($path)) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el archivo.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Archivo eliminado correctamente.'
        ]);
    }
}
