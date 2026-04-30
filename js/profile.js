// Профиль соискателя - JavaScript

// Редактирование личной информации
function editPersonalInfo() {
    const modal = new bootstrap.Modal(document.getElementById('personalInfoModal'));
    modal.show();
}

// Открытие модального окна контакта
function openContactModal(type) {
    const modal = new bootstrap.Modal(document.getElementById('contactModal'));
    const titleEl = document.getElementById('contactModalTitle');
    const labelEl = document.getElementById('contactLabel');
    const hintEl = document.getElementById('contactHint');
    const typeInput = document.getElementById('contactType');
    
    typeInput.value = type;
    
    const contactInfo = {
        telegram: {
            title: 'Telegram',
            label: 'Username енгізіңіз',
            hint: 'Мысалы: @username'
        },
        whatsapp: {
            title: 'WhatsApp',
            label: 'Телефон нөмірін енгізіңіз',
            hint: 'Мысалы: +7 777 123 45 67'
        },
        instagram: {
            title: 'Instagram',
            label: 'Username енгізіңіз',
            hint: 'Мысалы: @username'
        },
        email: {
            title: 'Email',
            label: 'Email енгізіңіз',
            hint: 'Мысалы: example@mail.kz'
        },
        phone: {
            title: 'Телефон',
            label: 'Телефон нөмірін енгізіңіз',
            hint: 'Мысалы: +7 777 123 45 67'
        }
    };
    
    const info = contactInfo[type];
    titleEl.textContent = info.title;
    labelEl.textContent = info.label;
    hintEl.textContent = info.hint;
    
    modal.show();
}

// Добавление другого способа связи
function addOtherContact() {
    const modal = new bootstrap.Modal(document.getElementById('otherContactModal'));
    modal.show();
}

// Удаление другого способа связи
function deleteOtherContact(contactId) {
    if (confirm('Бұл байланыс әдісін жойғыңыз келетініне сенімдісіз бе?')) {
        fetch('ajax/delete_other_contact.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'contact_id=' + contactId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Байланыс жойылды!');
                location.reload();
            } else {
                alert('Қате: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Қате орын алды!');
        });
    }
}

// Выбор иконки социальной сети
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация при открытии модального окна
    const otherContactModal = document.getElementById('otherContactModal');
    if (otherContactModal) {
        otherContactModal.addEventListener('shown.bs.modal', function() {
            const socialIcons = document.querySelectorAll('.social-icon');
            const otherContactType = document.getElementById('otherContactType');
            
            // Устанавливаем обработчики для иконок
            socialIcons.forEach(icon => {
                icon.addEventListener('click', function() {
                    socialIcons.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    if (otherContactType) {
                        otherContactType.value = this.dataset.type;
                        console.log('Selected social type:', this.dataset.type);
                    }
                });
            });
        });
    }
});

// Добавление образования
function addEducation() {
    const modal = new bootstrap.Modal(document.getElementById('educationModal'));
    modal.show();
}

// Добавление языка
function addLanguage() {
    const modal = new bootstrap.Modal(document.getElementById('languageModal'));
    modal.show();
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Переинициализация при открытии модального окна
    const languageModal = document.getElementById('languageModal');
    if (languageModal) {
        languageModal.addEventListener('shown.bs.modal', function() {
            // Сбрасываем выбор при открытии модального окна
            const levelInput = document.getElementById('languageLevel');
            const levelBtns = document.querySelectorAll('.language-level-btn');
            
            levelBtns.forEach(b => b.classList.remove('active'));
            if (levelInput) {
                levelInput.value = '';
            }
            
            // Добавляем обработчики событий
            levelBtns.forEach(btn => {
                // Удаляем старые обработчики (если есть)
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
            });
            
            // Добавляем новые обработчики
            document.querySelectorAll('.language-level-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const levelInput = document.getElementById('languageLevel');
                    
                    // Убираем active со всех кнопок
                    document.querySelectorAll('.language-level-btn').forEach(b => b.classList.remove('active'));
                    
                    // Добавляем active к текущей кнопке
                    this.classList.add('active');
                    
                    // Устанавливаем значение в скрытое поле
                    const level = this.dataset.level;
                    if (levelInput) {
                        levelInput.value = level;
                        console.log('✓ Selected level:', level);
                        console.log('✓ Input value:', levelInput.value);
                    } else {
                        console.error('✗ languageLevel input not found!');
                    }
                });
            });
        });
    }
});

