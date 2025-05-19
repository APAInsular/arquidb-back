<?php

namespace App\Imports;

use App\Traits\ImportTracker;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class MultiSheetImport implements WithMultipleSheets, WithEvents, WithChunkReading, ShouldQueue
{
    use ImportTracker;

    public function chunkSize(): int
    {
        return 200; // Tamaño de chunk para procesamiento en memoria
    }

    public function sheets(): array
    {
        $this->resetCounters();
        Log::info("Iniciando importación de hojas");

        return [
            'TBLEXPEDIENTES' => new SheetImport([
                'expedients' => new ExpedientsImport($this)
            ], $this, 100),

            // 'TBLEXPEDIENTES_FASES' => new SheetImport([
            //     'phases' => new PhasesImport($this)
            // ], $this, 100),

            'TBLEXPEDIENTES_COLEGIADOS' => new SheetImport([
                'people' => new PeopleImport($this),
                // 'collegiates' => new CollegiatesImport($this),
                'expedient_person'=>new ExpedientHasPeopleImport($this)
            ], $this, 100),

            'TBLEXPEDIENTES_CLIENTES' => new SheetImport([
                'people' => new PeopleImport($this),
                // 'clients' => new ClientsImport($this),
                // 'phones' => new PhonesImport($this),
                'expedient_person'=>new ExpedientHasPeopleImport($this)
            ], $this, 100),

            // 'tblclientes' => new SheetImport([
            //     'addresses' => new AddressesImport($this),
            //     'emails' => new EmailsImport($this)
            // ], $this, 100),

        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                gc_enable();
                $this->resetCounters();
                Log::info('Starting import process ' . memory_get_usage(true));
            },
            AfterImport::class => function (AfterImport $event) {
                gc_collect_cycles();
                Log::info("Import completed. Stats: ", [
                    'processed' => $this->processed,
                    'successful' => $this->successful,
                    'failed' => $this->failed
                ]);
            },
        ];
    }
}
