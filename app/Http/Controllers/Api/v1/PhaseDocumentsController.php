<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Phase;
use Illuminate\Http\Request;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\RelationController;

class PhaseDocumentsController extends RelationController
{
    //
    // use DisablePagination;
    // use DisableAuthorization;

    protected $model = Phase::class;

    protected $relation = 'documents';

   /**
     * Añade la URL temporal S3/R2 a cada documento antes de devolver la respuesta.
     */
    protected function afterIndex(
        \Orion\Http\Requests\Request $request,
        \Illuminate\Database\Eloquent\Model $parentEntity,
        $entities
    ) {
        foreach ($entities as $document) {
            $document->url = Storage::disk('s3')->temporaryUrl(
                $document->path,
                now()->addMinutes(10)
            );
        }

        return $entities;
    }
}
