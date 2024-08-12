<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class ProcessExcelWithSpout extends Command
{
    protected $signature = 'excel:process {path} {outputCsv}';
    protected $description = 'Process Excel to CSV with Spout';

    public function handle()
    {
        $startTime = now();
        $this->info('Proceso iniciado: ' . $startTime);

        $path = $this->argument('path');
        $outputCsv = $this->argument('outputCsv');

        // Crear un lector para el archivo Excel
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($path);

        // Verificar si hay hojas disponibles
        $sheetIterator = $reader->getSheetIterator();
        $sheetIterator->rewind(); // Asegura que el iterador esté en la primera hoja

        if (!$sheetIterator->valid()) {
            $this->error('No se encontró ninguna hoja en el archivo Excel.');
            $reader->close();
            return;
        }

        $sheet = $sheetIterator->current();

        // Crear un escritor para el archivo CSV
        $writer = WriterEntityFactory::createCSVWriter();
        $writer->openToFile($outputCsv);

        foreach ($sheet->getRowIterator() as $row) {
            $cells = $row->getCells();
            $rowData = [];

            // Extraer los valores de las celdas
            foreach ($cells as $cell) {
                $rowData[] = $cell->getValue();
            }

            // Separar en bloques
            $block1 = array_slice($rowData, 0, 18);

            // Bloque 2: Columnas S hasta SX (Índice 18 a 243)
            $block2Array = array_slice($rowData, 19, 518);
            $block2 = [];

            foreach ($block2Array as $key => $value) {
                if (!empty($value)) {
                    $block2[$key] = $value;
                }
            }
            $block2Json = json_encode($block2, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // Bloque 3: Desde columna SY en adelante (Índice 244 hasta el total de columnas)
            $block3 = array_slice($rowData, 519,540);

            // Combinar bloques y escribir en CSV
            $combinedRow = array_merge($block1, $block3, [$block2Json]);
            $writer->addRow(WriterEntityFactory::createRowFromArray($combinedRow));
        }

        $reader->close();
        $writer->close();

        $endTime = now();
        $this->info('Proceso finalizado: ' . $endTime);
        $this->info('Duración total: ' . $startTime->diffInMinutes($endTime) . ' minutos');
    }
}