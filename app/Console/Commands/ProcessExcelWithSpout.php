<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class ProcessExcelWithSpout extends Command
{
    protected $signature = 'excel:processspout:spout {path} {outputCsv}';
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

        // Obtener el iterador de filas
        $rowIterator = $sheet->getRowIterator();

        // Verificar si hay al menos una fila
        if (!$rowIterator->valid()) {
            $this->error('No se encontró ninguna fila en la hoja.');
            $reader->close();
            return;
        }

        // Intentar leer la primera fila (cabeceras)
        $headerRow = $rowIterator->current();
        $this->info('Verificando la primera fila (cabeceras)');

        if ($headerRow === null) {
            $this->error('La primera fila (cabeceras) es nula.');
            $reader->close();
            return;
        }

        $headers = $headerRow->getCells();
        if ($headers === null) {
            $this->error('No se pudieron obtener las celdas de la primera fila.');
            $reader->close();
            return;
        }

        $headerData = [];
        foreach ($headers as $cell) {
            $headerData[] = $cell->getValue();
        }

        // Verificar si se obtuvieron cabeceras válidas
        if (empty($headerData)) {
            $this->error('La primera fila (cabeceras) está vacía.');
            $reader->close();
            return;
        }

        $this->info('Cabeceras leídas: ' . implode(', ', $headerData));

        // Avanzar el iterador para eliminar la primera fila (cabeceras)
        $rowIterator->next();

        // Crear un escritor para el archivo CSV
        $writer = WriterEntityFactory::createCSVWriter();
        $writer->openToFile($outputCsv);

        // Iterar sobre las filas restantes
        foreach ($rowIterator as $row) {
            if ($row === null) {
                continue; // Saltar filas nulas
            }

            $cells = $row->getCells();
            $rowData = [];

            // Extraer los valores de las celdas
            foreach ($cells as $cell) {
                $rowData[] = $cell->getValue();
            }

            // Separar en bloques
            $block1 = array_slice($rowData, 0, 18);

            // Bloque 2: Columnas S hasta SX (Índice 18 a 243)
            $block2Array = array_slice($rowData, 18, 226);
            $block2 = [];

            foreach ($block2Array as $key => $value) {
                if (!empty($value)) {
                    $block2[$headerData[$key + 18]] = $value;
                }
            }
            $block2Json = json_encode($block2, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // Bloque 3: Desde columna SY en adelante (Índice 244 hasta el total de columnas)
            $block3 = array_slice($rowData, 244);

            // Combinar bloques y escribir en CSV
            $combinedRow = array_merge($block1, [$block2Json], $block3);
            $writer->addRow(WriterEntityFactory::createRowFromArray($combinedRow));
        }

        $reader->close();
        $writer->close();

        $endTime = now();
        $this->info('Proceso finalizado: ' . $endTime);
        $this->info('Duración total: ' . $startTime->diffInMinutes($endTime) . ' minutos');
    }
}
