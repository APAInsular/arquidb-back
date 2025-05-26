<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\User;
use App\Models\Document;
use App\Models\Record;
use Illuminate\Http\Request;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\RelationController;
use Illuminate\Support\Facades\Date;
use \Illuminate\Support\Facades\DB;

class UserDocumentsController extends RelationController
{
    //
    use DisablePagination;
    use DisableAuthorization;

    protected $model = User::class;

    protected $relation = 'documents';

    public function signDocuments(Request $request)
    {
        $validated = $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'required|integer|exists:documents,id',
        ]);

        $user = $request->user();
        DB::beginTransaction();

        try {
            foreach ($validated['documents'] as $docId) {
                $document = Document::find($docId);

                $this->authorize('sign', [$user, $document]);

                // Marcar como firmado
                $document->user_id = $user->id;
                $document->save();

                // Marcar la fase relacionada
                if ($document->phase) {
                    $document->phase->state = 'signed';
                    $document->phase->sign_date = Date::now();
                    $document->phase->save();
                }

                Record::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'action' => 'sign',
                    'affected_table' => "Document",
                    'affected_record_id' => $docId,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Documentos firmados correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al firmar los documentos.', 'details' => $e->getMessage()], 500);
        }
    }
}
