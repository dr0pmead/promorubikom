// modal.js - кастомный скрипт
document.addEventListener('DOMContentLoaded', function () {
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('modal-open'); // Добавляем класс для отображения модального окна
            setTimeout(() => {
                modal.classList.add('modal-zoom'); // Анимация зума
            }, 30); // Небольшая задержка для плавного зума
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('modal-zoom'); // Убираем анимацию зума
            setTimeout(() => {
                modal.classList.remove('modal-open'); // Закрываем модальное окно
            }, 30); // Ждем завершения анимации зума перед закрытием
        }
    }

    // Назначаем обработчики для открытия модальных окон
    document.querySelectorAll('[data-modal-trigger]').forEach(trigger => {
        trigger.addEventListener('click', function () {
            const modalId = this.getAttribute('data-modal-trigger');
            openModal(modalId);
        });
    });

    // Назначаем обработчики для закрытия модальных окон
    document.querySelectorAll('[data-modal-close]').forEach(closeBtn => {
        closeBtn.addEventListener('click', function () {
            const modalId = this.getAttribute('data-modal-close');
            closeModal(modalId);
        });
    });

    // Закрытие модального окна по клику на оверлей
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function () {
            const modalId = this.closest('.modal').id;
            closeModal(modalId);
        });
    });
});
