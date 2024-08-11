<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;


class ProcessExcelFile extends Command
{
    protected $signature = 'excel:process {path} {outputCsv} {sheet=Sheet1} {columns=540}';
    protected $description = 'Process an Excel file and export the specified number of columns to a CSV file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        ini_set('memory_limit', '1G'); // Ajusta el límite de memoria según sea necesario

        try {
            $path = $this->argument('path');
            $outputCsv = $this->argument('outputCsv');
            $sheet = $this->argument('sheet');
            $columns = (int) $this->argument('columns');

            // Verificar si el archivo existe
            if (!file_exists($path)) {
                $this->error("El archivo de entrada no existe en la ruta proporcionada.");
                return;
            }

            // Procesar el archivo en bloques
            $csvFile = fopen($outputCsv, 'w');

            Excel::import(new ExcelImport($csvFile, $sheet, $columns), $path);

            fclose($csvFile);

            $this->info('El archivo Excel ha sido procesado y el archivo CSV se ha creado correctamente.');
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}

class ExcelImport implements ToModel, WithHeadingRow, WithChunkReading, WithMultipleSheets
{
    protected $csvFile;
    protected $sheet;
    protected $columns;

    public function __construct($csvFile, $sheet, $columns)
    {
        $this->csvFile = $csvFile;
        $this->sheet = $sheet;
        $this->columns = $columns;
    }

    public function model(array $row)
    {
        // Bloque 1: Columnas A hasta R (Índice 0 a 17)
        $block1 = array_slice($row, 0, 18);

        // Bloque 2: Columnas S hasta SX (Índice 18 a 243)
        $block2Array = array_slice($row, 18, 226);
        $block2 = [];

        foreach ($block2Array as $key => $value) {
            if (!empty($value)) {
                $block2[$key] = $value;
            }
        }
        $block2Json = json_encode($block2);

        // Bloque 3: Desde columna SY en adelante (Índice 244 hasta $columns)
        $block3 = array_slice($row, 244, $this->columns - 244);

        // Combinar bloques y escribir en CSV
        $combinedRow = array_merge($block1, [$block2Json], $block3);
        fputcsv($this->csvFile, $combinedRow);

        return null; // Return null since we're not saving models
    }

    public function chunkSize(): int
    {
        return 1000; // Número de filas por bloque
    }

    public function sheets(): array
    {
        return [
            $this->sheet => new SheetImport($this->csvFile, $this->columns),
        ];
    }
}

class SheetImport implements ToModel, WithHeadingRow, WithChunkReading
{
    protected $csvFile;
    protected $columns;

    public function __construct($csvFile, $columns)
    {
        $this->csvFile = $csvFile;
        $this->columns = $columns;
    }

    public function model(array $row)
    {
        // Bloque 1: Columnas A hasta R (Índice 0 a 17)
        $block1 = array_slice($row, 0, 18);

        // Bloque 2: Columnas S hasta SX (Índice 18 a 243)
        $block2Array = array_slice($row, 18, 226);
        $block2 = [];

        foreach ($block2Array as $key => $value) {
            if (!empty($value)) {
                $block2[$key] = $value;
            }
        }
        $block2Json = json_encode($block2);

        // Bloque 3: Desde columna SY en adelante (Índice 244 hasta $columns)
        $block3 = array_slice($row, 244, $this->columns - 244);

        // Combinar bloques y escribir en CSV
        $combinedRow = array_merge($block1, [$block2Json], $block3);
        fputcsv($this->csvFile, $combinedRow);

        return null; // Return null since we're not saving models
    }

    public function chunkSize(): int
    {
        return 1000; // Número de filas por bloque
    }
}