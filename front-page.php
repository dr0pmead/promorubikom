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

                <div class="mt-10">
                <button data-remodal-target="modal-register" id="open-modal" class="bg-[#FF4A1D] hover:bg-[#FF6B3D] text-white font-semibold text-lg px-8 py-2 rounded-lg transition-all duration-300 items-center flex max-w-[225px] justify-center">
                    Участвовать <span class="ml-2"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/pepicons-print_arrow-left.svg" alt="circles" class="w-6"></span>
                </button>
                </div>
            </div>
        </section>

        <!-- Блок с условиями участия -->
        <section class=" py-16 px-4 md:px-8 lg:px-16 xl:px-24">
            <div class="w-full max-w-4xl mx-auto bg-[#131313] rounded-lg border-[1px] border-white/10 p-8">
                <h2 class="text-white text-3xl md:text-4xl font-bold text-center mb-8">Как принять участие в акции</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                    <?php
                    $args = array(
                        'post_type'      => 'conditions',
                        'posts_per_page' => -1, // Выводим все записи
                        'orderby'        => 'date',
                        'order'          => 'ASC'
                    );

                    $conditions_query = new WP_Query( $args );

                    if ( $conditions_query->have_posts() ) :
                        while ( $conditions_query->have_posts() ) : $conditions_query->the_post(); 
                            // Получаем миниатюру записи
                            $image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
                            // Получаем описание из мета-поля
                            $description = get_post_meta( get_the_ID(), 'condition_description', true );
                            ?>
                            <!-- Один блок условия -->
                            <div class="condition-item flex flex-col items-center text-center">
                                <div class="w-16 h-16 bg-[#FF4A1D] flex justify-center items-center rounded-full mb-4">
                                    <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title(); ?>" class="w-8 h-8">
                                </div>
                                <h3 class="text-white font-bold text-xl mb-2"><?php the_title(); ?></h3>
                                <p class="text-gray-300 text-sm">
                                    <?php echo esc_html( $description ); ?>
                                </p>
                            </div>
                        <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>
        </div>
    </section>
</div>

        <!-- Окно регистрации -->
        <div class="auth-modal bg-[#131313] border-[1px] border-[#fff]/10 p-8 rounded-lg text-center max-w-md mx-auto remodal" data-remodal-id="modal-register" role="dialog" aria-labelledby="modalTitle" aria-describedby="modalDesc">
            <h1 id="modal-title" class="text-2xl font-bold text-white mb-6">Регистрация участия</h1>

            <div id="error-message" class="text-sm text-white mb-4 hidden"></div>

            <form id="registration-form" method="POST" enctype="multipart/form-data">
                <div class="hidden-honeypot" style="display:none;">
                    <input type="text" name="bot_field" value="">
                </div>

                <input type="hidden" name="form_time" id="form_time" value="<?php echo time(); ?>">

                <!-- Поле для ввода телефона -->
                <div class="mb-4">
                    <input type="tel" name="phone" placeholder="+7 777 777 77 77" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600" required>
                </div>

                <!-- Поле для ввода имени -->
                <div class="mb-4">
                    <input type="text" name="fio" placeholder="Ваше имя" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600" required>
                </div>

                <!-- Поле для выбора региона -->
                <div class="mb-4">
                    <select name="region" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600" required>
                        <option value="" disabled selected>Ваш регион</option>
                        <?php
                        // Получаем все регионы из кастомного пост-типа
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

                <!-- Поле для возраста -->
                <div class="mb-4">
                    <input type="number" name="age" placeholder="Ваш возраст" class="w-full px-4 py-3 font-bold border-[1px] border-[#fff]/10 bg-[#131313] text-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600" required min="1" max="100">
                </div>

                <!-- Поле для выбора пола -->
                <div class="flex items-center mb-6 justify-start space-x-4">
                    <label class="text-white flex items-center gap-3">
                        <input type="radio" name="gender" value="male" class="mr-2 bg-[#E53F0B] focus:ring-[#E53F0B]" required> Мужской
                    </label>
                    <label class="text-white flex items-center gap-3">
                        <input type="radio" name="gender" value="female" class="mr-2 bg-[#E53F0B] focus:ring-[#E53F0B]" required> Женский
                    </label>
                </div>

                <!-- Поле для загрузки изображения -->
                <div class="mb-4">
                    <input type="file" name="receipt_image" id="receipt-image" accept="image/*" class="w-full text-gray-300 bg-[#131313] px-4 py-3 border-[1px] border-[#fff]/10 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600       file:mr-4 file:py-2 file:px-4
      file:rounded-md file:border-0
      file:text-sm file:font-semibold
      file:bg-[#E53F0B] file:text-white file:duration-150 file:cursor-pointer
      hover:file:bg-[#F35726]" required>
                </div>

                <!-- Кнопка регистрации -->
                <button type="submit" id="submit-registration" class="disabled:bg-[#E53F0B]/50 bg-[#E53F0B] hover:bg-[#F35726] text-white px-6 py-3 rounded-md w-full transition-colors font-bold flex items-center justify-center">
                    <span class="btn-text">Зарегистрироваться</span>
                    <span class="btn-spinner hidden animate-spin fill-white h-5 w-5">
                        <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.501 8C2.501 6.91221 2.82357 5.84884 3.42792 4.94437C4.03227 4.0399 4.89125 3.33495 5.89624 2.91867C6.90123 2.50238 8.0071 2.39347 9.074 2.60568C10.1409 2.8179 11.1209 3.34173 11.8901 4.11092C12.6593 4.8801 13.1831 5.86011 13.3953 6.92701C13.6075 7.9939 13.4986 9.09977 13.0823 10.1048C12.6661 11.1098 11.9611 11.9687 11.0566 12.5731C10.1522 13.1774 9.0888 13.5 8.001 13.5C7.80209 13.4999 7.61127 13.5788 7.47052 13.7193C7.32978 13.8599 7.25063 14.0506 7.2505 14.2495C7.25037 14.4484 7.32926 14.6392 7.46982 14.78C7.61037 14.9207 7.80109 14.9999 8 15C9.38447 15 10.7378 14.5895 11.889 13.8203C13.0401 13.0511 13.9373 11.9579 14.4672 10.6788C14.997 9.3997 15.1356 7.99224 14.8655 6.63437C14.5954 5.2765 13.9287 4.02922 12.9497 3.05026C11.9708 2.07129 10.7235 1.4046 9.36563 1.13451C8.00777 0.86441 6.6003 1.00303 5.32122 1.53285C4.04213 2.06266 2.94888 2.95987 2.17971 4.11101C1.41054 5.26216 1 6.61553 1 8C1 8.19905 1.07907 8.38994 1.21982 8.53069C1.36056 8.67143 1.55145 8.7505 1.7505 8.7505C1.94954 8.7505 2.14044 8.67143 2.28118 8.53069C2.42193 8.38994 2.501 8.19905 2.501 8Z"/>
                        </svg>
                    </span>
                </button>
            </form>
        </div>


<script>
    jQuery(document).ready(function($) {
        $('[data-remodal-id="modal-register"]').remodal();
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