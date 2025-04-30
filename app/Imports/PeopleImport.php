<?php

namespace App\Imports;

use App\Models\Person;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;
use Throwable;

class PeopleImport implements ToModel, WithHeadingRow, SkipsOnError, WithBatchInserts, WithChunkReading
{
    protected $tracker;

    public function __construct(MultiSheetImport $tracker)
    {
        $this->tracker = $tracker;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 500; // Procesar 500 filas a la vez
    }

    public function model(array $row)
    {
        try {
            // 1. Validar y preparar datos
            $personData = $this->preparePersonData($row);

            if ($personData === null) {
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }

            // 2. Crear o actualizar persona
            $person = $this->savePerson($personData);

            $this->tracker->incrementProcessed();
            return $person;
        } catch (\Exception $e) {
            Log::error('Error procesando fila de persona: ' . $e->getMessage());
            $this->tracker->incrementFailed();
            $this->tracker->incrementProcessed();
            return null;
        }
    }

    protected function preparePersonData(array $row): ?array
    {
        $nif = $row['nifcliente'] ?? $row['nif'];
        $nameField = $row['nombre'] ?? $row['Nombre'] ?? $row['nomcliente'];

        // Validar campos obligatorios
        $requiredFields = [
            'nif' => $nif ?? null,
            'nombre' => $nameField ?? null,
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty(trim($value ?? ''))) {
                Log::warning("Campo requerido faltante: {$field}", $row);
                return null;
            }
        }

        // Determinar el nombre y apellidos
        $nameData = $this->parseNomCliente($nameField);

        // Determinar el tipo de identificación basado en el NIF
        $identificationType = $this->determineIdentificationType($nif);

        return [
            'identification_type' => $identificationType,
            'identification_number' => trim($nif),
            'name' => $nameData['name'],
            'first_surname' =>  $nameData['first_surname'] ?? null,
            'second_surname' =>  $nameData['second_surname'] ?? null,
        ];
    }

    protected function determineIdentificationType(string $nif): string
    {
        $nif = strtoupper(trim($nif));

        // Lógica para determinar el tipo de documento
        if (preg_match('/^[A-Z]\d{7}[A-Z0-9]$/', $nif)) {
            return 'NIF'; // Código de identificación fiscal para empresas
        } elseif (preg_match('/^[XYZ]\d{7}[A-Z]$/', $nif)) {
            return 'NIE'; // Número de identificación de extranjero
        } elseif (preg_match('/^\d{8}[A-Z]$/', $nif)) {
            return 'DNI'; // Documento nacional de identidad
        } else {
            return 'OTRO'; // Otro tipo de identificación
        }
    }

    protected function savePerson(array $personData): ?Person
    {
        try {
            $person = Person::updateOrCreate(
                ['identification_number' => $personData['identification_number']],
                $personData
            );

            $this->tracker->incrementSuccessful();
            return $person;
        } catch (\Exception $e) {
            Log::error("Error guardando persona {$personData['identification_number']}: " . $e->getMessage());
            $this->tracker->incrementFailed();
            return null;
        }
    }

    protected function parseNomCliente(string $nomcliente): array
    {
        $nomcliente = trim($nomcliente);

        // Si parece ser una empresa (no tiene espacios o tiene siglas/denominación comercial)
        if ($this->isCompanyName($nomcliente)) {
            return [
                'name' => $nomcliente,
                'first_surname' => null,
                'second_surname' => null
            ];
        }

        // Procesamiento para nombres de personas
        $parts = preg_split('/\s+/', $nomcliente);
        $count = count($parts);

        // Casos comunes
        if ($count === 1) {
            // Solo un nombre (sin apellidos)
            return [
                'name' => $parts[0],
                'first_surname' => null,
                'second_surname' => null
            ];
        } elseif ($count === 2) {
            // Nombre + 1 apellido
            return [
                'name' => $parts[0],
                'first_surname' => $parts[1],
                'second_surname' => null
            ];
        } elseif ($count === 3) {
            // Nombre + 2 apellidos (caso más común en español)
            return [
                'name' => $parts[0],
                'first_surname' => $parts[1],
                'second_surname' => $parts[2]
            ];
        } else {
            // Más de 3 partes - lógica para nombres compuestos
            return $this->handleCompoundNames($parts);
        }
    }

    protected function isCompanyName(string $name): bool
    {
        // Patrones que indican que es una empresa
        $companyPatterns = [
            '/^[A-Z0-9&]+$/', // Solo mayúsculas y números/símbolos (ej. "EMPRESA1", "A&B")
            '/\b(S\.?L\.?|S\.?A\.?|S\.?L\.?L\.?|S\.?C\.?|COOP\.?|SLU|SLLP)\b/i', // Siglas de tipos de empresa
            '/\b(empresa|sociedad|corporación|grupo|holding|asociación|fundación)\b/i' // Palabras típicas en nombres de empresa
        ];

        foreach ($companyPatterns as $pattern) {
            if (preg_match($pattern, $name)) {
                return true;
            }
        }

        return false;
    }

    protected function handleCompoundNames(array $parts): array
    {
        $count = count($parts);

        // Inteligencia para nombres compuestos españoles/americanos
        $nameParts = [];
        $surnames = [];

        // Asumimos que el primer elemento es siempre parte del nombre
        $nameParts[] = $parts[0];

        // Analizamos los siguientes elementos
        for ($i = 1; $i < $count; $i++) {
            $current = $parts[$i];
            $lowerCurrent = strtolower($current);

            // Palabras que suelen ser parte del nombre (preposiciones, conectores)
            $nameConnectors = ['de', 'del', 'la', 'las', 'los', 'y', 'e', 'i', 'van', 'von', 'di'];

            if (in_array($lowerCurrent, $nameConnectors)) {
                // Si es un conector, lo añadimos al nombre y al siguiente elemento
                if ($i + 1 < $count) {
                    $nameParts[] = $current;
                    $nameParts[] = $parts[$i + 1];
                    $i++; // Saltamos el siguiente elemento ya que lo hemos procesado
                } else {
                    $nameParts[] = $current;
                }
            } else {
                // Si no es conector, lo consideramos apellido
                $surnames[] = $current;
            }
        }

        // Separamos los apellidos (primero y segundo)
        $firstSurname = $surnames[0] ?? null;
        $secondSurname = $surnames[1] ?? null;

        // Si hay más de 2 apellidos, los unimos en el segundo apellido
        if (count($surnames) > 2) {
            $secondSurname = implode(' ', array_slice($surnames, 1));
        }

        return [
            'name' => implode(' ', $nameParts),
            'first_surname' => $firstSurname,
            'second_surname' => $secondSurname
        ];
    }

    public function onError(Throwable $e)
    {
        Log::error('Error en importación de personas: ' . $e->getMessage());
        $this->tracker->incrementFailed();
        $this->tracker->incrementProcessed();
    }
}
