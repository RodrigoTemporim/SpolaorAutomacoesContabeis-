<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;


class Conciliacao extends BaseController
{
    public function index()
    {
        $csvData = null;
        echo view('common/header');
        echo view('conciliacao', ['csvData' => $csvData]);
        echo view('common/footer');
    }

    public function pandas()
    {
        $csvData = null;
        $saldos = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
                $nomeArquivo = $_FILES['arquivo']['name'];

                // Verifica se o arquivo é um arquivo CSV
                $extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
                if ($extensao === 'csv') {
                    $caminhoTemporario = $_FILES['arquivo']['tmp_name'];                    
                    $spreadsheet = IOFactory::load($caminhoTemporario);
                    $worksheet = $spreadsheet->getActiveSheet();
                    
                
                    // Modificações na planilha
                    $worksheet->removeColumn('H');
                    $worksheet->removeColumn('B');
                    $worksheet->removeColumn('D');
                    $worksheet->setCellValue('F1', 'Conciliação');
                    $worksheet->setCellValue('A1', 'Histórico');
                    $worksheet->insertNewColumnBefore('B', 1);
                    $worksheet->setCellValue('B1', 'Data');
                    $worksheet->removeRow(2);

                    $rowsToRemove = [];

                    foreach ($worksheet->getRowIterator() as $row) {
                        $cellA = $worksheet->getCell('A' . $row->getRowIndex());
                        $valueA = $cellA->getValue();

                        if (empty($valueA)) {
                            $rowsToRemove[] = $row->getRowIndex();
                        }
                    }

                    // Remove as linhas identificadas anteriormente em ordem inversa
                    rsort($rowsToRemove);

                    foreach ($rowsToRemove as $rowIndex) {
                        $worksheet->removeRow($rowIndex);
                    }

                    $lastRow = $worksheet->getHighestRow();

                    // Encontra a última data válida
                    $pattern = '/^\d{2}\/\d{2}\/\d{4}$/';
                    $lastValidDate = '';

                    for ($row = 1; $row <= $lastRow; $row++) {
                        $value = $worksheet->getCell('A' . $row)->getValue();

                        if (preg_match($pattern, $value) && $value != '') {
                            $lastValidDate = $value;


                            $worksheet->setCellValue('A' . $row, ''); // Define a célula na coluna A como vazia após a cópia
                        }

                        $valueB = $worksheet->getCell('B' . $row)->getValue();
                        if ($valueB == '' && $lastValidDate != '') {
                            $worksheet->setCellValue('B' . $row, $lastValidDate);                            
                        }
                    }

                    for ($row =1; $row <= $lastRow; $row++){
                        $valueA = $worksheet->getCell('A'.$row)->getValue();
                        if($valueA == '' or $valueA == 'Total m�sa d�bito:' or $valueA == 'Total anoa d�bito:'){
                            $worksheet->removeRow($row);
                            $row--; // Decrementa o contador de linha para verificar a próxima linha
                            $lastRow--; // Atualiza o valor de $lastRow
                        }

                    }


                    // Limpa coluna Histórico
                    foreach ($worksheet->getColumnIterator('A') as $column) {
                        foreach ($column->getCellIterator() as $cell) {
                            if ($cell->getColumn() === 'A' && $cell->getRow() != 1) {
                                $value = $cell->getValue();
                                $newValue = preg_replace('/[^0-9]/', '', $value);
                                $cell->setValue($newValue);
                            }
                        }
                    }

                    // Obtém os dados do CSV
                    $csvData = $worksheet->toArray();

                    echo view('common/header');
                    echo view('conciliacao', ['csvData' => $csvData, 'saldos' => $saldos]);
                    echo view('common/footer');
                } else {
                    echo "O arquivo enviado não é um CSV válido.";
                }
            } else {
                echo "Erro ao fazer o upload do arquivo.";
            }
        }
    }
}
