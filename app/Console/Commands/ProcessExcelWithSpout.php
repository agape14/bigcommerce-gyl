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

        // Establecer una carpeta temporal personalizada en tu proyecto
        $customTempFolder = storage_path('app/temp');

        // Crear la carpeta si no existe
        if (!file_exists($customTempFolder)) {
            mkdir($customTempFolder, 0777, true);
        }

        // Crear un lector para el archivo Excel
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->setTempFolder($customTempFolder);
        $reader->open($path);

        // Verificar si hay hojas disponibles
        $sheetIterator = $reader->getSheetIterator();
        $sheetIterator->rewind(); // Asegura que el iterador esté en la primera hoja

        if (!$sheetIterator->valid()) {
            $this->error('No se encontró ninguna hoja en el archivo Excel.');
            $reader->close();
            return;
        }

        $firstSheet = $sheetIterator->current();  // Obtener la primera hoja
        $rowIterator = $firstSheet->getRowIterator();

        // Crear un escritor para el archivo CSV
        $writer = WriterEntityFactory::createCSVWriter();
        $writer->openToFile($outputCsv);

        // Inicializar un contador de filas
        $rowCounter = 0;

        $rowCounter = 0;
        $headerRow = [];
        $jsonHeader = 'Attributes_JSON'; // Nueva cabecera para el campo JSON
        
        // Iterar sobre las filas del archivo Excel
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowCounter++;
                $cells = $row->getCells();
                $rowData = [];
    
                // Extraer los valores de las celdas
                foreach ($cells as $cell) {
                    $rowData[] = $cell->getValue();
                }
    
                // Obtener la primera fila como cabecera
                if ($rowCounter == 1) {
                    $headerRow = $rowData;
                    // Añadir la nueva cabecera para el JSON al final
                    $headerRow[] = $jsonHeader;
                    // Escribir las cabeceras en el archivo CSV
                    $writer->addRow(WriterEntityFactory::createRowFromArray($headerRow));
                    continue;
                }
    
                // Separar en bloques
                $block1 = array_slice($rowData, 0, 18);
    
                // Bloque 2: Columnas S hasta SX (Índice 19 a 518)
                $block2Array = array_slice($rowData, 19, 518);
                $block2 = [];
    
                // Usar las cabeceras correspondientes para el bloque 2
                $block2Headers = array_slice($headerRow, 19, 518);
                foreach ($block2Array as $key => $value) {
                    if (!empty($value)) {
                        $block2[$block2Headers[$key]] = $value; // Asignar nombre de cabecera a cada valor
                    }
                }
    
                // Convertir el bloque 2 a JSON
                $block2Json = json_encode($block2, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
                // Bloque 3: Desde columna SY en adelante (Índice 519 hasta el total de columnas)
                $block3 = array_slice($rowData, 519, 540);
    
                // Combinar bloques y añadir el bloque JSON al final
                $combinedRow = array_merge($block1, $block3, [$block2Json]);
    
                // Escribir la fila combinada en el archivo CSV
                $writer->addRow(WriterEntityFactory::createRowFromArray($combinedRow));
            }
        }

        // Cerrar los recursos
        $reader->close();
        $writer->close();

        // Limpia la carpeta temporal después de finalizar el procesamiento
        $this->cleanupTempFolder($customTempFolder);

        $endTime = now();
        $this->info('Proceso finalizado: ' . $endTime);
        $this->info('Duración total: ' . $startTime->diffInMinutes($endTime) . ' minutos');
    }

    private function cleanupTempFolder($folderPath)
    {
        // Eliminar archivos temporales
        if (is_dir($folderPath)) {
            foreach (glob($folderPath . '/*') as $file) {
                unlink($file);
            }
            rmdir($folderPath);
        }
    }
}