<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Traits\ImportTracker;

class ProcessImportChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ImportTracker;

    protected $chunk;
    protected $sheetName;
    protected $trackerData;
    protected $importersConfig;

    public function __construct(
        array $chunk,
        string $sheetName,
        array $trackerData = [],
        array $importersConfig = []
    ) {
        $this->chunk = $chunk;
        $this->sheetName = $sheetName;
        $this->trackerData = $trackerData;
        $this->importersConfig = $importersConfig;
    }

    public function handle()
    {
        $this->initializeCounters();

        try {
            // Determinar qué importadores usar según la hoja
            $importers = $this->getImportersForSheet();
            $this->freeMemory();

            foreach ($this->chunk as $index => $row) {
                $this->processRow($row, $importers);

                if ($index % 20 === 0) {
                    $this->freeMemory();
                }
            }

            Log::info("Chunk processed successfully", [
                'sheet' => $this->sheetName,
                'rows_processed' => count($this->chunk)
            ]);
        } catch (\Exception $e) {
            Log::error("Error processing chunk: " . $e->getMessage());
            $this->fail($e);
        }
    }

    protected function freeMemory()
    {
        $memory = memory_get_usage(true);
        if ($memory > 500 * 1024 * 1024) {
            gc_collect_cycles();
            Log::warning("Memoria alta: " . round($memory / 1024 / 1024) . "MB - Limpiando");
        }
    }

    protected function initializeCounters()
    {
        $this->processed = $this->trackerData['processed'] ?? 0;
        $this->successful = $this->trackerData['successful'] ?? 0;
        $this->failed = $this->trackerData['failed'] ?? 0;
    }

    protected function getImportersForSheet()
    {
        // Mapeo de hojas a importadores (debería coincidir con MultiSheetImport)
        $sheetImporters = [
            'TBLEXPEDIENTES_COLEGIADOS' => ['people', 'collegiates'],
            'TBLEXPEDIENTES_CLIENTES' => ['people', 'clients', 'phones'],
            'tblclientes' => ['addresses', 'emails'],
            'TBLEXPEDIENTES' => ['expedients'],
            'TBLEXPEDIENTES_FASES' => ['phases']
        ];

        $importers = [];

        foreach ($sheetImporters[$this->sheetName] ?? [] as $importerType) {
            $importerClass = $this->importersConfig[$importerType] ?? null;
            if ($importerClass) {
                $importers[] = new $importerClass($this);
            }
        }

        return $importers;
    }

    protected function processRow(array $row, array $importers)
    {
        $this->incrementProcessed();

        try {
            foreach ($importers as $importer) {
                $importer->model($row);
            }

            $this->incrementSuccessful();
        } catch (\Exception $e) {
            Log::error("Error processing row: " . $e->getMessage(), [
                'sheet' => $this->sheetName,
                'row_data' => $row
            ]);
            $this->incrementFailed();
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::critical("ProcessImportChunk failed for sheet {$this->sheetName}", [
            'exception' => $exception,
            'chunk_size' => count($this->chunk)
        ]);
    }
}
