<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Phase;
use Illuminate\Http\Request;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\RelationController;

use Orion\Http\Requests\Request as OrionRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
    protected function afterIndex(OrionRequest $request, Model $parentEntity, $entities)
    {
        foreach ($entities as $entity) {
            $entity->url = Storage::disk('s3')->temporaryUrl($entity->path, now()->addMinutes(10));
        }
        return $entities;
    }
}
