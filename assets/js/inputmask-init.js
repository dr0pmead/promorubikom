jQuery(document).ready(function($) {
    // Маска для номера телефона
    $("input[name='phone']").inputmask({
        mask: "+7 (999) 999-99-99",
        placeholder: "_",
        showMaskOnHover: false,
        showMaskOnFocus: true,
        clearIncomplete: true, // Очищает незаполненные части при отправке
        onincomplete: function() {
            $(this).removeClass("valid").addClass("invalid");
        },
        oncomplete: function() {
            $(this).removeClass("invalid").addClass("valid");
        }
    });

    // Маска для даты рождения
    $("input[name='age']").inputmask("99.99.9999", { placeholder: "дд.мм.гггг" });
});
