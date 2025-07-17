<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ArchivoController extends Controller
{
    //
    public function verificarArchivo()
    {
        $path = storage_path('app/private/Prueba.xlsx');

        if (File::exists($path)) {
            return "El archivo está presente en: $path";
        } else {
            return "Archivo no encontrado.";
        }
    }
}
