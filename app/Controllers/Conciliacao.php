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

    public function csv()
    {
        set_time_limit(0); // Remova o limite de tempo (ou ajuste conforme necessário)

        $batchSize = 1000; // Número de linhas por batch
        $csvData = null;
        $saldos = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
                $nomeArquivo = $_FILES['arquivo']['name'];

                // Verifica se o arquivo é um arquivo CSV
                $extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
                if ($extensao === 'csv') {
                    $caminhoTemporario = $_FILES['arquivo']['tmp_name'];
                    $spreadsheet = IOFactory::createReader('Csv')->setDelimiter(';')->setInputEncoding('CP1252')->load($caminhoTemporario);
                    $worksheet = $spreadsheet->getActiveSheet();

                    // Modificações na planilha
                    $worksheet->removeColumn('H');
                    $worksheet->removeColumn('B');
                    $worksheet->removeColumn('D');
                    $worksheet->setCellValue('F1', 'Conciliação');
                    $worksheet->setCellValue('A1', 'Histórico');
                    $worksheet->insertNewColumnBefore('B', 1);
                    $worksheet->setCellValue('B1', 'Data');
                    $worksheet->setCellValue('E1', 'Dédito');
                    $worksheet->setCellValue('F1', 'Crédito');
                    $worksheet->removeRow(2);

                    /*
                        Historico A
                        Data B
                        Filial C
                        Chave D
                        Débito E
                        Crédito F
                        Conciliacao G
                    */
                    $rowsToRemove = [];

                    foreach ($worksheet->getRowIterator() as $row) {
                        $cellA = $worksheet->getCell('A' . $row->getRowIndex());
                        $valueA = $cellA->getValue();

                        if (empty($valueA)) {
                            $rowsToRemove[] = $row->getRowIndex();
                        }
                    }

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

                            $worksheet->setCellValue('A' . $row, ''); //remove a data da coluna A
                        }

                        $valueB = $worksheet->getCell('B' . $row)->getValue();
                        if ($valueB == '' && $lastValidDate != '') {
                            $worksheet->setCellValue('B' . $row, $lastValidDate);
                        }
                    }

                    // remove os rows vazios quando A estiver vazio
                    for ($row = 1; $row <= $lastRow; $row++) {
                        $valueA = $worksheet->getCell('A' . $row)->getValue();
                        if ($valueA == '' or $valueA == 'Total m�sa d�bito:' or $valueA == 'Total anoa d�bito:') {
                            $worksheet->removeRow($row);
                            $row--;
                            $lastRow--;
                        }
                    }

                    foreach ($worksheet->getColumnIterator('A') as $column) {
                        foreach ($column->getCellIterator() as $cell) {
                            $rowIndex = $cell->getRow();
                    
                            // Verifica se é a coluna 'A' e não é a primeira linha
                            if ($cell->getColumn() === 'A' && $rowIndex != 1) {
                                // Verificação específica para folha de pagamento
                                if (stripos($cell->getValue(), 'SALARIO') !== false || stripos($cell->getValue(), 'DEBITADO REF. SALARIO') !== false || stripos($cell->getValue(), 'Líquido') !== false) {
                                    $originalValue = $cell->getValue(); // Armazena o valor original
                    
                                    // Remover numeros
                                    $valueWithoutNumbers = preg_replace('/[0-9]/', '', $originalValue);
                    
                                    // Remover certos prefixos
                                    $prefixesToRemove = ['PAGAMENTO REF. SALARIO -', 'Líquido Adiantamento salário/ col.: -'];
                                    foreach ($prefixesToRemove as $prefix) {
                                        if (stripos($valueWithoutNumbers, $prefix) === 0) {
                                            $newValue = substr($valueWithoutNumbers, strlen($prefix));
                                            $cell->setValue($newValue);
                                            break;
                                        }
                                    }
                                } else {
                                    // Lógica para tratamento de outras entradas
                                    $value = $cell->getValue();
                                    $newValue = preg_replace('/[^0-9]/', '', $value);
                                    $cell->setValue($newValue);
                                }
                            }
                        }
                    }                   
                    
                    $uniqueValues = [];
                    $valueMap = []; // Mapa de A
                    $lastRowIndex = 1; // Inicia em q lastroW

                    // Processamento em batches
                    for ($row = 1; $row <= $lastRow; $row++) {                    

                        // SOMA do Débito e Crédito para Criar a Conciliação...
                        for ($row = 1; $row <= $lastRow; $row++) {
                            $valueA = trim($worksheet->getCell('A' . $row)->getValue());
                            $valueB = $worksheet->getCell('B' . $row)->getValue();
                            $valueD = $worksheet->getCell('E' . $row)->getValue();
                            $valueE = $worksheet->getCell('F' . $row)->getValue();
                    
                            // Remover todos os caracteres, exceto letras e números, de $valueA
                            $valueA = strtolower($valueA);
                            $valueA = preg_replace('/[^a-zA-Z0-9]/', '', $valueA);

                            if (in_array(trim($valueA), $uniqueValues)) {
                                // Valor em A repetido, mesclar células em A
                                $worksheet->mergeCells("A$row:A$lastRowIndex");

                                if (isset($valueMap[$valueA])) {
                                    $currentValueB = $worksheet->getCell('B' . $valueMap[$valueA])->getValue();
                                    $currentValueD = $worksheet->getCell('E' . $valueMap[$valueA])->getValue();
                                    $currentValueE = $worksheet->getCell('F' . $valueMap[$valueA])->getValue();

                            
                                    $worksheet->setCellValue('B' . $valueMap[$valueA], $currentValueB . ' ' . $valueB);
                                    $worksheet->setCellValue('E' . $valueMap[$valueA], $currentValueD . ' ' . $valueD);
                                    $worksheet->setCellValue('F' . $valueMap[$valueA], $currentValueE . ' ' . $valueE);
                                    $worksheet->removeRow($row);
                                    
                                }

                            } else {
                                $uniqueValues[] = $valueA;                                
                                $valueMap[$valueA] = $row; // Atualiza o mapa para a última linha de cada valor em A
                                
                            }
                            
                            $lastRowIndex = $row;
                        }
                    }

                    // Soma dos Rows
                    for ($row = 2; $row <= $lastRow; $row++) {
                        $valueRow1 = $worksheet->getCell('E' . $row)->getFormattedValue();
                        $valueRow2 = $worksheet->getCell('F' . $row)->getFormattedValue();

                        // remover os . e alterar as vírgulas
                        $valueRow1 = str_replace(['.', ','], ['', '.'], $valueRow1);
                        $valueRow2 = str_replace(['.', ','], ['', '.'], $valueRow2);

                        // string to float
                        $valueRow1 = floatval($valueRow1);
                        $valueRow2 = floatval($valueRow2);

                        // soma + definição de G baseado nos ROWS
                        $sum = $valueRow1 - $valueRow2;
                        $worksheet->setCellValue('G' . $row, $sum);
                    }

                    // clena o restante
                    for ($row = 1; $row <= $lastRow; $row++) {
                        $valueA = $worksheet->getCell('A' . $row)->getValue();
                        if ($valueA == '') {
                            $worksheet->removeRow($row);
                            $row--;
                            $lastRow--;
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
