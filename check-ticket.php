<?php
/*
Template Name: Панель управления
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
        style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/main_bg.webp'); background-size: cover;">
        
        <div class="absolute w-full h-full top-[-4rem] left-0 z-10 pointer-events-none">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/circles.svg" alt="circles">
        </div>
        <!-- Полупрозрачный слой поверх фонового изображения -->
        <div class="absolute inset-0 bg-black opacity-50"></div>

        <!-- Контент, который будет поверх изображения -->
        <div class="relative z-20 px-4 md:px-8 lg:px-16 xl:px-8 2xl:px-24 3xl:px-28 pb-24 pt-28 w-full h-full overflow-y-auto gap-6 grid grid-cols-1 xl:grid-cols-2">
   
   <!-- Блок для проверки тикетов -->
   <div class="w-full bg-[#131313]/50 backdrop-blur-md border-[1px] border-[#fff]/10 rounded-md p-4 md:p-6 flex items-center flex-col overflow-y-auto  min-h-[600px] md:h-full">
       <div class="w-full flex justify-between items-center flex-col sm:flex-row gap-4">
           <span class="text-xl md:text-2xl font-bold text-white w-full sm:w-auto text-center md:text-left"> Проверка тикетов </span>
           <button id="delete-all-tickets" class="justify-between py-2 px-6 flex items-center text-white font-bold rounded-md duration-150 bg-[#E50B0B] hover:bg-[#ff4343] gap-2 w-full sm:w-[35%] lg:w-[30%] md:w-[35%]" onclick="event.stopPropagation(); deleteAllTickets(this)"> 
                <div class="btn-text flex justify-between items-center w-full"><span class="text-sm btn-text">Удалить все тикеты</span>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/lets-icons_trash-duotone.svg" alt="trash" class="w-6"></div>
                <span class="btn-spinner hidden animate-spin  h-5 w-5 mx-auto">
                <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.501 8C2.501 6.91221 2.82357 5.84884 3.42792 4.94437C4.03227 4.0399 4.89125 3.33495 5.89624 2.91867C6.90123 2.50238 8.0071 2.39347 9.074 2.60568C10.1409 2.8179 11.1209 3.34173 11.8901 4.11092C12.6593 4.8801 13.1831 5.86011 13.3953 6.92701C13.6075 7.9939 13.4986 9.09977 13.0823 10.1048C12.6661 11.1098 11.9611 11.9687 11.0566 12.5731C10.1522 13.1774 9.0888 13.5 8.001 13.5C7.80209 13.4999 7.61127 13.5788 7.47052 13.7193C7.32978 13.8599 7.25063 14.0506 7.2505 14.2495C7.25037 14.4484 7.32926 14.6392 7.46982 14.78C7.61037 14.9207 7.80109 14.9999 8 15C9.38447 15 10.7378 14.5895 11.889 13.8203C13.0401 13.0511 13.9373 11.9579 14.4672 10.6788C14.997 9.3997 15.1356 7.99224 14.8655 6.63437C14.5954 5.2765 13.9287 4.02922 12.9497 3.05026C11.9708 2.07129 10.7235 1.4046 9.36563 1.13451C8.00777 0.86441 6.6003 1.00303 5.32122 1.53285C4.04213 2.06266 2.94888 2.95987 2.17971 4.11101C1.41054 5.26216 1 6.61553 1 8C1 8.19905 1.07907 8.38994 1.21982 8.53069C1.36056 8.67143 1.55145 8.7505 1.7505 8.7505C1.94954 8.7505 2.14044 8.67143 2.28118 8.53069C2.42193 8.38994 2.501 8.19905 2.501 8Z" fill="#fff"/>
                </svg>
            </span>
            </button>
       </div>

       <?php
       $pendingTickets = get_pending_tickets();
       if (empty($pendingTickets)) {
       ?>
       <div class="nodata flex flex-col justify-center items-center text-center text-white font-bold text-xl w-full h-[400px] lg:h-[700px]">
           <img src="<?php echo get_template_directory_uri(); ?>/assets/images/no-data.webp" alt="Нет данных" class="w-[45%] sm:w-[35%] md:[w-30%] lg:w-[25%] mx-auto">
       </div>
       <?php
       } else {
           echo '<div class="tickets-grid mt-4 flex flex-col gap-4 w-full">';
           foreach ($pendingTickets as $ticket) {
               $filename = htmlspecialchars($ticket['ticket_number']);
               $uploadDate = isset($ticket['upload_date']) && $ticket['upload_date'] instanceof MongoDB\BSON\UTCDateTime 
                   ? date('d.m.Y H:i', $ticket['upload_date']->toDateTime()->getTimestamp()) 
                   : 'Дата не указана';
               $path = htmlspecialchars($ticket['path_to']); 
               ?>
               <div class="ticket-item bg-[#131313] border-[1px] border-white/10 rounded-lg p-4 text-white w-full cursor-pointer"
                   data-image-path="<?php echo $path; ?>" onclick="openImageModal('<?php echo $path; ?>')">
                   <div class="flex items-center justify-between gap-4">
                       <div class="flex gap-3">
                           <span>
                               <img src="<?php echo get_template_directory_uri(); ?>/assets/images/lets-icons_ticket-duotone.svg" alt="Тикет" class="w-8">
                           </span>
                           <div class="flex flex-col gap-2 justify-start">
                               <span class="text-lg md:text-xl font-bold leading-3"><?php echo $filename; ?></span>
                               <span class="text-sm text-gray-400 leading-3"><?php echo $uploadDate; ?></span>
                           </div>
                       </div>
                       <div class="flex gap-3 items-center">
                           <button id="submit-delete" class="delete-ticket-btn flex items-center justify-center px-2 sm:px-6 md:px-8 py-2 duration-150 bg-[#131313] hover:bg-[#222222] border-[1px] border-[#fff]/10 rounded-md"
                                   data-ticket-id="<?php echo $ticket['_id']; ?>" onclick="event.stopPropagation(); deleteTicket(this)">
                                   <span class="w-5 sm:w-[80px] text-center">
                               <div class="btn-text">
                                    <span class="hidden sm:block">Удалить</span>
                                    <span class="block sm:hidden"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/lets-icons_trash-duotone.svg" alt="trash" class="w-6"></span>
                                    </div>
                                   <span class="btn-spinner hidden animate-spinh-5 w-5 mx-auto">
                                       <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                       <path d="M2.501 8C2.501 6.91221 2.82357 5.84884 3.42792 4.94437C4.03227 4.0399 4.89125 3.33495 5.89624 2.91867C6.90123 2.50238 8.0071 2.39347 9.074 2.60568C10.1409 2.8179 11.1209 3.34173 11.8901 4.11092C12.6593 4.8801 13.1831 5.86011 13.3953 6.92701C13.6075 7.9939 13.4986 9.09977 13.0823 10.1048C12.6661 11.1098 11.9611 11.9687 11.0566 12.5731C10.1522 13.1774 9.0888 13.5 8.001 13.5C7.80209 13.4999 7.61127 13.5788 7.47052 13.7193C7.32978 13.8599 7.25063 14.0506 7.2505 14.2495C7.25037 14.4484 7.32926 14.6392 7.46982 14.78C7.61037 14.9207 7.80109 14.9999 8 15C9.38447 15 10.7378 14.5895 11.889 13.8203C13.0401 13.0511 13.9373 11.9579 14.4672 10.6788C14.997 9.3997 15.1356 7.99224 14.8655 6.63437C14.5954 5.2765 13.9287 4.02922 12.9497 3.05026C11.9708 2.07129 10.7235 1.4046 9.36563 1.13451C8.00777 0.86441 6.6003 1.00303 5.32122 1.53285C4.04213 2.06266 2.94888 2.95987 2.17971 4.11101C1.41054 5.26216 1 6.61553 1 8C1 8.19905 1.07907 8.38994 1.21982 8.53069C1.36056 8.67143 1.55145 8.7505 1.7505 8.7505C1.94954 8.7505 2.14044 8.67143 2.28118 8.53069C2.42193 8.38994 2.501 8.19905 2.501 8Z" fill="#fff"/>
                                       </svg>
                                   </span>
                               </span>
                           </button>

                           <button id="submit-approve" class="approve-ticket-btn flex items-center justify-center px-2 sm:px-6 md:px-8 py-2 duration-150 bg-[#131313] hover:bg-[#222222] border-[1px] border-[#fff]/10 rounded-md"
                                   data-ticket-id="<?php echo $ticket['_id']; ?>" onclick="event.stopPropagation(); approveTicket(this)">
                               <span class="w-5 h-5 sm:w-[80px] text-center flex items-center justify-center sm:h-auto">
                                   <div class="btn-text">
                                    <span class="hidden sm:block">Принять</span>
                                    <span class="block sm:hidden"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/approved-icon.svg" alt="trash" class="w-6"></span>
                                    </div>
                                   <span class="btn-spinner hidden animate-spin  h-5 w-5 mx-auto">
                                       <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                       <path d="M2.501 8C2.501 6.91221 2.82357 5.84884 3.42792 4.94437C4.03227 4.0399 4.89125 3.33495 5.89624 2.91867C6.90123 2.50238 8.0071 2.39347 9.074 2.60568C10.1409 2.8179 11.1209 3.34173 11.8901 4.11092C12.6593 4.8801 13.1831 5.86011 13.3953 6.92701C13.6075 7.9939 13.4986 9.09977 13.0823 10.1048C12.6661 11.1098 11.9611 11.9687 11.0566 12.5731C10.1522 13.1774 9.0888 13.5 8.001 13.5C7.80209 13.4999 7.61127 13.5788 7.47052 13.7193C7.32978 13.8599 7.25063 14.0506 7.2505 14.2495C7.25037 14.4484 7.32926 14.6392 7.46982 14.78C7.61037 14.9207 7.80109 14.9999 8 15C9.38447 15 10.7378 14.5895 11.889 13.8203C13.0401 13.0511 13.9373 11.9579 14.4672 10.6788C14.997 9.3997 15.1356 7.99224 14.8655 6.63437C14.5954 5.2765 13.9287 4.02922 12.9497 3.05026C11.9708 2.07129 10.7235 1.4046 9.36563 1.13451C8.00777 0.86441 6.6003 1.00303 5.32122 1.53285C4.04213 2.06266 2.94888 2.95987 2.17971 4.11101C1.41054 5.26216 1 6.61553 1 8C1 8.19905 1.07907 8.38994 1.21982 8.53069C1.36056 8.67143 1.55145 8.7505 1.7505 8.7505C1.94954 8.7505 2.14044 8.67143 2.28118 8.53069C2.42193 8.38994 2.501 8.19905 2.501 8Z" fill="#fff"/>
                                       </svg>
                                   </span>
                               </span>
                           </button>
                       </div>
                   </div>
               </div>
               <?php
           }
           echo '</div>';
       }
       ?>
   </div>

   <!-- Блок для розыгрыша по тикетам -->
   <div class="w-full bg-[#131313]/50 backdrop-blur-md border-[1px] border-[#fff]/10 rounded-md p-4 md:p-6 flex items-center flex-col  min-h-[600px] md:h-full h-full overflow-hidden ">
       <div class="w-full flex justify-between items-center flex-col sm:flex-row gap-4">
           <span class="text-xl md:text-2xl font-bold text-white w-full sm:w-auto text-center md:text-left"> Розыгрыш по тикетам </span>
           <button id="open-lottery-modal" class="py-2 px-6 flex items-center text-white font-bold rounded-md duration-150 bg-[#131313] border-[1px] border-[#fff]/10 hover:bg-[#222222] gap-2 w-full justify-between sm:w-[35%] lg:w-[30%] md:w-[35%]">
                <span class="text-sm">Разыграть</span>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/bxs_party.svg" alt="party" class="w-6">
            </button>
       </div>
       <?php
$lotteryRecords = get_lottery_records();
?>

<div x-data="{ openDetails: false }" class="w-full mt-4">
    <?php if (empty($lotteryRecords)) { ?>
        <div class="nodata flex flex-col justify-center items-center text-center text-white font-bold text-xl w-full h-[400px] lg:h-[650px]">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/no-data.webp" alt="Нет данных" class="w-[45%] sm:w-[35%] md:[w-30%] lg:w-[25%] mx-auto">
        </div>
    <?php } else { ?>
        <div class="relative w-full h-full">
            <!-- Список розыгрышей -->
            <div class="lottery-records-grid mb-4 flex flex-col gap-4 w-full  h-[400px] sm:h-[450px] md:h-[500px] lg:h-[550px] overflow-y-auto"
                x-show="!openDetails" 
                x-transition:enter="transition transform ease-in-out duration-300"
                x-transition:enter-start="translate-x-[-100%] opacity-0" x-transition:enter-end="translate-x-0 opacity-1"
                x-transition:leave="transition transform ease-in-out duration-300"
                x-transition:leave-start="translate-x-0 opacity-1" x-transition:leave-end="translate-x-[-100%] opacity-0">

                <?php foreach ($lotteryRecords as $lottery) { ?>
                    <div class="lottery-item bg-[#131313] border-[1px] border-white/10 rounded-lg p-4 text-white w-full cursor-pointer duration-150"
                         data-id="<?php echo $lottery['_id']; ?>">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col gap-3">
                                <span class="text-xl font-bold leading-3"><?php echo htmlspecialchars($lottery['numberLottery'] ?? 'Неизвестный номер'); ?></span>
                                <div class="text-sm text-gray-400">Количество участников: <?php echo intval($lottery['participant_count'] ?? 0); ?></div>
                            </div>
                            <!-- Передача ID через onclick -->
                            <button class="p-2 md:py-2 md:px-6  text-white font-bold rounded-md bg-[#131313] border-[1px] border-white/10 hover:bg-[#222222]"
                                    @click="openDetails = true" onclick="fetchLotteryDetails('<?php echo $lottery['_id']; ?>')">
                                <span class="md:flex hidden">Посмотреть детали</span>
                                <span class="md:hidden flex items-center"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/tabler_list-details.svg" alt="party" class="w-6"></span>
                            </button>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <!-- Детали розыгрыша -->
            <div class="lottery-details bg-[#131313] border-[1px] border-white/10 rounded-lg p-4 text-white w-full h-[450px] md:h-[500px] lg:h-[550px] absolute top-0 left-0"
                x-show="openDetails" 
                x-transition:enter="transition transform ease-in-out duration-300"
                x-transition:enter-start="translate-x-[100%] opacity-0" x-transition:enter-end="translate-x-0 opacity-1"
                x-transition:leave="transition transform ease-in-out duration-300"
                x-transition:leave-start="translate-x-0 opacity-1" x-transition:leave-end="translate-x-[100%] opacity-0">

                <button class="text-white mb-4 flex items-center justify-center gap-2 font-bold" @click="openDetails = false">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/maki_arrow.svg" alt="party" class="w-6 rotate-180"> <span class="font-bold"> Назад </span>
                </button>

                <!-- Контейнер для отображения данных -->
                <div id="lottery-details-content">
                    <div class="w-full flex items-center justify-center h-full">
                        <span class="btn-spinner hidden animate-spin  h-5 w-5 mx-auto">
                            <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.501 8C2.501 6.91221 2.82357 5.84884 3.42792 4.94437C4.03227 4.0399 4.89125 3.33495 5.89624 2.91867C6.90123 2.50238 8.0071 2.39347 9.074 2.60568C10.1409 2.8179 11.1209 3.34173 11.8901 4.11092C12.6593 4.8801 13.1831 5.86011 13.3953 6.92701C13.6075 7.9939 13.4986 9.09977 13.0823 10.1048C12.6661 11.1098 11.9611 11.9687 11.0566 12.5731C10.1522 13.1774 9.0888 13.5 8.001 13.5C7.80209 13.4999 7.61127 13.5788 7.47052 13.7193C7.32978 13.8599 7.25063 14.0506 7.2505 14.2495C7.25037 14.4484 7.32926 14.6392 7.46982 14.78C7.61037 14.9207 7.80109 14.9999 8 15C9.38447 15 10.7378 14.5895 11.889 13.8203C13.0401 13.0511 13.9373 11.9579 14.4672 10.6788C14.997 9.3997 15.1356 7.99224 14.8655 6.63437C14.5954 5.2765 13.9287 4.02922 12.9497 3.05026C11.9708 2.07129 10.7235 1.4046 9.36563 1.13451C8.00777 0.86441 6.6003 1.00303 5.32122 1.53285C4.04213 2.06266 2.94888 2.95987 2.17971 4.11101C1.41054 5.26216 1 6.61553 1 8C1 8.19905 1.07907 8.38994 1.21982 8.53069C1.36056 8.67143 1.55145 8.7505 1.7505 8.7505C1.94954 8.7505 2.14044 8.67143 2.28118 8.53069C2.42193 8.38994 2.501 8.19905 2.501 8Z" fill="#fff"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

   </div>
</div>
    </section>

    <!-- Модальное окно для показа изображения -->
    <div class="modal hidden fixed inset-0 bg-black bg-opacity-75 z-50 items-center justify-center" id="imageModal">
        <div class="relative">
            <img src="" id="modalImage" alt="Ticket Image" class="rounded-lg">
            <button class="absolute top-2 right-2 text-white text-xl" onclick="closeImageModal()">X</button>
        </div>
    </div>

    <div class="lottery-modal bg-[#131313] border-[1px] border-[#fff]/10 p-8 rounded-lg text-center max-w-md mx-auto remodal" data-remodal-id="modal-lottery" role="dialog" aria-labelledby="modalTitle" aria-describedby="modalDesc">
        <h1 id="modal-title" class="text-2xl font-bold text-white mb-6">Введите количество участников</h1>
        <div id="error-lottery" class="text-red-500 text-sm mb-4 hidden"></div>
        <!-- Поле ввода количества участников -->
        <input type="number" id="participant-count" class="w-full px-4 py-2 text-gray-300 bg-[#222222] border-[1px] border-[#fff]/10 rounded-md text-center" placeholder="Количество участников (0-1000)" min="0" max="1000">

        <!-- Кнопка "Разыграть" -->
        <button id="start-lottery" class="bg-[#E53F0B] hover:bg-[#F35726] mt-6 text-white px-6 py-3 rounded-md w-full transition-colors font-bold flex items-center justify-center">
            <span class="btn-text">Разыграть</span>
            <span class="btn-spinner hidden animate-spin fill-white h-5 w-5">
            <svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.501 8C2.501 6.91221 2.82357 5.84884 3.42792 4.94437C4.03227 4.0399 4.89125 3.33495 5.89624 2.91867C6.90123 2.50238 8.0071 2.39347 9.074 2.60568C10.1409 2.8179 11.1209 3.34173 11.8901 4.11092C12.6593 4.8801 13.1831 5.86011 13.3953 6.92701C13.6075 7.9939 13.4986 9.09977 13.0823 10.1048C12.6661 11.1098 11.9611 11.9687 11.0566 12.5731C10.1522 13.1774 9.0888 13.5 8.001 13.5C7.80209 13.4999 7.61127 13.5788 7.47052 13.7193C7.32978 13.8599 7.25063 14.0506 7.2505 14.2495C7.25037 14.4484 7.32926 14.6392 7.46982 14.78C7.61037 14.9207 7.80109 14.9999 8 15C9.38447 15 10.7378 14.5895 11.889 13.8203C13.0401 13.0511 13.9373 11.9579 14.4672 10.6788C14.997 9.3997 15.1356 7.99224 14.8655 6.63437C14.5954 5.2765 13.9287 4.02922 12.9497 3.05026C11.9708 2.07129 10.7235 1.4046 9.36563 1.13451C8.00777 0.86441 6.6003 1.00303 5.32122 1.53285C4.04213 2.06266 2.94888 2.95987 2.17971 4.11101C1.41054 5.26216 1 6.61553 1 8C1 8.19905 1.07907 8.38994 1.21982 8.53069C1.36056 8.67143 1.55145 8.7505 1.7505 8.7505C1.94954 8.7505 2.14044 8.67143 2.28118 8.53069C2.42193 8.38994 2.501 8.19905 2.501 8Z" fill="#fff"/>
                </svg>
            </span>
        </button>
    </div>
</div>

    <script>
jQuery(document).ready(function($) {

        // Функция для открытия модального окна с изображением
        function openImageModal(imagePath) {
            // Создаем HTML для модального окна
            const modalHtml = `
                <div class="image-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; z-index: 1000;">
                    <div class="image-modal-content" style="position: relative; background: white; border-radius: 8px;">
                        <img src="${imagePath}" alt="Тикет" style="max-width: 100%; max-height: 80vh;" />
                        <button class="close-modal p-2 px-2 text-white bg-[#131313] rounded-md border-[1px] border-[#fff]/10 leading-[10px]" style="position: absolute; top: 10px; right: 10px; font-size: 20px; cursor: pointer;">&times;</button>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);

            // Добавляем обработчик для закрытия модального окна
            $('.close-modal').on('click', function() {
                closeImageModal();
            });
        }

        // Функция для закрытия модального окна с изображением
        function closeImageModal() {
            $('.image-modal-overlay').remove();
        }

        // Открытие модального окна для лотереи
        $('#open-lottery-modal').on('click', function() {
            var modalInstance = $('[data-remodal-id="modal-lottery"]').remodal();
            modalInstance.open();
        });


        // Логика розыгрыша при нажатии на кнопку "Разыграть"
        $('#start-lottery').on('click', function() {
            var participantCount = $('#participant-count').val();

            $('#start-lottery .btn-text').prop('disabled', true);
            $('#start-lottery .btn-text').addClass('hidden');
            $('#start-lottery .btn-spinner').removeClass('hidden');

            // Валидация количества участников
            if (participantCount >= 1 && participantCount <= 1000) {
                // AJAX-запрос на сервер для выполнения логики розыгрыша
                $.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    type: 'POST',
                    data: {
                        action: 'start_lottery',
                        participant_count: participantCount // передаем количество участников
                    },
                    success: function(response) {
                        if (response.success) {
                            // Успешное завершение розыгрыша
                            alert('Розыгрыш завершен! Победители выбраны.');
                            let remodalInstance = $('[data-remodal-id="modal-lottery"]').remodal();
                            remodalInstance.close();
                        } else {
                            // Если ошибка — выводим сообщение в модальном окне
                            $('#error-lottery').text(response.data.message).fadeIn();
                        }
                    },
                    error: function() {
                        // Ошибка соединения или сервера
                        $('#error-lottery').text('Произошла ошибка при попытке выполнения розыгрыша.').fadeIn();
                    },
                    complete: function() {
                        // Восстановление кнопки после завершения операции
                        $('#start-lottery .btn-text').prop('disabled', false);
                        $('#start-lottery .btn-text').removeClass('hidden');
                        $('#start-lottery .btn-spinner').addClass('hidden');
                    }
                });
            } else {
                // Некорректное количество участников
                $('#error-lottery').text('Введите корректное количество участников (от 1 до 1000).').fadeIn();
                $('#start-lottery .btn-text').prop('disabled', false);
                $('#start-lottery .btn-text').removeClass('hidden');
                $('#start-lottery .btn-spinner').addClass('hidden');
            }
        });

        // Функция для удаления тикета
        function deleteTicket(button) {
            const ticketId = $(button).data('ticket-id');

            // Добавляем классы для отображения спиннера
            $(button).find('.btn-text').addClass('hidden');
            $(button).find('.btn-spinner').removeClass('hidden');
            $(button).find('.btn-spinner').addClass('block');

            if (confirm('Вы уверены, что хотите удалить этот тикет?')) {
                $.ajax({
                    url: ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'delete_ticket',
                        ticket_id: ticketId // Передаем ID тикета
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload(); // Обновляем страницу
                        } else {
                            alert('Ошибка: ' + response.data.message);
                        }
                    },
                    complete: function() {
                        // Восстановление текста кнопки после завершения операции
                        $(button).find('.btn-text').removeClass('hidden');
                        $(button).find('.btn-spinner').addClass('hidden');
                        $(button).find('.btn-spinner').removeClass('block');
                    }
                });
            } else {
                // Восстанавливаем текст кнопки, если отменили операцию
                $(button).find('.btn-text').removeClass('hidden');
                $(button).find('.btn-spinner').addClass('hidden');
                $(button).find('.btn-spinner').removeClass('block');
            }
        }

        // Принятие тикета
        function approveTicket(button) {
            const ticketId = $(button).data('ticket-id');

            $(button).find('.btn-text').addClass('hidden');
            $(button).find('.btn-spinner').removeClass('hidden');
            $(button).find('.btn-spinner').addClass('block');

            if (confirm('Вы уверены, что хотите одобрить этот тикет?')) {
                $.ajax({
                    url: ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'approve_ticket',
                        ticket_id: ticketId // Передаем ID тикета
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload(); // Обновляем страницу после успешного одобрения
                        } else {
                            alert('Ошибка: ' + response.data.message);
                        }
                    },
                    complete: function() {
                        // Возвращаем кнопку в исходное состояние после завершения операции
                        $(button).find('.btn-text').removeClass('hidden');
                        $(button).find('.btn-spinner').addClass('hidden');
                        $(button).find('.btn-spinner').removeClass('block');
                    }
                });
            }
        }

        // Удаление всех тикетов
        function deleteAllTickets(button) {
            $(button).find('.btn-text').addClass('hidden');
            $(button).find('.btn-spinner').removeClass('hidden');
            $(button).find('.btn-spinner').addClass('block');

            if (confirm('Вы уверены, что хотите удалить все тикеты?')) {
                $.ajax({
                    url: ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'delete_all_tickets', // Указываем действие для удаления всех тикетов
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload(); // Обновляем страницу после успешного удаления всех тикетов
                        } else {
                            alert('Ошибка: ' + response.data.message);
                        }
                    },
                    error: function() {
                        alert('Произошла ошибка при попытке удаления всех тикетов.');
                    },
                    complete: function() {
                        $(button).find('.btn-text').removeClass('hidden');
                        $(button).find('.btn-spinner').addClass('hidden');
                        $(button).find('.btn-spinner').removeClass('block');
                    }
                });
            } else {
                $(button).find('.btn-text').removeClass('hidden');
                $(button).find('.btn-spinner').addClass('hidden');
                $(button).find('.btn-spinner').removeClass('block');
            }
        }

        function fetchLotteryDetails(lotteryId) {
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'get_lottery_details',
                    lottery_id: lotteryId
                },
                success: function(response) {
                    if (response.success) {
                        const lotteryDetails = response.data.data;

                        // Логируем для проверки
                        console.log("Данные лотереи:", lotteryDetails);

                        // HTML для информации о лотерее
                        let detailsHtml = `
                            <div id="lottery-info">
                                <p><strong>Номер розыгрыша:</strong> ${lotteryDetails.numberLottery || 'Неизвестный номер'}</p>
                                <p><strong>Количество участников:</strong> ${lotteryDetails.participant_count || 'Неизвестно'}</p>
                            </div>`;

                        // HTML для победных тикетов
                        detailsHtml += `<div id="lottery-winner-tickets" class="overflow-y-auto h-[525px] overflow-hidden mt-4">`;

                        if (lotteryDetails.winning_tickets && lotteryDetails.winning_tickets.length > 0) {
                            lotteryDetails.winning_tickets.forEach(function(ticket) {
                                detailsHtml += `
                                    <div class="ticket-item bg-[#222222] border-[1px] border-white/10 rounded-lg p-4 mb-4 flex justify-between items-center">
                                        <div class="flex flex-col gap-2 justify-start">
                                            <div class="flex gap-2 items-center sm:flex-row flex flex-col sm:gap-2 gap-1">
                                                <span class="font-bold text-lg text-white"> ${ticket.owner.fio} </span>
                                                <span class="text-sm text-white font-regular w-full justify-start flex items-center sm:w-[35%] text-nowrap">${ticket.owner.region} </span>
                                            </div>
                                            <span class="text-md text-white"> ${ticket.owner.phone} </span>
                                        </div>
                                        <div>
                                            <button onclick="openImageModal('${ticket.path}')" class="show-receipt-btn p-2 sm:py-2 sm:px-6 text-white font-bold rounded-md bg-[#131313] border-[1px] border-white/10 hover:bg-[#222222] flex items-center justify-center">
                                                <span class="hidden sm:block">Показать чек</span>
                                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/ion_open.svg" alt="icon" class="w-6 sm:hidden">
                                            </button>
                                        </div>
                                    </div>`;
                            });
                        } else {
                            detailsHtml += `<p>Победители не найдены.</p>`;
                        }

                        detailsHtml += `</div>`;

                        // Вставляем сформированный HTML в контейнер
                        $('#lottery-details-content').html(detailsHtml);
                    } else {
                        alert('Ошибка: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Произошла ошибка при запросе данных.');
                }
            });
        }




        // Привязка функций к окнам и кнопкам
        window.deleteTicket = deleteTicket;
        window.approveTicket = approveTicket;
        window.openImageModal = openImageModal;
        window.deleteAllTickets = deleteAllTickets;
        window.fetchLotteryDetails = fetchLotteryDetails;

        });

    </script>

</main>

<?php
get_footer();
?>
