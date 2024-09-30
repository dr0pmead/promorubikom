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
                    <button data-remodal-target="modal-auth" id="submit-login" class="bg-[#FF4A1D] hover:bg-[#FF6B3D] text-white font-semibold text-lg px-8 py-2 rounded-lg transition-all duration-300 items-center flex max-w-[225px] justify-center">
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

<script>
    jQuery(document).ready(function($) {
        // При клике на кнопку
        $('#submit-login').on('click', function(e) {
            e.preventDefault();

            <?php if (isset($_COOKIE['user_id'])): ?>
                // Пользователь авторизован, перенаправляем на /dashboard
                window.location.href = '/dashboard';
            <?php else: ?>
                // Пользователь не авторизован, открываем модальное окно
                let remodalInstance = $('[data-remodal-id="modal-auth"]').remodal();
                remodalInstance.open();
            <?php endif; ?>
        });
    });
</script>

</main>