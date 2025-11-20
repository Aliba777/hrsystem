// Анимации и интерактивность
document.addEventListener('DOMContentLoaded', function() {
    // Плавные переходы
    const links = document.querySelectorAll('a');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href').startsWith('#')) return;
            e.preventDefault();
            setTimeout(() => {
                window.location.href = this.href;
            }, 300);
        });
    });

    // Динамическое обновление счетчиков
    function updateCounters() {
        // Можно добавить live-обновление счетчиков заявок
    }

    // Валидация форм
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('input[required], textarea[required]');
            let valid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    valid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Пожалуйста, заполните все обязательные поля');
            }
        });
    });
});