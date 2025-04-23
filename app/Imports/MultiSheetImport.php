<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetImport implements WithMultipleSheets
{
    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            // Aquí defines qué importador corresponde a cada hoja
            // '' => new PeopleImport(),
            // '' => new CollegiatesImport(),
            'tblclientes' => [
                new PeopleImport(),
                new ClientsImport(),
                new PhonesImport(),
                new AddressesImport(),
                new EmailsImport(),
            ],
            'TBLEXPEDIENTES' => new ExpedientsImport(),
            'TBLEXPEDIENTES_FASES' => new PhasesImport(),
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
}
