<?php
/*
Template Name: Учетная запись
*/

if (!isset($_COOKIE['user_id'])) {
    wp_redirect(home_url()); // Перенаправление на главную страницу или другую страницу
    exit;
}

?>


<?php
get_header();
?>

<main class="min-h-screen bg-gray-100">
    <section class="relative flex justify-center items-center h-screen bg-[#131313] bg-cover bg-no-repeat bg-bottom"
        style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/main_bg.png');">
        
        <div class="absolute w-full h-full top-[-4rem] left-0 z-10 pointer-events-none">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/circles.svg" alt="circles">
        </div>
        <!-- Полупрозрачный слой поверх фонового изображения -->
        <div class="absolute inset-0 bg-black opacity-50"></div>

        <!-- Контент, который будет поверх изображения -->
        <div class="px-8 md:px-14 lg:px-16 xl:px-8 2xl:px-24 3xl:px-28 pb-24 pt-28 w-full h-full flex flex-col  z-20 gap-6 ">
            <div class="w-full justify-between items-center flex flex-col sm:flex-row gap-4">
                <span class="text-4xl font-bold text-white ">
                    Личный кабинет
                </span>
                <button id="submit-login" class="bg-[#E53F0B] hover:bg-[#F35726] rounded-md flex items-center justify-between px-10 py-2 gap-3 duration-150 text-nowrap" data-remodal-target="modal-check">
                    <span class="text-white font-bold text-sm">Зарегистрировать чек</span>
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/lets-icons_sign-in-squre-duotone.svg" alt="Login Icon" class="w-6">
                </button>
            </div>


            <!-- <div class="w-full h-full bg-[#131313] rounded-lg border-[1px] border-white/10 px-8 py-3 justify-center items-center flex">
                <div id="no-data" class="flex flex-col gap-3 w-full items-center justify-center pointer-events-none">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/no-data.png" alt="" class="w-[25%]">
                </div>
            </div> -->

            <div class="w-full h-full bg-[#131313]/40 backdrop-blur-lg rounded-lg border-[1px] border-white/10 px-3 py-3 flex flex-col overflow-y-auto max-h-[600px] md:h-full min-h-[600px]">

<?php
$tickets = get_user_tickets();

