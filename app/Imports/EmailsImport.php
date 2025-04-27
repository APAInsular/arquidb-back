<?php

namespace App\Imports;

use App\Models\Email;
use App\Models\Person;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Validator;

class EmailsImport implements ToModel, WithHeadingRow, SkipsOnError, WithBatchInserts
{
    protected $tracker;
    protected $currentPersonId = null;

    public function __construct(MultiSheetImport $tracker)
    {
        $this->tracker = $tracker;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function model(array $row)
    {
        try {
            // 1. Buscar el NIF en la fila para encontrar a la persona
            $nif = $this->findNifColumn($row);

            if (empty($nif)) {
                Log::warning('No se encontró NIF en fila', $row);
                $this->tracker->incrementFailed();
                return null;
            }

            // 2. Buscar la persona en la base de datos
            $person = Person::where('identification_number', $nif)->first();

            if (!$person) {
                Log::warning("No se encontró persona con NIF: {$nif}");
                $this->tracker->incrementFailed();
                return null;
            }

            $this->currentPersonId = $person->id;

            // 3. Procesar los emails
            $emailString = $this->findEmailColumn($row);

            if (empty($emailString)) {
                Log::warning('No se encontraron emails para persona: ' . $nif, $row);
                $this->tracker->incrementFailed();
                return null;
            }

            $emails = $this->extractEmails($emailString);

            if (empty($emails)) {
                Log::warning('No se encontraron emails válidos para persona: ' . $nif);
                $this->tracker->incrementFailed();
                return null;
            }

            // 4. Guardar cada email válido
            foreach ($emails as $email) {
                $this->saveEmail($email);
            }

            $this->tracker->incrementProcessed();
            return null;
        } catch (\Exception $e) {
            Log::error('Error procesando fila: ' . $e->getMessage());
            $this->tracker->incrementFailed();
            return null;
        }
    }

    protected function findNifColumn(array $row): ?string
    {
        $possibleColumns = ['nif', 'Nif', 'NIF', 'identification_number'];

        foreach ($possibleColumns as $column) {
            if (isset($row[$column]) && !empty(trim($row[$column]))) {
                return trim($row[$column]);
            }
        }

        return null;
    }

    protected function findEmailColumn(array $row): string
    {
        $possibleColumns = [
            'email',
            'Email',
            'e_mail',
            'E-mail',
            'correo',
            'Correo',
            'mail',
            'Mail',
            'correo_electronico'
        ];

        foreach ($possibleColumns as $column) {
            if (isset($row[$column]) && !empty(trim($row[$column]))) {
                Log::debug("Email encontrado en columna: {$column}");
                return trim($row[$column]);
            }
        }

        // Buscar cualquier columna que contenga @
        foreach ($row as $key => $value) {
            if (is_string($value) && strpos($value, '@') !== false) {
                Log::info("Email encontrado en columna no estándar: {$key}");
                return trim($value);
            }
        }

        return '';
    }

    protected function extractEmails(string $emailString): array
    {
        // Separar por múltiples delimitadores
        $rawEmails = preg_split('/[,;\s\/]+/', $emailString);

        $validEmails = [];
        foreach ($rawEmails as $email) {
            $cleanEmail = trim($email);

            if ($this->isValidEmail($cleanEmail)) {
                $validEmails[] = strtolower($cleanEmail); // Normalizar a minúsculas
            } elseif (!empty($cleanEmail)) {
                Log::warning("Email descartado (formato inválido): {$cleanEmail}");
            }
        }

        return array_unique($validEmails); // Eliminar duplicados en la misma celda
    }

    protected function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function saveEmail(string $email): void
    {
        try {
            $validator = Validator::make(
                ['email' => $email],
                [
                    'email' => 'required|email',
                    // Eliminada la regla unique ya que permitimos emails compartidos
                ]
            );

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            // Verificar si esta combinación persona-email ya existe
            $exists = Email::where('email', $email)
                ->where('person_id', $this->currentPersonId)
                ->exists();

            if (!$exists) {
                Email::create([
                    'email' => $email,
                    'person_id' => $this->currentPersonId
                ]);
                $this->tracker->incrementSuccessful();
            } else {
                Log::info("Email {$email} ya existe para persona {$this->currentPersonId} - omitiendo");
            }
        } catch (\Exception $e) {
            Log::error("Error guardando email {$email}: " . $e->getMessage());
            $this->tracker->incrementFailed();
        }
    }

    public function onError(Throwable $e)
    {
        Log::error('Error en importación de emails: ' . $e->getMessage());
        $this->tracker->incrementFailed();
    }
}
