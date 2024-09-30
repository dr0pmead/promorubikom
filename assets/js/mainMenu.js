let menuOpen = false; // Переменная, которая отслеживает состояние меню


document.getElementById('mobile-menu-button').addEventListener('click', function () {
    const mobileMenu = document.getElementById('mobile-menu');
    const body = document.getElementsByTagName('body')[0];

    if (!menuOpen) {
        // Открыть меню
        mobileMenu.classList.remove('opacity-0', 'translate-x-[200px]');
        mobileMenu.classList.toggle('pointer-events-none');
        mobileMenu.classList.add('opacity-1');
        body.classList.add('overflow-hidden');
        menuOpen = true; // Меню открыто
    } else {
        // Закрыть меню
        mobileMenu.classList.remove('opacity-1');
        mobileMenu.classList.toggle('pointer-events-none');
        mobileMenu.classList.add('opacity-0', 'translate-x-[200px]');
        body.classList.remove('overflow-hidden');
        menuOpen = false; // Меню закрыто
    }
});

// Закрытие меню при клике вне его
document.addEventListener('click', function (event) {
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuButton = document.getElementById('mobile-menu-button');

    if (menuOpen && !mobileMenu.contains(event.target) && !mobileMenuButton.contains(event.target)) {
        const body = document.getElementsByTagName('body')[0];
        mobileMenu.classList.remove('opacity-1');
        mobileMenu.classList.add('opacity-0', 'translate-x-[500px]');
        mobileMenu.classList.toggle('pointer-events-none');
        body.classList.remove('overflow-hidden');
        menuOpen = false;
    }
});