// Проверяем, пуст ли массив или является ли он null
if (empty($tickets) || count($tickets) === 0) {
    // Если чеков нет, выводим сообщение
    ?>
    <div class="nodata text-center text-white font-bold text-xl w-full h-full flex items-center">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/no-data.png" alt="Нет данных" class="w-[25%] mx-auto ">
    </div>
    <?php
} else {
    // Если чеки есть, выводим их циклом
    echo '<div class="tickets-grid grid grid-cols-1 sm:grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">'; // Используем сетку с авто-переносом
    foreach ($tickets as $ticket) {
        // Убедитесь, что данные корректно обрабатываются
        $filename = isset($ticket['file_name']) ? htmlspecialchars($ticket['file_name']) : 'Неизвестно';
        $uploadDate = isset($ticket['upload_date']) && $ticket['upload_date'] instanceof MongoDB\BSON\UTCDateTime 
            ? date('d.m.Y H:i', $ticket['upload_date']->toDateTime()->getTimestamp()) 
            : 'Дата не указана';
        ?>
        <div class="ticket-item bg-[#131313] border-[1px] border-white/10 rounded-lg  text-white flex items-center justify-between px-2 py-2">
            <div class="flex items-center justify-between w-full gap-2">
                <span>
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/lets-icons_ticket-duotone.svg" alt="Тикет" class="mx-auto">
                </span>
                <span class="text-2xl text-white font-bold leading-[5px]">
                    <?php echo htmlspecialchars($filename); ?>
                </span>
                <span class="text-xl text-white font-bold">
                    <?php

                    $status = isset($ticket['status']) ? $ticket['status'] : 'unknown';

                    // Выбор иконки в зависимости от статуса
                    if ($status === 'approved') {
                        echo '<img src="' . get_template_directory_uri() . '/assets/images/approved-icon.svg" alt="Одобрено" class="w-8">';
                    } elseif ($status === 'pending') {
                        echo '<img src="' . get_template_directory_uri() . '/assets/images/spinner.svg" alt="Ожидание" class="w-8 animate-spin fill-white">';
                    } elseif ($status === 'rejected') {
                        echo '<img src="' . get_template_directory_uri() . '/assets/images/rejected-icon.svg" alt="Отказ" class="w-8">';
                    } else {
                        echo '<img src="' . get_template_directory_uri() . '/assets/images/unkown-status.svg" alt="Неизвестный статус" class="w-8">';
                    }
                    ?>
                </span>
            </div>
        </div>
        <?php
    }
    echo '</div>';
}
?>
</div>
<div class="w-full bg-[#131313]/40 backdrop-blur-lg rounded-lg border-[1px] border-white/10 px-3 py-3 grid xl:grid-cols-5 gap-6 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 ">
                    <div class="w-full bg-[#131313] rounded-lg border-[1px] border-white/10 px-3 py-3 font-bold text-xl text-white/50 user-fio md:leading-[15px]  flex items-center">
                        Имя пользователя
                    </div>
                    <div class="w-full bg-[#131313] rounded-lg border-[1px] border-white/10 px-3 py-3 font-bold text-xl text-white/50 user-age md:leading-[15px]  flex items-center">
                        Дата рождения
                    </div>
                    <div class="w-full bg-[#131313] rounded-lg border-[1px] border-white/10 px-3 py-3 font-bold text-xl text-white/50 user-phone md:leading-[15px]   flex items-center">
                        Телефон
                    </div>
                    <div class="w-full bg-[#131313] rounded-lg border-[1px] border-white/10 px-3 py-3 font-bold text-xl text-white/50 user-region md:leading-[15px]  flex items-center">
                        Регион
                    </div>
                    <div class="w-full bg-[#131313] rounded-lg border-[1px] border-white/10 px-3 py-3 font-bold text-xl text-white/50 user-gender md:leading-[15px]  flex items-center">
                        Пол
                    </div>
                </div>
        </div>
        
    </section>

    <!-- Модальное окно для добавления чека -->
    <div class="modal-check bg-[#131313] border-[1px] border-[#fff]/10 p-8 rounded-lg text-center max-w-lg mx-auto remodal" data-remodal-id="modal-check">
        <h1 class="text-2xl font-bold text-white mb-6">Добавить чек</h1>
        
        <!-- Drag and Drop для файла -->
        <div id="drop-area" class="w-full h-64 border-dashed border-2 border-white/10 flex items-center justify-center text-center bg-[#131313] rounded-lg cursor-pointer">
            <div class="pointer-events-none">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/feather_upload-cloud.png" alt="Upload" class="w-16 mx-auto mb-4">
                <p class="text-gray-300">Выберите файл или перетащите его сюда</p>
                <span class="text-gray-500">JPG, XLSX или PDF, размер файла не более 10 МБ</span>
            </div>
            <input type="file" id="file-upload" accept=".jpg,.jpeg,.png,.pdf" class="hidden">
        </div>

        <button type="submit" id="submit-check" class="bg-[#E53F0B] hover:bg-[#F35726] mt-6 text-white px-6 py-3 rounded-md w-full transition-colors font-bold flex items-center justify-center">
            <span class="btn-text">Добавить</span>
            <span class="btn-spinner hidden animate-spin fill-white h-5 w-5">
            <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.501 8C2.501 6.91221 2.82357 5.84884 3.42792 4.94437C4.03227 4.0399 4.89125 3.33495 5.89624 2.91867C6.90123 2.50238 8.0071 2.39347 9.074 2.60568C10.1409 2.8179 11.1209 3.34173 11.8901 4.11092C12.6593 4.8801 13.1831 5.86011 13.3953 6.92701C13.6075 7.9939 13.4986 9.09977 13.0823 10.1048C12.6661 11.1098 11.9611 11.9687 11.0566 12.5731C10.1522 13.1774 9.0888 13.5 8.001 13.5C7.80209 13.4999 7.61127 13.5788 7.47052 13.7193C7.32978 13.8599 7.25063 14.0506 7.2505 14.2495C7.25037 14.4484 7.32926 14.6392 7.46982 14.78C7.61037 14.9207 7.80109 14.9999 8 15C9.38447 15 10.7378 14.5895 11.889 13.8203C13.0401 13.0511 13.9373 11.9579 14.4672 10.6788C14.997 9.3997 15.1356 7.99224 14.8655 6.63437C14.5954 5.2765 13.9287 4.02922 12.9497 3.05026C11.9708 2.07129 10.7235 1.4046 9.36563 1.13451C8.00777 0.86441 6.6003 1.00303 5.32122 1.53285C4.04213 2.06266 2.94888 2.95987 2.17971 4.11101C1.41054 5.26216 1 6.61553 1 8C1 8.19905 1.07907 8.38994 1.21982 8.53069C1.36056 8.67143 1.55145 8.7505 1.7505 8.7505C1.94954 8.7505 2.14044 8.67143 2.28118 8.53069C2.42193 8.38994 2.501 8.19905 2.501 8Z"/>
                </svg>
            </span>
        </button>
    </div>

    <script>
        jQuery(document).ready(function($) {
            // Отправляем запрос на сервер для получения данных пользователя
            $.ajax({
                url: ajax_object.ajax_url, // Используем глобальную переменную WordPress для AJAX URL
                type: 'POST',
                data: {
                    action: 'get_user_data' // Действие для вызова на сервере
                },
                success: function(response) {
                    if (response.success) {
                        // Заполняем данные на странице
                        $('.user-fio').text(response.data.fio);
                        $('.user-age').text(response.data.age);
                        $('.user-phone').text(response.data.phone);
                        $('.user-region').text(response.data.region);
                        $('.user-gender').text(response.data.gender);
                    } else {
                        console.error('Ошибка: ' + response.message);
                    }

                },
                error: function() {
                    console.error('Ошибка отправки запроса на сервер');
                }
            });
        });

        jQuery(document).ready(function($) {
    const dropArea = $('#drop-area');
    const fileInput = $('#file-upload');
    let selectedFile = null;

    // Клик для открытия файлового диалога
    dropArea.on('click', function(event) {
    // Если клик происходит не на дочерних элементах, открываем диалог выбора файла
    if (event.target === dropArea[0]) {
        fileInput.click();
    }
});

    // Изменение файлов через диалоговое окно
    fileInput.on('change', function(event) {
        handleFile(event.target.files[0]);
    });

    // Перетаскивание файла
    dropArea.on('dragover', function(event) {
        event.preventDefault();
        dropArea.addClass('dragging');
    });

    dropArea.on('dragleave', function(event) {
        event.preventDefault();
        dropArea.removeClass('dragging');
    });

    dropArea.on('drop', function(event) {
        event.preventDefault();
        dropArea.removeClass('dragging');
        handleFile(event.originalEvent.dataTransfer.files[0]);
    });

    // Обработка выбранного файла
    function handleFile(file) {
        if (validateFile(file)) {
            selectedFile = file;
            dropArea.find('p').text(`Файл выбран: ${file.name}`);
        } else {
            alert('Неверный формат файла или слишком большой размер.');
        }
    }

    // Валидация файла
    function validateFile(file) {
        const validTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        const maxSize = 10 * 1024 * 1024; // 10 МБ
        return validTypes.includes(file.type) && file.size <= maxSize;
    }

    // Обработка загрузки чека
    $('#submit-check').on('click', function() {
        if (selectedFile) {
            // Показ спиннера и скрытие текста кнопки
            $('#submit-check .btn-text').prop('disabled', true);
            $('#submit-check .btn-text').addClass('hidden');
            $('#submit-check .btn-spinner').removeClass('hidden');

            const formData = new FormData();
            formData.append('file', selectedFile);
            formData.append('action', 'upload_check'); // Действие для WordPress AJAX

            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const parsedResponse = JSON.parse(response);
                        
                        if (parsedResponse.success) {
                            // Закрываем модальное окно после успешной загрузки
                            let remodalInstance = $('[data-remodal-id="modal-check"]').remodal();
                            remodalInstance.close();
                            location.reload();
                            // Сброс данных формы
                            $('#file-upload').val('');  // Сбрасываем выбранный файл
                            selectedFile = null;

                        } else {
                            alert('Ошибка: ' + (parsedResponse.message || 'Неизвестная ошибка'));
                        }
                    } catch (e) {
                        console.error('Ошибка при обработке ответа сервера: ', e);
                    }

                    // Восстановление кнопки после завершения операции
                    $('#submit-check .btn-text').prop('disabled', false);
                    $('#submit-check .btn-text').removeClass('hidden');
                    $('#submit-check .btn-spinner').addClass('hidden');

                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Ошибка AJAX запроса: ', textStatus, errorThrown);

                    // Восстановление кнопки после ошибки
                    $('#submit-check .btn-text').prop('disabled', false);
                    $('#submit-check .btn-text').removeClass('hidden');
                    $('#submit-check .btn-spinner').addClass('hidden');
                }
            });
        } else {
            alert('Выберите файл для загрузки.');
        }
    });


});
    </script>
</main>

