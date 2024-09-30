<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;

// Функция для выгрузки данных в Excel с диаграммами
function export_to_excel() {
    // Создаем новую таблицу
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Получаем данные о пользователях
    $regionData = get_region_statistics();
    $ageData = get_age_statistics();
    $genderData = get_gender_statistics();

    // Заполняем таблицу данными о регионах
    $sheet->setCellValue('A1', 'Регионы');
    
    $row = 2;
    foreach ($regionData['regionGroups'] as $region => $count) {
        $sheet->setCellValue("A{$row}", $region);
        $sheet->setCellValue("B{$row}", $count);
        $row++;
    }
    
    // Создание диаграммы для распределения по регионам
    $dataSeriesLabels = [new DataSeriesValues('String', 'Worksheet!$B$1', null, 1)];
    $xAxisTickValues = [new DataSeriesValues('String', 'Worksheet!$A$2:$A$' . ($row - 1), null, 4)];
    $dataSeriesValues = [new DataSeriesValues('Number', 'Worksheet!$B$2:$B$' . ($row - 1), null, 4)];
    
    $series = new DataSeries(
        DataSeries::TYPE_PIECHART,
        null,
        range(0, count($dataSeriesValues) - 1),
        $dataSeriesLabels,
        $xAxisTickValues,
        $dataSeriesValues
    );

    $plotArea = new PlotArea(null, [$series]);
    $chart = new Chart(
        'Распределение по регионам',
        new Title('Распределение пользователей по регионам'),
        new Legend(Legend::POSITION_RIGHT, null, false),
        $plotArea
    );

    // Указываем, где будет отображаться диаграмма
    $chart->setTopLeftPosition('E2');
    $chart->setBottomRightPosition('K16');
    $sheet->addChart($chart);

    // Заполняем таблицу данными о возрасте
    $sheet->setCellValue('A20', 'Возрастная группа');
    $sheet->setCellValue('B20', 'Количество пользователей');
    
    $row = 21;
    foreach ($ageData['ageGroups'] as $ageGroup => $count) {
        $sheet->setCellValue("A{$row}", $ageGroup);
        $sheet->setCellValue("B{$row}", $count);
        $row++;
    }

    // Диаграмма для возрастных групп
    $dataSeriesLabels = [new DataSeriesValues('String', 'Worksheet!$B$20', null, 1)];
    $xAxisTickValues = [new DataSeriesValues('String', 'Worksheet!$A$21:$A$' . ($row - 1), null, 4)];
    $dataSeriesValues = [new DataSeriesValues('Number', 'Worksheet!$B$21:$B$' . ($row - 1), null, 4)];

    $series = new DataSeries(
        DataSeries::TYPE_BARCHART,
        null,
        range(0, count($dataSeriesValues) - 1),
        $dataSeriesLabels,
        $xAxisTickValues,
        $dataSeriesValues
    );

    $plotArea = new PlotArea(null, [$series]);
    $chart = new Chart(
        'Распределение по возрасту',
        new Title('Распределение пользователей по возрасту'),
        new Legend(Legend::POSITION_RIGHT, null, false),
        $plotArea
    );

    // Указываем, где будет отображаться диаграмма
    $chart->setTopLeftPosition('E20');
    $chart->setBottomRightPosition('K34');
    $sheet->addChart($chart);

    // Заполняем таблицу данными о поле
    $sheet->setCellValue('A40', 'Пол');
    
    $row = 41;
    foreach ($genderData['genderGroups'] as $gender => $count) {
        $sheet->setCellValue("A{$row}", $gender);
        $sheet->setCellValue("B{$row}", $count);
        $row++;
    }

    // Диаграмма для распределения по полу
    $dataSeriesLabels = [new DataSeriesValues('String', 'Worksheet!$B$40', null, 1)];
    $xAxisTickValues = [new DataSeriesValues('String', 'Worksheet!$A$41:$A$' . ($row - 1), null, 2)];
    $dataSeriesValues = [new DataSeriesValues('Number', 'Worksheet!$B$41:$B$' . ($row - 1), null, 2)];

    $series = new DataSeries(
        DataSeries::TYPE_PIECHART,
        null,
        range(0, count($dataSeriesValues) - 1),
        $dataSeriesLabels,
        $xAxisTickValues,
        $dataSeriesValues
    );

    $plotArea = new PlotArea(null, [$series]);
    $chart = new Chart(
        'Распределение по полу',
        new Title('Распределение пользователей по полу'),
        new Legend(Legend::POSITION_RIGHT, null, false),
        $plotArea
    );

    $chart->setTopLeftPosition('E40');
    $chart->setBottomRightPosition('K54');
    $sheet->addChart($chart);

    // Генерация Excel файла
    $writer = new Xlsx($spreadsheet);
    $writer->setIncludeCharts(true); // Включаем диаграммы

    // Отправляем файл для скачивания
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="user_data.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
}

// Регистрация AJAX экшена
add_action('wp_ajax_export_to_excel', 'export_to_excel');
add_action('wp_ajax_nopriv_export_to_excel', 'export_to_excel');