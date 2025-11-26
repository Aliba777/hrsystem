<!-- Modal: Личная информация -->
<div class="modal fade" id="personalInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Жеке ақпаратты өңдеу</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="personalInfoForm">
                    <div class="mb-3">
                        <label class="form-label">Аты *</label>
                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тегі *</label>
                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Қала *</label>
                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($user['city'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Туған күні</label>
                        <input type="date" name="birth_date" class="form-control" value="<?= $user['birth_date'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Жынысы</label>
                        <select name="gender" class="form-control">
                            <option value="">Таңдаңыз</option>
                            <option value="male" <?= ($user['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Ер</option>
                            <option value="female" <?= ($user['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Әйел</option>
                            <option value="other" <?= ($user['gender'] ?? '') == 'other' ? 'selected' : '' ?>>Басқа</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Азаматтығы</label>
                        <input type="text" name="citizenship" class="form-control" value="<?= htmlspecialchars($user['citizenship'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Сақтау</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Контакт (универсальный) -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalTitle">Байланыс</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm">
                    <input type="hidden" name="contact_type" id="contactType">
                    <div class="mb-3">
                        <label class="form-label" id="contactLabel">Мән</label>
                        <input type="text" name="contact_value" id="contactValue" class="form-control" required>
                        <small class="text-muted" id="contactHint"></small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Сақтау</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px 20px 0 0;
}
.modal-content {
    border-radius: 20px;
    border: none;
}
</style>


<!-- Modal: Другие способы связи / Әлеуметтік желілер -->
<div class="modal fade" id="otherContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Әлеуметтік желі қосу</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-center mb-3">Әлеуметтік желі түрін таңдаңыз:</p>
                <div class="social-icon-selector text-center mb-4">
                    <i class="fab fa-github social-icon" data-type="github" title="GitHub"></i>
                    <i class="fab fa-behance social-icon" data-type="behance" title="Behance"></i>
                    <i class="fab fa-dribbble social-icon" data-type="dribbble" title="Dribbble"></i>
                    <i class="fab fa-vk social-icon" data-type="vk" title="VK"></i>
                    <i class="fab fa-linkedin social-icon" data-type="linkedin" title="LinkedIn"></i>
                    <i class="fab fa-facebook social-icon" data-type="facebook" title="Facebook"></i>
                    <i class="fab fa-twitter social-icon" data-type="twitter" title="Twitter"></i>
                    <i class="fab fa-youtube social-icon" data-type="youtube" title="YouTube"></i>
                    <i class="fas fa-link social-icon active" data-type="website" title="Басқа сайт"></i>
                </div>
                <form id="otherContactForm">
                    <div class="mb-3">
                        <label class="form-label">Сілтеме *</label>
                        <input type="url" name="contact_url" id="otherContactUrl" class="form-control" placeholder="https://example.com" required>
                        <small class="text-muted">Толық сілтемені енгізіңіз (https:// бастап)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Сипаттама *</label>
                        <input type="text" name="contact_description" id="otherContactDescription" class="form-control" placeholder='Мысалы: "Менің портфолиом"' required>
                        <small class="text-muted">Бұл сілтеме не туралы екенін жазыңыз</small>
                    </div>
                    <input type="hidden" name="contact_type" id="otherContactType" value="website">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Қосу
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Образование -->
<div class="modal fade" id="educationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Білім қосу</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="educationForm">
                    <div class="mb-3">
                        <label class="form-label">Білім деңгейі *</label>
                        <select name="level" class="form-control" required>
                            <option value="">Таңдаңыз</option>
                            <option value="Орта білім">Орта білім</option>
                            <option value="Колледж">Колледж</option>
                            <option value="Бакалавр">Бакалавр</option>
                            <option value="Магистр">Магистр</option>
                            <option value="Доктор">Доктор (PhD)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Оқу орны *</label>
                        <input type="text" name="institution" class="form-control" placeholder="Университет атауын енгізіңіз" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Факультет</label>
                        <input type="text" name="faculty" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Мамандық</label>
                        <input type="text" name="field_of_study" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Басталған жылы *</label>
                            <input type="number" name="start_year" class="form-control" min="1950" max="2030" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Аяқталған жылы *</label>
                            <input type="number" name="end_year" class="form-control" min="1950" max="2050" required>
                            <small class="text-muted">Егер әлі оқисаңыз, болжамды жылды көрсетіңіз</small>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Сақтау</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Язык -->
<div class="modal fade" id="languageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Тіл қосу</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="languageForm">
                    <div class="mb-3">
                        <label class="form-label">Тіл *</label>
                        <select name="language" id="languageSelect" class="form-control" required style="width: 100%;">
                            <option value="">Таңдаңыз немесе іздеңіз...</option>
                            <?php
                            $languages = [
                                'Қазақ', 'Русский', 'English',
                                'Deutsch', 'Français', 'Español', 'Italiano', 'Português',
                                'Nederlands', 'Polski', 'Українська', 'Čeština', 'Slovenčina',
                                'Magyar', 'Română', 'Български', 'Ελληνικά', 'Svenska',
                                'Norsk', 'Dansk', 'Suomi', 'Íslenska', 'Lietuvių', 'Latviešu',
                                'Eesti', 'Српски', 'Hrvatski', 'Slovenščina', 'Shqip', 'Македонски',
                                '中文', '日本語', '한국어', 'हिन्दी', 'বাংলা', 'اردو',
                                'தமிழ்', 'తెలుగు', 'ಕನ್ನಡ', 'മലയാളം', 'ગુજરાતી', 'ਪੰਜਾਬੀ',
                                'ไทย', 'Tiếng Việt', 'Bahasa Indonesia', 'Bahasa Melayu', 'Tagalog',
                                'မြန်မာ', 'ខ្មែរ', 'ລາວ', 'Монгол',
                                'Türkçe', 'Azərbaycan', 'Өзбек', 'Қырғыз', 'Türkmen', 'Татар',
                                'العربية', 'עברית', 'فارسی', 'پښتو', 'کوردی', 'تاجیکی',
                                'ქართული', 'Հայերեն', 'Чеченский', 'Ингушский',
                                'Afrikaans', 'Kiswahili', 'Yorùbá', 'Igbo', 'Hausa', 'Zulu', 'Xhosa', 'አማርኛ',
                                'Беларуская', 'Bosanski', 'Català', 'Cymraeg', 'Esperanto',
                                'Euskara', 'Gaeilge', 'Galego', 'Latina', 'Malti'
                            ];
                            foreach ($languages as $lang) {
                                echo "<option value=\"" . htmlspecialchars($lang) . "\">" . htmlspecialchars($lang) . "</option>";
                            }
                            ?>
                        </select>
                        <small class="text-muted">Іздеу үшін теруді бастаңыз</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Деңгей *</label>
                        <div class="language-levels">
                            <button type="button" class="language-level-btn" data-level="A1">A1</button>
                            <button type="button" class="language-level-btn" data-level="A2">A2</button>
                            <button type="button" class="language-level-btn" data-level="B1">B1</button>
                            <button type="button" class="language-level-btn" data-level="B2">B2</button>
                            <button type="button" class="language-level-btn" data-level="C1">C1</button>
                            <button type="button" class="language-level-btn" data-level="C2">C2</button>
                            <button type="button" class="language-level-btn" data-level="Родной">Ана тілі</button>
                        </div>
                        <input type="hidden" name="proficiency" id="languageLevel" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Сақтау</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Настройки (с табами) -->
<div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Баптаулар</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button">
                            Жеке деректер
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="images-tab" data-bs-toggle="tab" data-bs-target="#images" type="button">
                            Суреттер
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="settingsTabContent">
                    <!-- Таб: Личные данные -->
                    <div class="tab-pane fade show active" id="personal" role="tabpanel">
                        <form id="settingsForm">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Телефон</label>
                                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Жаңа құпия сөз</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Өзгерту үшін енгізіңіз">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Құпия сөзді растау</label>
                                <input type="password" name="confirm_password" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">Сақтау</button>
                        </form>
                        <hr>
                        <button class="btn btn-danger w-100" onclick="deleteAccount()">
                            <i class="fas fa-trash me-2"></i>Профильді жою
                        </button>
                    </div>
                    
                    <!-- Таб: Изображения -->
                    <div class="tab-pane fade" id="images" role="tabpanel">
                        <h6>Резюмеге суреттер (макс 8)</h6>
                        <div id="resumeImages" class="image-grid mb-4">
                            <!-- Изображения будут загружаться через AJAX -->
                        </div>
                        <input type="file" id="resumeUpload" accept="image/*" multiple style="display:none">
                        <button class="btn btn-outline-primary w-100 mb-4" onclick="document.getElementById('resumeUpload').click()">
                            <i class="fas fa-upload me-2"></i>Сурет жүктеу
                        </button>
                        
                        <h6>Жұмыс үлгілері / Портфолио (макс 20)</h6>
                        <div id="portfolioImages" class="image-grid mb-4">
                            <!-- Изображения будут загружаться через AJAX -->
                        </div>
                        <input type="file" id="portfolioUpload" accept="image/*" multiple style="display:none">
                        <button class="btn btn-outline-primary w-100" onclick="document.getElementById('portfolioUpload').click()">
                            <i class="fas fa-upload me-2"></i>Сурет жүктеу
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.social-icon-selector {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
    gap: 10px;
    justify-content: center;
    max-width: 500px;
    margin: 0 auto;
}

.social-icon {
    font-size: 2rem;
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
    aspect-ratio: 1;
}

.social-icon:hover {
    border-color: #667eea;
    background: #f8f9fa;
    transform: scale(1.05);
}

.social-icon.active {
    border-color: #667eea;
    background: #667eea;
    color: white;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.language-levels {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.language-level-btn {
    padding: 10px 20px;
    border: 2px solid #e9ecef;
    border-radius: 20px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.language-level-btn:hover {
    border-color: #667eea;
    background: #f8f9fa;
    transform: scale(1.05);
}

.language-level-btn.active {
    border-color: #667eea;
    background: #667eea;
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    transform: scale(1.05);
}

.image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 10px;
}

.image-grid img {
    width: 100%;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
}
</style>
