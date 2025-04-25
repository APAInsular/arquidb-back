<?php

namespace App\Imports;

use App\Traits\ImportTracker;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Facades\Log;

class MultiSheetImport implements WithMultipleSheets, WithEvents
{
    use ImportTracker;

    public function sheets(): array
    {
        $this->resetCounters();

        return [
            // Aquí defines qué importador corresponde a cada hoja
            // '' => new PeopleImport(),
            // '' => new CollegiatesImport(),
            // 'tblclientes' => [
            // new PeopleImport(),
            // new ClientsImport(),
            // new PhonesImport($this, 21),
            // new AddressesImport(),
            // new EmailsImport(),
            // ],
            'tblclientes' => new PeopleImport($this),
            // 'TBLEXPEDIENTES' => new ExpedientsImport(),
            // 'TBLEXPEDIENTES_FASES' => new PhasesImport(),
            // '' => new DocumentsImport(),
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
