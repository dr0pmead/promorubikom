<?php
get_header();
?>

<main class="min-h-screen relative bg-[#131313] bg-cover bg-no-repeat bg-bottom flex items-center justify-center w-full" 
    style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/main_bg.webp');">

    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
    
    <div class="relative z-10">
        
        <section class="relative flex justify-center items-center py-24 pt-48">
            <div class="w-full flex items-center justify-center flex-col text-center">
                <h1 class="text-white font-bold text-3xl md:text-4xl lg:text-6xl leading-tight tracking-wide">
                    Участвуйте в <span class="before:block before:absolute before:-inset-1 before:skew-y-2 before:bg-[#FF4A1D] relative inline-block"><span class="relative text-white">розыгрышах</span></span> <br> от компании Рубиком
                </h1>

                <!-- <div class="mt-10">
                <button data-remodal-target="modal-register" id="open-modal" class="bg-[#FF4A1D] hover:bg-[#FF6B3D] text-white font-semibold text-lg px-8 py-2 rounded-lg transition-all duration-300 items-center flex max-w-[225px] justify-center">
                    Участвовать <span class="ml-2"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/pepicons-print_arrow-left.svg" alt="circles" class="w-6"></span>
                </button>
                </div> -->
            </div>
        </section>

        <section class="py-16 px-4 md:px-8 lg:px-16 xl:px-24">
            <?php
                // Параметры запроса
                $args = array(
                    'post_type'      => 'promoactions', // Тип поста promoactions
                    'posts_per_page' => -1, // Вывести все записи
                    'orderby'        => 'date',
                    'order'          => 'ASC',
                    'meta_query'     => array( // Условие на метаполе
                        array(
                            'key'   => '_display_on_homepage', // Имя метаполя
                            'value' => 'yes', // Значение метаполя должно быть 'yes'
                        ),
                    ),
                );

                // Запрос постов
                $promoactions_query = new WP_Query($args);

                if ($promoactions_query->have_posts()) {
                    echo '<h2 class="text-white text-3xl md:text-4xl font-bold text-center mb-8">Действующие акции</h2>';
                } else {
                    // Если постов нет, выводим сообщение "Сейчас мы не проводим акции"
                    echo '<h2 class="text-white text-3xl md:text-4xl font-bold text-center mb-8">Сейчас мы не проводим акции :( </h2>';
                }
                ?>
                
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">

                <?
                if ($promoactions_query->have_posts()) :
                    while ($promoactions_query->have_posts()) : $promoactions_query->the_post(); 
                        $post_id = get_the_ID();
                        $image_url = get_the_post_thumbnail_url($post_id, 'full');
                        $description = get_the_excerpt();
                        $actionID = basename(get_permalink($post_id));

                        ?>
                        <div class="bg-[#131313]/50 backdrop-blur-lg rounded-lg border-[1px] border-white/10 p-6 shadow-lg">
                            <h3 class="text-white font-bold text-xl mb-4"><?php the_title(); ?></h3>
                            <p class="text-gray-300 text-sm mb-4"><?php echo esc_html($description); ?></p>
                            <div class="flex justify-start gap-4">
                                <button data-remodal-target="register-modal-<?php echo $actionID; ?>" class="bg-[#FF4A1D] hover:bg-[#F35726] text-white px-4 py-2 rounded-md font-bold flex items-center justify-center">
                                    Участвовать <span class="ml-2"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/pepicons-print_arrow-left.svg" alt="circles" class="w-6"></span>
                                </button>
                                <button data-remodal-target="action-modal-<?php echo $actionID; ?>" class="text-white px-2 py-2 rounded-md font-regular flex items-center justify-center">
                                    Условия <span class="ml-2"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/maki_arrow.svg" alt="circles" class="w-3"></span>
                                </button>
                            </div>
                        </div>
                
                        <div class="bg-[#131313] border-[1px] border-[#fff]/10 p-8 rounded-lg text-center max-w-md mx-auto remodal" data-remodal-id="register-modal-<?php echo $actionID; ?>" role="dialog" aria-labelledby="modalTitle" aria-describedby="modalDesc">
                        <h1 id="modal-title" class="text-2xl font-bold text-white mb-6">Регистрация участия</h1>

                            <div id="error-message" class="text-sm text-white mb-4 hidden"></div>

                            <form id="registration-form-<? echo $actionID ?>" method="POST" enctype="multipart/form-data">
                                <div class="hidden-honeypot" style="display:none;">
                                    <input type="text" name="bot_field" value="">
                                </div>

                                <input type="hidden" name="form_time" id="form_time-<? echo $actionID ?>" value="<?php echo time(); ?>">

                                <div class="mb-4">
                                    <input type="tel" name="phone" placeholder="+7 777 777 77 77" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600" required>
                                </div>

                                <div class="mb-4">
                                    <input type="text" name="name" placeholder="Ваше имя" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600" required>
                                </div>

                                <div class="mb-4">
                                    <input type="text" name="firstname" placeholder="Ваша фамилия" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600" required>
                                </div>

                                <div class="mb-4">
                                    <select name="region" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600" required>
                                        <option value="" disabled selected>Ваш регион</option>
                                        <?php
                                        $regions = new WP_Query( array(
                                            'post_type' => 'region',
                                            'posts_per_page' => -1,
                                            'orderby' => 'title',
                                            'order' => 'ASC'
                                        ));

                                        if ( $regions->have_posts() ) :
                                            while ( $regions->have_posts() ) : $regions->the_post(); ?>
                                                <option value="<?php the_title(); ?>"><?php the_title(); ?></option>
                                            <?php endwhile;
                                            wp_reset_postdata();
                                        endif;
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <input type="number" name="age" min="18" max="100"  pattern="\d*" placeholder="Ваш возраст" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600" required min="1" max="100">
                                </div>

                                <div class="flex items-center mb-6 justify-start space-x-4">
                                    <label class="text-white flex items-center gap-3">
                                        <input type="radio" name="gender" value="male" class="mr-2 bg-[#E53F0B] focus:ring-[#E53F0B]" required> Мужской
                                    </label>
                                    <label class="text-white flex items-center gap-3">
                                        <input type="radio" name="gender" value="female" class="mr-2 bg-[#E53F0B] focus:ring-[#E53F0B]" required> Женский
                                    </label>
                                </div>

                                <div id="dynamic-field-<?php echo $actionID; ?>" class="mb-4">
                                    <?php
                                    $args = array(
                                        'name'        => $actionID, // Это basename постоянной ссылки
                                        'post_type'   => 'promoactions', // Ваш кастомный тип поста
                                        'post_status' => 'publish',
                                        'numberposts' => 1
                                    );
                                    $posts = get_posts($args);
                                    $post = $posts[0]; // Получаем пост

                                    $condition_type = get_post_meta($post->ID, 'promo_condition_type', true);

                                    if (!empty($posts)) {
                                        $post = $posts[0]; // Получаем пост
    
                                        $conditions = get_post_meta($post->ID, 'promo_conditions', true);

                                        if ($condition_type === 'photo') {
                                            // Если тип фото, выводим поле для загрузки изображения
                                            ?>
                                            <input type="file" name="receipt_image" id="receipt-image-<?php echo $actionID; ?>" accept="image/*"
                                                class="w-full text-gray-300 bg-[#131313] px-4 py-3 border-[1px] border-[#fff]/10 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600
                                                file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold
                                                file:bg-[#E53F0B] file:text-white file:duration-150 file:cursor-pointer hover:file:bg-[#F35726]" required>
                                            <?php
                                        } else {
                                            // Если тип текст, выводим обычное текстовое поле
                                            ?>
                                            <input type="text" name="condition_text" id="condition-text-<?php echo $actionID; ?>" placeholder="Введите текст"
                                                class="w-full text-gray-300 bg-[#131313] px-4 py-3 border-[1px] border-[#fff]/10 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600"
                                                required>
                                            <?php
                                        }

                                    } else {
                                        // Если пост не найден, выводим сообщение об ошибке
                                        echo '<p>Пост с данным ID не найден.</p>';
                                    }    


                                    
                                    ?>
                                </div>

                                <button type="submit" id="submit-registration-<? echo $actionID ?>" class="disabled:bg-[#E53F0B]/50 bg-[#E53F0B] hover:bg-[#F35726] text-white px-6 py-3 rounded-md w-full transition-colors font-bold flex items-center justify-center">
                                    <span class="btn-text">Зарегистрироваться</span>
                                    <span class="btn-spinner hidden animate-spin fill-white h-5 w-5">
                                        <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2.501 8C2.501 6.91221 2.82357 5.84884 3.42792 4.94437C4.03227 4.0399 4.89125 3.33495 5.89624 2.91867C6.90123 2.50238 8.0071 2.39347 9.074 2.60568C10.1409 2.8179 11.1209 3.34173 11.8901 4.11092C12.6593 4.8801 13.1831 5.86011 13.3953 6.92701C13.6075 7.9939 13.4986 9.09977 13.0823 10.1048C12.6661 11.1098 11.9611 11.9687 11.0566 12.5731C10.1522 13.1774 9.0888 13.5 8.001 13.5C7.80209 13.4999 7.61127 13.5788 7.47052 13.7193C7.32978 13.8599 7.25063 14.0506 7.2505 14.2495C7.25037 14.4484 7.32926 14.6392 7.46982 14.78C7.61037 14.9207 7.80109 14.9999 8 15C9.38447 15 10.7378 14.5895 11.889 13.8203C13.0401 13.0511 13.9373 11.9579 14.4672 10.6788C14.997 9.3997 15.1356 7.99224 14.8655 6.63437C14.5954 5.2765 13.9287 4.02922 12.9497 3.05026C11.9708 2.07129 10.7235 1.4046 9.36563 1.13451C8.00777 0.86441 6.6003 1.00303 5.32122 1.53285C4.04213 2.06266 2.94888 2.95987 2.17971 4.11101C1.41054 5.26216 1 6.61553 1 8C1 8.19905 1.07907 8.38994 1.21982 8.53069C1.36056 8.67143 1.55145 8.7505 1.7505 8.7505C1.94954 8.7505 2.14044 8.67143 2.28118 8.53069C2.42193 8.38994 2.501 8.19905 2.501 8Z"/>
                                        </svg>
                                    </span>
                                </button>
                            </form>
                        </div>
                
                        <div class="bg-[#131313] border-[1px] border-[#fff]/10 p-8 rounded-lg text-center max-w-md mx-auto remodal" data-remodal-id="action-modal-<?php echo $actionID; ?>" role="dialog" aria-labelledby="modalTitle" aria-describedby="modalDesc">
                            <h1 id="modal-title" class="text-2xl font-bold text-white mb-6">Условия акции</h1>

                            <div class="modal-content">
                                <?php
                                // Логируем получение actionID
                                error_log('ID текущего поста для условий: ' . $actionID);

                                // Находим пост по basename постоянной ссылки
                                $args = array(
                                    'name'        => $actionID, // Это basename постоянной ссылки
                                    'post_type'   => 'promoactions', // Ваш кастомный тип поста
                                    'post_status' => 'publish',
                                    'numberposts' => 1
                                );
                                $posts = get_posts($args);

                                if (!empty($posts)) {
                                    $post = $posts[0]; // Получаем пост

                                    // Получаем условия из мета-поля
                                    $conditions = get_post_meta($post->ID, 'promo_conditions', true);

                                    if (!empty($conditions)) {
                                        echo '<ul class="text-left">';
                                        foreach ($conditions as $condition) {
                                            echo '<li class="text-white mb-2">• ' . esc_html($condition) . '</li>';
                                        }
                                        echo '</ul>';
                                    } else {
                                        echo '<p>Условия акции не указаны.</p>';
                                    }
                                } else {
                                    // Если пост не найден, выводим сообщение об ошибке
                                    echo '<p>Пост с данным ID не найден.</p>';
                                }
                                ?>
                            </div>

                            <button data-remodal-action="close" class="remodal-cancel bg-[#FF4A1D] hover:bg-[#F35726] text-white px-6 py-3 rounded-md">Закрыть</button>
                        </div>
                    <?php
    endwhile;
    wp_reset_postdata();
else :
    echo 'Акции не найдены.';
endif;
?>
            </div>
        </section>

</div>

</main>

<!-- Модальное окно для рандомайзера -->
<div class="remodal bg-[#131313] border-[1px] border-[#fff]/10 p-8 rounded-lg text-center max-w-md mx-auto" data-remodal-id="modal-randomizer" role="dialog" aria-labelledby="modalTitle" aria-describedby="modalDesc" data-remodal-options="hashTracking: false, closeOnOutsideClick: false">
    <h1 id="modalTitle" class="text-2xl font-bold text-white mb-6">Рандомайзер</h1>

    <!-- Поле для ввода количества участников -->
    <input type="number" id="randomizer-participant-count" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600 mb-6" placeholder="Количество участников" min="1">

    <!-- Поле для ввода количества победителей -->
    <input type="number" id="randomizer-winner-count" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600 mb-6" placeholder="Количество победителей" min="1">

    <!-- Кнопка "Разыграть" -->
    <button id="start-randomizer" class="bg-[#E53F0B] hover:bg-[#F35726] text-white px-6 py-3 rounded-md w-full transition-colors font-bold">
        Разыграть
    </button>

    <!-- Контейнер для отображения победителей, скрыт по умолчанию -->
    <div id="randomizer-result" class="text-white mt-6 hidden"></div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Определяем поле на основе условий
        var conditionType = '<?php echo $condition_type; ?>'; // Получаем тип условия из PHP
        
        if (conditionType === 'photo') {
            $('#condition-text-<?php echo $actionID; ?>').removeAttr('required'); // Убираем required у текстового поля, если оно было
            $('#receipt-image-<?php echo $actionID; ?>').attr('required', true); // Добавляем required для поля загрузки фото
        } else {
            $('#receipt-image-<?php echo $actionID; ?>').removeAttr('required'); // Убираем required у поля для загрузки фото, если оно было
            $('#condition-text-<?php echo $actionID; ?>').attr('required', true); // Добавляем required для текстового поля
        }
    });