// Удаление аккаунта
function deleteAccount() {
    if (confirm('Сіз профильді жойғыңыз келетініне сенімдісіз бе? Бұл әрекетті қайтару мүмкін емес!')) {
        if (confirm('Соңғы растау: Барлық деректер жойылады!')) {
            // AJAX запрос на удаление
            fetch('ajax/delete_account.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Профиль жойылды');
                    window.location.href = 'logout.php';
                } else {
                    alert('Қате: ' + data.message);
                }
            });
        }
    }
}

// Обработка формы личной информации
document.getElementById('personalInfoForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('ajax/update_personal_info.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Деректер сәтті жаңартылды!');
            location.reload();
        } else {
            alert('Қате: ' + data.message);
        }
    });
});

// Обработка формы контакта
document.getElementById('contactForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('ajax/update_contact.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Байланыс сәтті жаңартылды!');
            location.reload();
        } else {
            alert('Қате: ' + data.message);
        }
    });
});

// Обработка формы других контактов
document.getElementById('otherContactForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('ajax/add_other_contact.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Байланыс әдісі қосылды!');
            location.reload();
        } else {
            alert('Қате: ' + data.message);
        }
    });
});

// Обработка формы образования
document.getElementById('educationForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('ajax/add_education.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Білім қосылды!');
            location.reload();
        } else {
            alert('Қате: ' + data.message);
        }
    });
});

// Обработка формы языка
document.getElementById('languageForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const levelInput = document.getElementById('languageLevel');
    const languageSelect = document.querySelector('[name="language"]');
    
    console.log('=== FORM SUBMIT DEBUG ===');
    console.log('Language input:', languageSelect);
    console.log('Language value:', languageSelect?.value);
    console.log('Level input:', levelInput);
    console.log('Level value:', levelInput?.value);
    
    // Проверяем, что язык выбран
    if (!languageSelect || !languageSelect.value) {
        alert('Тілді таңдаңыз!');
        return;
    }
    
    // Проверяем, что уровень выбран
    if (!levelInput || !levelInput.value) {
        alert('Тіл деңгейін таңдаңыз!');
        console.error('✗ Level not selected!');
        return;
    }
    
    const formData = new FormData(this);
    
    // Выводим все данные формы
    console.log('=== FORM DATA ===');
    for (let [key, value] of formData.entries()) {
        console.log(key + ':', value);
    }
    
    fetch('ajax/add_language.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('=== SERVER RESPONSE ===');
        console.log(data);
        if (data.success) {
            alert('Тіл қосылды!');
            location.reload();
        } else {
            alert('Қате: ' + data.message);
        }
    })
    .catch(error => {
        console.error('=== FETCH ERROR ===');
        console.error(error);
        alert('Қате орын алды!');
    });
});

// Обработка формы настроек
document.getElementById('settingsForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('ajax/update_settings.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Баптаулар сәтті жаңартылды!');
            location.reload();
        } else {
            alert('Қате: ' + data.message);
        }
    });
});

// Загрузка изображений резюме
document.getElementById('resumeUpload')?.addEventListener('change', function(e) {
    const files = e.target.files;
    if (files.length > 8) {
        alert('Максимум 8 сурет жүктей аласыз!');
        return;
    }
    
    const formData = new FormData();
    formData.append('image_type', 'resume');
    for (let file of files) {
        formData.append('images[]', file);
    }
    
    fetch('ajax/upload_images.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Суреттер жүктелді!');
            location.reload();
        } else {
            alert('Қате: ' + data.message);
        }
    });
});

// Загрузка изображений портфолио
document.getElementById('portfolioUpload')?.addEventListener('change', function(e) {
    const files = e.target.files;
    if (files.length > 20) {
        alert('Максимум 20 сурет жүктей аласыз!');
        return;
    }
    
    const formData = new FormData();
    formData.append('image_type', 'portfolio');
    for (let file of files) {
        formData.append('images[]', file);
    }
    
    fetch('ajax/upload_images.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Суреттер жүктелді!');
            location.reload();
        } else {
            alert('Қате: ' + data.message);
        }
    });
});
