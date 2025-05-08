<?php

namespace App\Imports;

use App\Jobs\ProcessImportChunk;
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
    protected $currentSheetName;

    public function __construct(array $importers, $tracker)
    {
        $this->importers = $importers;
        $this->tracker = $tracker;
    }

    public function model(array $row)
    {
        // Solo se ejecutará para pequeños chunks en memoria
        foreach ($this->importers as $key => $importer) {
            try {
                $importer->model($row);
                $this->tracker->incrementProcessed();
                $this->tracker->incrementSuccessful();
            } catch (\Exception $e) {
                Log::error("Error processing row in {$key}: " . $e->getMessage());
                $this->tracker->incrementFailed();
            }
        }
        return null;
    }

    public function dispatchChunkToQueue($chunk)
    {
        // Configuración de importadores (debe coincidir con MultiSheetImport)
        $importersConfig = [
            'people' => PeopleImport::class,
            'collegiates' => CollegiatesImport::class,
            'clients' => ClientsImport::class,
            'phones' => PhonesImport::class,
            'addresses' => AddressesImport::class,
            'emails' => EmailsImport::class,
            'expedients' => ExpedientsImport::class,
            'phases' => PhasesImport::class
        ];

        ProcessImportChunk::dispatch(
            $chunk->toArray(),
            $this->currentSheetName,
            [
                'processed' => $this->tracker->processed,
                'successful' => $this->tracker->successful,
                'failed' => $this->tracker->failed
            ],
            $importersConfig
        )->onQueue('imports');
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
