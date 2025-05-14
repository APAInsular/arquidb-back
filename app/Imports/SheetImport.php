<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SheetImport implements ToModel, WithEvents, WithHeadingRow, WithChunkReading, ShouldQueue
{
    protected $importers;
    protected $tracker;
    protected $currentSheetName;
    protected $chunkSize;

    public function __construct(array $importers, $tracker, $chunkSize = 200)
    {
        $this->importers = $importers;
        $this->tracker = $tracker;
        $this->chunkSize = $chunkSize;
    }

    public function model(array $row)
    {
        Log::info("Procesando fila: " . json_encode($row));
        Log::info("Memoria usada: " . memory_get_usage(true));

        foreach ($this->importers as $key => $importer) {
            try {
                $importer->model($row);
                $this->tracker->incrementProcessed();
                $this->tracker->incrementSuccessful();
            } catch (\Exception $e) {
                $this->tracker->incrementFailed();
            }
        }
        return null;
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $this->currentSheetName = $event->getSheet()->getTitle();
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
