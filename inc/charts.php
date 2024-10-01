<?php

function get_mongo_chart_data() {
    // Подключение к MongoDB
    $db = get_mongo_connection();
    
    if (!$db) {
        return json_encode(['error' => 'Ошибка подключения к MongoDB']);
    }

    $collection = $db->statistics;

    try {
        $results = $collection->aggregate([
            ['$group' => ['_id' => '$region', 'count' => ['$sum' => 1]]]
        ]);

        $regions = [];
        $user_counts = [];

        foreach ($results as $entry) {
            $regions[] = $entry->_id;
            $user_counts[] = $entry->count;
        }

        return [
            'regions' => $regions,
            'user_counts' => $user_counts
        ];
    } catch (Exception $e) {
        return json_encode(['error' => 'Ошибка при запросе данных: ' . $e->getMessage()]);
    }
}


// Шорткод для круговой диаграммы с MongoDB
function render_mongo_piechart() {
    $data = get_mongo_chart_data();
    $regions = json_encode($data['regions']);
    $user_counts = json_encode($data['user_counts']);

    ob_start(); ?>
    <div id="mongoPieChart"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var options = {
                chart: {
                    type: 'pie',
                    height: 350
                },
                series: <?php echo $user_counts; ?>, // Количество пользователей по регионам
                labels: <?php echo $regions; ?>, // Названия регионов
                colors: ['#FF5733', '#FFBD33', '#28B463', '#3498DB', '#8E44AD', '#F39C12'],
                title: {
                    text: 'Распределение пользователей по регионам',
                    align: 'center'
                },
                legend: {
                    position: 'bottom'
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 300
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            var chart = new ApexCharts(document.querySelector("#mongoPieChart"), options);
            chart.render();
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('mongo_piechart', 'render_mongo_piechart');

add_shortcode('mongo_apexchart', 'render_mongo_apexchart');

function get_region_statistics() {
    $db = get_mongo_connection();
    $ticketsCollection = $db->statistics;

    try {
        $pipeline = [
            ['$group' => ['_id' => '$region', 'count' => ['$sum' => 1]]],
            ['$sort' => ['count' => -1]]
        ];

        $results = $ticketsCollection->aggregate($pipeline)->toArray();
        $regionGroups = [];
        $totalUsers = 0;

        foreach ($results as $entry) {
            $region = $entry['_id'];
            $count = $entry['count'];
            $regionGroups[$region] = $count;
            $totalUsers += $count;
        }

        // Определяем самый популярный регион
        arsort($regionGroups);
        $mostPopularRegion = key($regionGroups);

        // Подсчитываем процентное соотношение
        $averageRegion = [];
        foreach ($regionGroups as $region => $count) {
            $percentage = ($totalUsers > 0) ? round(($count / $totalUsers) * 100, 2) : 0;
            $averageRegion[$region] = $percentage;
        }

        return [
            'regionGroups' => $regionGroups,
            'averageRegion' => $averageRegion,
            'mostPopularRegion' => $mostPopularRegion,
            'totalUsers' => $totalUsers
        ];
    } catch (Exception $e) {
        return ['error' => 'Ошибка при получении данных: ' . $e->getMessage()];
    }
}

function get_age_statistics() {
    $db = get_mongo_connection();
    $ticketsCollection = $db->statistics;

    try {
        $pipeline = [
            ['$group' => ['_id' => '$age', 'count' => ['$sum' => 1]]],
            ['$sort' => ['_id' => 1]]
        ];

        $results = $ticketsCollection->aggregate($pipeline)->toArray();
        $ageGroups = [
            '18-28' => 0,
            '29-39' => 0,
            '40-50' => 0,
            '50+' => 0
        ];
        $totalAge = 0;
        $userCount = 0;

        foreach ($results as $entry) {
            $age = intval($entry['_id']);
            $count = intval($entry['count']);

            // Увеличиваем счетчики для соответствующих возрастных групп
            if ($age >= 18 && $age <= 28) {
                $ageGroups['18-28'] += $count;
            } elseif ($age >= 29 && $age <= 39) {
                $ageGroups['29-39'] += $count;
            } elseif ($age >= 40 && $age <= 50) {
                $ageGroups['40-50'] += $count;
            } else {
                $ageGroups['50+'] += $count;
            }

            // Для расчета среднего возраста
            $totalAge += $age * $count;
            $userCount += $count;
        }

        $averageAge = $userCount > 0 ? round($totalAge / $userCount) : 0;

        return [
            'ageGroups' => $ageGroups,
            'averageAge' => $averageAge
        ];
    } catch (Exception $e) {
        return ['error' => 'Ошибка при получении данных: ' . $e->getMessage()];
    }
}

function get_gender_statistics() {
    $db = get_mongo_connection();
    $ticketsCollection = $db->statistics;

    try {
        $pipeline = [
            ['$group' => ['_id' => '$gender', 'count' => ['$sum' => 1]]],
            ['$sort' => ['count' => -1]]
        ];

        $results = $ticketsCollection->aggregate($pipeline)->toArray();
        $genderGroups = [
            'Мужчины' => 0,
            'Женщины' => 0
        ];
        $totalUsers = 0;

        foreach ($results as $entry) {
            $gender = $entry['_id'];
            $count = $entry['count'];

            // Преобразуем значение gender в русский эквивалент
            if ($gender === 'male') {
                $genderGroups['Мужчины'] += $count;
            } elseif ($gender === 'female') {
                $genderGroups['Женщины'] += $count;
            }

            $totalUsers += $count;
        }

        $averageGender = [];
        foreach ($genderGroups as $gender => $count) {
            $averageGender[$gender] = ($totalUsers > 0) ? round(($count / $totalUsers) * 100, 2) : 0;
        }

        return [
            'genderGroups' => $genderGroups,
            'averageGender' => $averageGender,
            'totalUsers' => $totalUsers
        ];
    } catch (Exception $e) {
        return ['error' => 'Ошибка при получении данных: ' . $e->getMessage()];
    }
}
