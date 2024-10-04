<?php
/*
Template Name: Статистика
*/

if (!isset($_COOKIE['user_id'])) {
    wp_redirect(home_url()); // Перенаправление на главную страницу, если пользователь не авторизован
    exit;
}

// Подключаем MongoDB и проверяем, является ли пользователь администратором
$user_id = $_COOKIE['user_id'];
$collection = get_mongo_connection()->users; // Подключаемся к коллекции users
$user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($user_id)]);

if (!$user || !isset($user['admin']) || !$user['admin']) {
    wp_redirect(home_url()); // Если пользователь не найден или не администратор, перенаправляем
    exit;
}

?>

<?php
get_header();
?>

<main class="min-h-screen bg-gray-100">
    <section class="relative flex justify-center items-center h-screen bg-[#131313] bg-cover bg-no-repeat bg-bottom"
        style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/main_bg.webp');">
        
        <div class="absolute w-full h-full top-[-4rem] left-0 z-10 pointer-events-none">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/circles.svg" alt="circles">
        </div>
        <!-- Полупрозрачный слой поверх фонового изображения -->
        <div class="absolute inset-0 bg-black opacity-50"></div>

        <!-- Контент, который будет поверх изображения -->
        <div class="px-8 md:px-14 lg:px-16 xl:px-8 2xl:px-24 3xl:px-28 pb-24 pt-28 w-full h-full flex flex-col  z-20 gap-6 ">
            <div class="w-full justify-between items-center flex flex-col sm:flex-row gap-4">
                <span class="text-4xl font-bold text-white ">
                    Статистика
                </span>
                <button id="export-data" class="bg-[#E53F0B] hover:bg-[#F35726] rounded-md flex items-center justify-between px-10 py-2 gap-3 duration-150 text-nowrap">
                    <span class="text-white font-bold text-sm">Выгрузить в Excel</span>
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/vscode-icons_file-type-excel.svg" alt="Excel Icon" class="w-6">
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <div class="flex flex-col gap-2 bg-[#131313]/40 backdrop-blur-lg rounded-lg border-[1px] border-white/10 p-4">
                <span class="text-white text-2xl font-bold">Популярные регионы</span>
                <div class="flex flex-col gap-2 min-h-[400px] h-full bg-[#131313] rounded-md border-[1px] border-[#fff]/10 p-4">
                    <?php
                    $regionData = get_region_statistics();

                    // Если есть данные
                    if (!isset($regionData['error']) && !empty($regionData)) {
                        // Выводим самый популярный регион
                        echo '<div class="flex justify-between text-white font-bold text-lg mb-4 border-b-[1px] border-b-[#fff]/10 pb-4">';
                        echo '<span>Самый популярный регион:</span>';
                        echo '<span>' . htmlspecialchars($regionData['mostPopularRegion'] ?? 'Нет данных') . '</span>';
                        echo '</div>';

                        // Вставляем место для диаграммы
                        echo '<div id="regionsChart" class="mb-4"></div>';
                        
                        // Подготовка данных для диаграммы
                        $region_names = json_encode(array_keys($regionData['regionGroups']));
                        $region_counts = json_encode(array_values($regionData['regionGroups']));
                    ?>

                    <!-- Скрипт для диаграммы -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var options = {
                                chart: {
                                    type: 'pie',
                                    height: 350
                                },
                                series: <?php echo $region_counts; ?>, // Количество пользователей по регионам
                                labels: <?php echo $region_names; ?>, // Названия регионов
                                colors: ['#FF5733', '#FFBD33', '#28B463', '#3498DB', '#8E44AD', '#F39C12'],
                                title: {
                                    text: 'Распределение по регионам',
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

                            var chart = new ApexCharts(document.querySelector("#regionsChart"), options);
                            chart.render();
                        });
                    </script>

                    <?php
                        // Выводим распределение по регионам
                        foreach ($regionData['regionGroups'] as $region => $count) {
                            ?>
                            <div class="flex w-full justify-between items-center">
                                <span class="text-white font-bold text-lg"><?php echo htmlspecialchars($region); ?></span>
                                <span class="text-white/60 text-sm flex items-center"><?php echo intval($count); ?></span>
                            </div>
                            <?php
                        }
                    } else {
                        // Если произошла ошибка или данных нет
                        echo '<div class="text-white font-bold text-center">Нет данных</div>';
                    }
                    ?>
                </div>
            </div>


            <div class="flex flex-col gap-2 bg-[#131313]/40 backdrop-blur-lg rounded-lg border-[1px] border-white/10 p-4">
                <span class="text-white text-2xl font-bold">Возраст пользователей</span>
                    <div class="flex flex-col gap-2 min-h-[400px] h-full bg-[#131313] rounded-md border-[1px] border-[#fff]/10 p-4">
                        <?php
                        $ageData = get_age_statistics();

                        // Если есть данные
                        if (!isset($ageData['error']) && !empty($ageData)) {
                            // Выводим средний возраст
                            echo '<div class="flex justify-between text-white font-bold text-lg mb-4 border-b-[1px] border-b-[#fff]/10 pb-4">';
                            echo '<span>Средний возраст:</span>';
                            echo '<span>' . intval($ageData['averageAge']) . ' лет</span>';
                            echo '</div>';

                            // Вставляем место для диаграммы
                            echo '<div id="ageChart" class="mb-4"></div>';
                            
                            // Подготовка данных для диаграммы
                            $age_groups = json_encode(array_keys($ageData['ageGroups']));
                            $age_counts = json_encode(array_values($ageData['ageGroups']));
                        ?>

                        <!-- Скрипт для диаграммы -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                var options = {
                                    chart: {
                                        type: 'pie',
                                        height: 350
                                    },
                                    series: <?php echo $age_counts; ?>, // Количество пользователей по возрастным группам
                                    labels: <?php echo $age_groups; ?>, // Возрастные группы
                                    colors: ['#FF5733', '#33FFBD', '#33A8FF', '#FFBD33'],
                                    title: {
                                        text: 'Распределение по возрасту',
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

                                var chart = new ApexCharts(document.querySelector("#ageChart"), options);
                                chart.render();
                            });
                        </script>

                        <?php
                            // Выводим распределение по возрастным группам
                            foreach ($ageData['ageGroups'] as $ageGroup => $count) {
                                ?>
                                <div class="flex w-full justify-between items-center">
                                    <span class="text-white font-bold text-lg"><?php echo htmlspecialchars($ageGroup); ?></span>
                                    <span class="text-white/60 text-sm flex items-center"><?php echo intval($count); ?></span>
                                </div>
                                <?php
                            }
                        } else {
                            // Если произошла ошибка или данных нет
                            echo '<div class="text-white font-bold text-center">Нет данных</div>';
                        }
                        ?>
                    </div>
            </div>

                
            <div class="flex flex-col gap-2 bg-[#131313]/40 backdrop-blur-lg rounded-lg border-[1px] border-white/10 p-4">
    <span class="text-white text-2xl font-bold">Популярный пол пользователей</span>
    <div class="flex flex-col gap-2 min-h-[400px] h-full bg-[#131313] rounded-md border-[1px] border-[#fff]/10 p-4">
        <?php
        $genderData = get_gender_statistics();

        // Если есть данные
        if (!isset($genderData['error']) && !empty($genderData)) {
            // Определяем самый популярный пол
            $mostPopularGender = array_keys($genderData['genderGroups'], max($genderData['genderGroups']))[0];
            echo '<div class="flex justify-between text-white font-bold text-lg mb-4 border-b-[1px] border-b-[#fff]/10 pb-4">';
            echo '<span>Самый популярный пол:</span>';
            echo '<span>' . htmlspecialchars($mostPopularGender) . '</span>';
            echo '</div>';

            // Вставляем место для диаграммы
            echo '<div id="genderChart" class="mb-4"></div>';
            
            // Подготовка данных для диаграммы
            $gender_names = json_encode(array_keys($genderData['genderGroups']));
            $gender_counts = json_encode(array_values($genderData['genderGroups']));
        ?>

        <!-- Скрипт для диаграммы -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var options = {
                    chart: {
                        type: 'pie',
                        height: 350
                    },
                    series: <?php echo $gender_counts; ?>, // Количество пользователей по полу
                    labels: <?php echo $gender_names; ?>, // Названия пола
                    colors: ['#3498DB', '#E74C3C'],
                    title: {
                        text: 'Распределение по полу',
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

                var chart = new ApexCharts(document.querySelector("#genderChart"), options);
                chart.render();
            });
        </script>

        <?php
            // Выводим распределение по полу
            foreach ($genderData['genderGroups'] as $gender => $count) {
                ?>
                <div class="flex w-full justify-between items-center">
                    <span class="text-white font-bold text-lg"><?php echo htmlspecialchars($gender); ?></span>
                    <span class="text-white/60 text-sm flex items-center"><?php echo intval($count); ?></span>
                </div>
                <?php
            }
        } else {
            // Если произошла ошибка или данных нет
            echo '<div class="text-white font-bold text-center">Нет данных</div>';
        }
        ?>
    </div>
