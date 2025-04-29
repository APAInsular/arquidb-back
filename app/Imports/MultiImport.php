<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Log;

class MultiImport implements ToModel, WithEvents, WithHeadingRow
{
    protected $importers;
    protected $tracker;

    public function __construct(array $importers, $tracker)
    {
        $this->importers = $importers;
        $this->tracker = $tracker;
    }

    public function model(array $row)
    {
        Log::debug("Processing row in MultiImporter", $row);
        foreach ($this->importers as $importer) {
            Log::debug("Executing " . get_class($importer));
            try {
                $importer->model($row);
            } catch (\Exception $e) {
                // Manejar error individual del importador
                continue;
            }
        }

        return null; // No retornamos modelo directamente
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                foreach ($this->importers as $importer) {
                    if (method_exists($importer, 'registerEvents')) {
                        $events = $importer->registerEvents();
                        if (isset($events[BeforeSheet::class])) {
                            $events[BeforeSheet::class]($event);
                        }
                    }
                }
            },
            AfterSheet::class => function (AfterSheet $event) {
                foreach ($this->importers as $importer) {
                    if (method_exists($importer, 'registerEvents')) {
                        $events = $importer->registerEvents();
                        if (isset($events[AfterSheet::class])) {
                            $events[AfterSheet::class]($event);
                        }
                    }
                }
            }
        ];
    }
}