</script>

<script>
jQuery(document).ready(function ($) {
    // Обработчик отправки формы
    $('form[id^="registration-form-"]').on('submit', function (e) {
        e.preventDefault(); // Предотвращаем стандартную отправку формы

        var form = $(this);
        var actionID = form.attr('id').split('registration-form-')[1]; // Получаем actionID из id формы
        var formData = new FormData(form[0]); // Получаем данные формы

        formData.append('action', 'handle_ticket_registration'); // Добавляем action для AJAX
        formData.append('promoaction', actionID); // Добавляем promoaction в данные формы

        var errorMessage = $('#error-message'); // Элемент для отображения сообщений

        // Сбрасываем сообщение перед отправкой формы
        errorMessage.text('').addClass('hidden').removeClass('text-green-500 text-red-500');

        // Показать спиннер и скрыть текст кнопки во время отправки
        form.find('.btn-spinner').removeClass('hidden');
        form.find('.btn-text').addClass('hidden');

        // Отправка данных через AJAX
        $.ajax({
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                form.find('.btn-spinner').addClass('hidden');
                form.find('.btn-text').removeClass('hidden');

                if (response.success) {
                    // Успешная регистрация — выводим сообщение в модальном окне
                    errorMessage.text(response.data.message).removeClass('hidden').addClass('text-green-500');
                    form[0].reset(); // Очистить форму
                    
                    // Закрытие модального окна через 3 секунды
                    setTimeout(function () {
                        errorMessage.addClass('hidden'); // Скрыть сообщение
                        $('[data-remodal-id="register-modal-' + actionID + '"]').remodal().close(); // Закрыть модальное окно
                        location.reload();
                    }, 1500); // Закрытие через 3 секунды

                    
                } else {
                    // Ошибка — выводим сообщение в модальном окне
                    errorMessage.text(response.data.message).removeClass('hidden').addClass('text-red-500');
                }
            },
            error: function () {
                errorMessage.text('Ошибка на сервере. Попробуйте позже.').removeClass('hidden').addClass('text-red-500');
                form.find('.btn-spinner').addClass('hidden');
                form.find('.btn-text').removeClass('hidden');
            }
        });
    });
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