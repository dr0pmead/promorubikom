<?php
get_header();
?>

<main class="min-h-screen relative bg-[#131313] bg-cover bg-no-repeat bg-bottom" 
    style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/main_bg.webp');">

    <!-- Полупрозрачный слой поверх фонового изображения для затемнения -->
    <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
    
    <!-- Контент страниц -->
    <div class="relative z-10">
        
        <!-- Первая секция -->
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

<!-- Блок с условиями участия -->
<!-- Главный блок акций -->
<section class="py-16 px-4 md:px-8 lg:px-16 xl:px-24">
    <h2 class="text-white text-3xl md:text-4xl font-bold text-center mb-8">Действующие акции</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
        <?php
        $args = array(
            'post_type'      => 'promoactions',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'ASC'
        );

        $promoactions_query = new WP_Query($args);

        if ($promoactions_query->have_posts()) :
            while ($promoactions_query->have_posts()) : $promoactions_query->the_post(); 
                // Получаем миниатюру записи
                $image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                // Получаем описание из основного контента записи (the_content)
                $description = get_the_excerpt();
                ?>
                <!-- Один блок акции -->
                <div class="w-full max-w-4xl mx-auto bg-[#131313]/50 backdrop-blur-lg rounded-lg border-[1px] border-white/10 p-8 ">
                    <div class="condition-item flex flex-col items-center text-center">
                        <h3 class="text-white font-bold text-xl mb-2"><?php the_title(); ?></h3>
                        <p class="text-gray-300 text-sm">
                            <?php echo esc_html($description); ?>
                        </p>
                        <!-- Кнопка для участия -->
                         <div class="flex gap-4 items-center">
                            <button class="mt-4 bg-[#FF4A1D] hover:bg-[#F35726] text-white px-4 py-2 rounded-md font-bold flex gap-2" data-remodal-target="register-modal-<?php echo get_the_ID(); ?>">
                                Участвовать
                            </button>
                            <button class="mt-4 text-white px-2 py-2 rounded-md" data-remodal-target="conditions-<?php echo get_the_ID(); ?>">
                                Условия
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Модальное окно с условиями -->
                <div class="bg-[#131313] border-[1px] border-[#fff]/10 p-8 rounded-lg text-center max-w-md mx-auto remodal" data-remodal-id="conditions-<?php echo get_the_ID(); ?>" role="dialog" aria-labelledby="modalTitle" aria-describedby="modalDesc">
                    <h2 id="modalTitle" class="text-xl font-bold mb-4 text-white ">Условия акции</h2>
                    <div class="modal-content">
                    <?php
                        $conditions = get_post_meta(get_the_ID(), 'promo_conditions', true);
                        if (is_array($conditions) && !empty($conditions)) {
                            echo '<ul class="promo-conditions-list flex justify-start flex-col mb-6">';
                            foreach ($conditions as $condition) {
                                echo '<li class="text-lg text-white flex justify-start">' . wpautop(esc_html($condition)) . '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<p>Условия акции отсутствуют.</p>';
                        }
                    ?>
                    </div>
                    <button data-remodal-action="close" class="bg-[#E53F0B] hover:bg-[#F35726] text-white px-6 py-3 rounded-md w-full transition-colors font-bold flex items-center justify-center">
                        Закрыть
                    </button>
                </div>

                <!-- Модальное окно регистрации -->
                <div class="auth-modal bg-[#131313] border-[1px] border-[#fff]/10 p-8 rounded-lg text-center max-w-md mx-auto remodal" data-remodal-id="register-modal-<?php echo get_the_ID(); ?>" role="dialog" aria-labelledby="modalTitle" aria-describedby="modalDesc">
                    <h1 id="modal-title" class="text-2xl font-bold text-white mb-6">Регистрация участия</h1>
                    <div id="error-message" class="text-sm text-white mb-4 hidden"></div>
                    <form id="registration-form-<?php echo get_the_ID(); ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="promoaction" value="<?php echo get_the_ID(); ?>">
                        <div class="mb-4">
                            <input type="tel" name="phone" placeholder="+7 777 777 77 77" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600" required>
                        </div>
                        <div class="mb-4">
                            <input type="text" name="fio" placeholder="Ваше имя" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600" required>
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
                            <input type="number" name="age" placeholder="Ваш возраст" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600" required min="1" max="100">
                        </div>
                        <div class="mb-4">
                            <input type="file" name="receipt_image" id="receipt-image" accept="image/*" class="w-full text-gray-300 bg-[#131313] px-4 py-3 border-[1px] border-[#fff]/10 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600" required>
                        </div>
                        <button type="submit" class="bg-[#E53F0B] hover:bg-[#F35726] text-white px-6 py-3 rounded-md w-full transition-colors font-bold">Зарегистрироваться</button>
                    </form>
                </div>
                <?php
            endwhile;
            wp_reset_postdata();
        endif;
        ?>
    </div>
</section>

</div>



<script>
    jQuery(document).ready(function($) {
        $('[data-remodal-id="modal-register"]').remodal();
    });

    jQuery(document).ready(function($) {
    // Ожидание полной загрузки документа
        $(document).on('click', '[data-remodal-target]', function(event) {
            var modalId = $(this).data('remodal-target');
            var remodalInstance = $('[data-remodal-id="' + modalId + '"]').remodal();
            remodalInstance.open();
        });
    });

    jQuery(document).ready(function($) {
    $('#registration-form').on('submit', function(e) {
        e.preventDefault(); // Останавливаем стандартную отправку формы

        var formData = new FormData(this); // Собираем данные формы, включая файл
        formData.append('action', 'register_user'); // Добавляем action в запрос

        // Отключаем кнопку отправки, скрываем текст и показываем спиннер
        $('#submit-registration .btn-text').prop('disabled', true); // Делаем кнопку неактивной
        $('#submit-registration .btn-text').addClass('hidden'); // Скрываем текст кнопки
        $('#submit-registration .btn-spinner').removeClass('hidden'); // Показываем спиннер

        $.ajax({
            url: ajax_object.ajax_url, // Должно указывать на admin-ajax.php
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            cache: false,
            success: function(response) {
                if (response.success) {
                    // Выводим сообщение об успешной регистрации чека в модальном окне
                    $('#error-message').text('Чек успешно зарегистрирован!').fadeIn().removeClass('text-red-500').addClass('text-green-500');

                    // Очищаем форму
                    $('#registration-form')[0].reset();

                    // Через 3 секунды закрываем модальное окно
                    setTimeout(function() {
                        let remodalInstance = $('[data-remodal-id="modal-register"]').remodal();
                        remodalInstance.close();
                    }, 1500);
                } else {
                    $('#error-message').text('Ошибка регистрации: ' + response.data.message).fadeIn().removeClass('text-green-500').addClass('text-red-500');
                }
            },
            error: function() {
                $('#error-message').text('Ошибка отправки данных на сервер.').fadeIn().removeClass('text-green-500').addClass('text-red-500');
            },
            complete: function() {
                // Восстанавливаем кнопку и убираем спиннер
                $('#submit-registration .btn-text').prop('disabled', false); // Активируем кнопку
                $('#submit-registration .btn-text').removeClass('hidden'); // Показываем текст кнопки
                $('#submit-registration .btn-spinner').addClass('hidden'); // Скрываем спиннер
            }
        });
    });
});
</script>

</main>