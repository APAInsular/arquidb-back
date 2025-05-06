<?php

namespace App\Imports;

use App\Traits\ImportTracker;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Facades\Log;

class MultiSheetImport implements WithMultipleSheets, WithEvents, WithChunkReading
{
    use ImportTracker;

    private $peopleCache = [];

    public function chunkSize(): int
    {
        return 250; // Reducir el tamaño del chunk para archivos muy grandes
    }

    public function sheets(): array
    {
        $this->resetCounters();
        $peopleImport = new PeopleImport($this);

        return [
            // Aquí defines qué importador corresponde a cada hoja
            // 'TBLEXPEDIENTES_COLEGIADOS' => new MultiImport([
            //     $peopleImport,
            //     new CollegiatesImport($this),
            // ], $this),
            // 'TBLEXPEDIENTES_CLIENTES' => new MultiImport([
            //     $peopleImport,
            //     new ClientsImport($this),
            //     new PhonesImport($this),
            // ], $this),
            // 'tblclientes' => new MultiImport([
            //     new AddressesImport($this),
            //     new EmailsImport($this),
            // ], $this),
            'TBLEXPEDIENTES' => new ExpedientsImport($this),
            'TBLEXPEDIENTES_FASES' => new PhasesImport($this),
        ];

        // Orden
        // PersonSeeder::class,
        // CollegiateSeeder::class,
        // ClientSeeder::class,
        // ExpedientSeeder::class,
        // PhaseSeeder::class,
        // DocumentSeeder::class,
        // PhoneSeeder::class,
        // AddressSeeder::class,
        // EmailSeeder::class,

        // Alternativa si las hojas tienen índices numéricos:
        // 0 => new ClientsImport(),
        // 1 => new ProductsImport(),
        // 2 => new OrdersImport(),
    }

    protected function makeChunkedImport(array $importers)
    {
        return new class($importers, $this) extends MultiImport {
            public function chunkSize(): int
            {
                return 100; // Chunk más pequeño para hojas grandes
            }

            public function batchSize(): int
            {
                return 50; // Batch más pequeño para hojas grandes
            }
        };
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                $this->resetCounters();
                Log::info('Starting import process');
            },
            AfterImport::class => function (AfterImport $event) {
                Log::info("Import completed. Stats: ", [
                    'processed' => $this->processed,
                    'successful' => $this->successful,
                    'failed' => $this->failed
                ]);
            },
        ];
    }
}
