<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Facades\Log;
use Throwable;

class MultiImport implements ToModel, WithEvents, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsOnError
{
    protected $importers;
    protected $tracker;
    protected $currentRow;

    public function __construct(array $importers, $tracker)
    {
        $this->importers = $importers;
        $this->tracker = $tracker;
    }

    public function model(array $row)
    {
        $this->currentRow = $row;

        Log::debug("Processing row in MultiImporter", ['row' => $row]);

        foreach ($this->importers as $importer) {
            $importerClass = get_class($importer);
            Log::debug("Executing importer: {$importerClass}");

            try {
                $result = $importer->model($row);
                Log::debug("Importer {$importerClass} processed row successfully");
            } catch (\Exception $e) {
                Log::error("Error in importer {$importerClass}: " . $e->getMessage(), [
                    'row' => $row,
                    'error' => $e
                ]);
                // $this->tracker->incrementFailed();
                continue;
            }
        }

        return null;
    }

    public function registerEvents(): array
    {
        $events = [
            BeforeSheet::class => function (BeforeSheet $event) {
                foreach ($this->importers as $importer) {
                    $this->forwardEvent($importer, $event, BeforeSheet::class);
                }
            },
            AfterSheet::class => function (AfterSheet $event) {
                foreach ($this->importers as $importer) {
                    $this->forwardEvent($importer, $event, AfterSheet::class);
                }
            },
            BeforeImport::class => function (BeforeImport $event) {
                // Limpiar memoria antes de empezar
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            },
            AfterImport::class => function (AfterImport $event) {
                // Limpiar memoria al finalizar
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            },
        ];

        return $events;
    }

    protected function forwardEvent($importer, $event, string $eventClass)
    {
        if (method_exists($importer, 'registerEvents')) {
            $importerEvents = $importer->registerEvents();
            if (isset($importerEvents[$eventClass])) {
                try {
                    $importerEvents[$eventClass]($event);
                } catch (\Exception $e) {
                    Log::error("Error forwarding {$eventClass} to " . get_class($importer) . ": " . $e->getMessage());
                }
            }
        }
    }

    public function batchSize(): int
    {
        $sizes = array_map(function ($importer) {
            return method_exists($importer, 'batchSize') ? $importer->batchSize() : 1000;
        }, $this->importers);

        return min($sizes); // Usamos el tamaño de batch más pequeño
    }

    public function chunkSize(): int
    {
        $sizes = array_map(function ($importer) {
            return method_exists($importer, 'chunkSize') ? $importer->chunkSize() : 500;
        }, $this->importers);

        return min($sizes); // Usamos el tamaño de chunk más pequeño
    }

    public function onError(Throwable $e)
    {
        Log::error('MultiImport global error: ' . $e->getMessage(), [
            'row' => $this->currentRow ?? null,
            'error' => $e
        ]);

        foreach ($this->importers as $importer) {
            if (method_exists($importer, 'onError')) {
                try {
                    $importer->onError($e);
                } catch (\Exception $innerException) {
                    Log::error("Error in importer's onError handler: " . $innerException->getMessage());
                }
            }
        }

        // $this->tracker->incrementFailed();
    }
}
