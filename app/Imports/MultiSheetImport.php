<?php

namespace App\Imports;

use App\Traits\ImportTracker;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessImportChunk;

class MultiSheetImport implements WithMultipleSheets, WithEvents, WithChunkReading
{
    use ImportTracker;

    public function chunkSize(): int
    {
        return 250; // Tamaño de chunk para procesamiento en memoria
    }

    public function sheets(): array
    {
        $this->resetCounters();

        return [
            'TBLEXPEDIENTES_COLEGIADOS' => $this->createImportForSheet([
                'people' => new PeopleImport($this),
                'collegiates' => new CollegiatesImport($this)
            ]),

            'TBLEXPEDIENTES_CLIENTES' => $this->createImportForSheet([
                'people' => new PeopleImport($this),
                'clients' => new ClientsImport($this),
                'phones' => new PhonesImport($this)
            ]),

            'tblclientes' => $this->createImportForSheet([
                'addresses' => new AddressesImport($this),
                'emails' => new EmailsImport($this)
            ]),

            'TBLEXPEDIENTES' => $this->createImportForSheet([
                'expedients' => new ExpedientsImport($this)
            ]),

            'TBLEXPEDIENTES_FASES' => $this->createImportForSheet([
                'phases' => new PhasesImport($this)
            ])
        ];
    }

    protected function createImportForSheet(array $importers)
    {
        return new class($importers, $this) extends \App\Imports\MultiImport {
            public function chunkSize(): int
            {
                return 100; // Chunk más pequeño para procesamiento en jobs
            }

            public function registerEvents(): array
            {
                return [
                    'sheet' => function ($sheet) {
                        $sheet->on('chunk', function ($chunk) {
                            $this->dispatchChunkToQueue($chunk);
                        });
                    }
                ];
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
