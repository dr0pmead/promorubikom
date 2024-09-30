<?php

function get_mongo_chart_data() {
    // Подключение к MongoDB
    $db = get_mongo_connection();
    
    if (!$db) {
        return json_encode(['error' => 'Ошибка подключения к MongoDB']);
    }

    $collection = $db->tickets;

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
    // Подключаемся к MongoDB
    $db = get_mongo_connection();
    $ticketsCollection = $db->tickets;
    $usersCollection = $db->users;

    // Находим все уникальные owner_id в тикетах
    try {
        $results = $ticketsCollection->distinct('owner_id');
        $regionGroups = [];
        $totalUsers = 0;

        // Для каждого пользователя находим его регион
        foreach ($results as $ownerId) {
            $user = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($ownerId)]);

            if ($user && isset($user['region'])) {
                $totalUsers++; // Увеличиваем общий счетчик пользователей
                $region = $user['region'];

                // Увеличиваем количество пользователей в соответствующем регионе
                if (isset($regionGroups[$region])) {
                    $regionGroups[$region]++;
                } else {
                    $regionGroups[$region] = 1;
                }
            }
        }

        // Подсчитываем процентное соотношение
        $averageRegion = [];
        foreach ($regionGroups as $region => $count) {
            if ($totalUsers > 0) {
                $percentage = ($count / $totalUsers) * 100;
                $averageRegion[$region] = round($percentage, 2); // Округляем до двух знаков
            } else {
                $averageRegion[$region] = 0;
            }
        }

        // Определяем самый популярный регион
        arsort($regionGroups); // Сортируем регионы по убыванию
        $mostPopularRegion = key($regionGroups); // Первый элемент будет самым популярным

        // Возвращаем данные по регионам, среднее значение и самый популярный регион
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


function get_age_from_birthday($birthday) {
    // Преобразуем строку в формат даты
    $birthDate = DateTime::createFromFormat('d.m.Y', $birthday);
    if ($birthDate === false) {
        return null; // Вернем null, если дата невалидная
    }

    // Текущая дата
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;

    return $age;
}

function get_age_statistics() {
    // Подключаемся к MongoDB
    $db = get_mongo_connection();
    $ticketsCollection = $db->tickets;
    $usersCollection = $db->users;

    // Находим все уникальные owner_id в тикетах
    try {
        $results = $ticketsCollection->distinct('owner_id');
        $ageGroups = [
            '18-28' => 0,
            '29-39' => 0,
            '40-50' => 0,
            '50+' => 0
        ];
        $totalAge = 0;
        $userCount = 0;

        // Для каждого пользователя находим возраст
        foreach ($results as $ownerId) {
            $user = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($ownerId)]);

            if ($user && isset($user['age'])) {
                // Получаем возраст из даты рождения
                $age = get_age_from_birthday($user['age']);

                if ($age !== null) {
                    // Увеличиваем счетчики для соответствующих групп
                    if ($age >= 18 && $age <= 28) {
                        $ageGroups['18-28']++;
                    } elseif ($age >= 29 && $age <= 39) {
                        $ageGroups['29-39']++;
                    } elseif ($age >= 40 && $age <= 50) {
                        $ageGroups['40-50']++;
                    } else {
                        $ageGroups['50+']++;
                    }

                    // Считаем общую сумму возрастов и количество пользователей
                    $totalAge += $age;
                    $userCount++;
                }
            }
        }

        // Рассчитываем средний возраст
        $averageAge = $userCount > 0 ? round($totalAge / $userCount) : 0;

        // Возвращаем данные по возрастным группам и среднему возрасту
        return [
            'ageGroups' => $ageGroups,
            'averageAge' => $averageAge
        ];
    } catch (Exception $e) {
        return ['error' => 'Ошибка при получении данных: ' . $e->getMessage()];
    }
}

function get_gender_statistics() {
    // Подключаемся к MongoDB
    $db = get_mongo_connection();
    $ticketsCollection = $db->tickets;
    $usersCollection = $db->users;

    // Находим все уникальные owner_id в тикетах
    try {
        $results = $ticketsCollection->distinct('owner_id');
        $genderGroups = [
            'Мужчины' => 0,
            'Женщины' => 0
        ];
        $totalUsers = 0;

        // Для каждого пользователя находим его пол
        foreach ($results as $ownerId) {
            $user = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($ownerId)]);

            if ($user && isset($user['gender'])) {
                $totalUsers++; // Увеличиваем общий счетчик пользователей
                // Преобразуем значения gender в русские эквиваленты
                if ($user['gender'] === 'male') {
                    $genderGroups['Мужчины']++;
                } elseif ($user['gender'] === 'female') {
                    $genderGroups['Женщины']++;
                }
            }
        }

        // Подсчитываем процентное соотношение
        $averageGender = [];
        foreach ($genderGroups as $gender => $count) {
            if ($totalUsers > 0) {
                $percentage = ($count / $totalUsers) * 100;
                $averageGender[$gender] = round($percentage, 2); // Округляем до двух знаков
            } else {
                $averageGender[$gender] = 0;
            }
        }

        // Возвращаем данные по группам пола и среднее значение
        return [
            'genderGroups' => $genderGroups,
            'averageGender' => $averageGender,
            'totalUsers' => $totalUsers
        ];
    } catch (Exception $e) {
        return ['error' => 'Ошибка при получении данных: ' . $e->getMessage()];
    }
}