</div>




            </div>
        </div>
    </section>
    <script>
document.getElementById('export-data').addEventListener('click', function () {
    // Отправляем AJAX запрос на сервер для выгрузки данных
    window.location.href = ajax_object.ajax_url + '?action=export_to_excel';
});
        </script>
        <script>
jQuery(document).ready(function ($) {
    // Обработчик для кнопки "Разыграть"
    $('#start-randomizer').on('click', function () {
        const participantCount = parseInt($('#randomizer-participant-count').val());
        const winnerCount = parseInt($('#randomizer-winner-count').val());

        // Проверка корректных значений
        if (isNaN(participantCount) || isNaN(winnerCount) || participantCount < 1 || winnerCount < 1 || winnerCount > participantCount) {
            alert('Введите корректные значения для участников и победителей.');
            return;
        }

        // Генерация случайных победителей
        const participants = Array.from({ length: participantCount }, (_, i) => i + 1);
        const winners = [];

        while (winners.length < winnerCount) {
            const randomIndex = Math.floor(Math.random() * participants.length);
            winners.push(participants.splice(randomIndex, 1)[0]);
        }

        // Отображение результатов
        const resultContainer = $('#randomizer-result');
        resultContainer.html(`<p><strong>Победители:</strong> ${winners.join(', ')}</p>`);
        resultContainer.removeClass('hidden');
    });

    // Сброс формы и скрытие результатов при закрытии модального окна
    $(document).on('closing', '.remodal', function () {
        $('#randomizer-participant-count').val('');
        $('#randomizer-winner-count').val('');
        $('#randomizer-result').addClass('hidden'); // Скрыть результат
    });
});

            </script>
</main>

