window.addEventListener('load', function() {
    var preloader = document.getElementById('preloader');
    preloader.style.opacity = '0';  // Плавное исчезновение
    setTimeout(function() {
        preloader.style.display = 'none';
    }, 500);  // Убираем прелоадер через 0.5 сек после скрытия
});
