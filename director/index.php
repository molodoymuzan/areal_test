<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if (isset($_SESSION['password_change_required']) && $_SESSION['password_change_required'] == 1) {
    session_destroy();
    header('Location: ../login.php?need_change=1');
    exit;
}

if ($_SESSION['role_id'] != 1) {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR · руководитель</title>
    <link rel="stylesheet" href="../css/director.css">
</head>
<body>
    <div class="app">
        <div class="header">
            <div class="user">
                <div class="avatar" id="userInitials"></div>
                <div class="user-info">
                    <h3 id="userName"></h3>
                    <p>Руководитель</p>
                </div>
            </div>
            <div class="actions">
                <button class="btn btn-primary" id="profileBtn">Личный кабинет</button>
                <button class="btn btn-logout" id="logoutBtn">Выйти</button>
            </div>
        </div>

        <div class="welcome">
            <div>
                <h2 id="greeting"></h2>
                <p>Кадровый учет</p>
            </div>
            <div class="date" id="currentDate"></div>
        </div>

        <div class="tabs">
            <button class="tab active" id="tabEmployees">Сотрудники</button>
            <button class="tab" id="tabHr">HR-специалисты</button>
            <button class="tab" id="tabStructure">Структура компании</button>
        </div>

        <div class="filters" id="filtersSection"></div>

        <button class="add-btn" id="addBtn">
            <span>+</span> <span id="addBtnText">Добавить сотрудника</span>
        </button>

        <div id="structurePanel" class="structure-panel" style="display: none;">
            <div class="structure-grid">
                <div class="structure-section">
                    <div class="section-header">
                        <h3>Отделы</h3>
                        <button class="btn btn-primary" id="addDepartmentBtn">+ Добавить отдел</button>
                    </div>
                    <div class="structure-list" id="departmentsList"></div>
                </div>
                <div class="structure-section">
                    <div class="section-header">
                        <h3>Должности</h3>
                        <select id="departmentFilterForPositions" class="department-select">
                            <option value="">Все отделы</option>
                        </select>
                        <button class="btn btn-primary" id="addPositionBtn">+ Добавить должность</button>
                    </div>
                    <div class="structure-list" id="positionsList"></div>
                </div>
            </div>
        </div>

        <div class="cards-grid" id="cardsGrid"></div>

        <div class="stats">
            <div class="stat-item">
                <span class="stat-number" id="totalCount">0</span>
                <span class="stat-label">всего</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" id="activeCount">0</span>
                <span class="stat-label">активных</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" id="dismissedCount">0</span>
                <span class="stat-label">уволено</span>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="profileModal">
    <div class="modal" style="max-width: 700px;">
        <h2>Личный кабинет</h2>
        
        <div class="profile-header">
            <div class="profile-avatar-large" id="profileInitials"></div>
            <div class="profile-info">
                <div class="profile-name" id="profileFullName"></div>
                <div class="profile-role">Руководитель</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Фамилия *</label>
                <input type="text" id="profileLastName" placeholder="Иванов">
            </div>
            <div class="form-group">
                <label>Имя *</label>
                <input type="text" id="profileFirstName" placeholder="Иван">
            </div>
        </div>
        
        <div class="form-group">
            <label>Отчество</label>
            <input type="text" id="profileMiddleName" placeholder="Иванович">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Дата рождения</label>
                <input type="date" id="profileBirthDate">
            </div>
            <div class="form-group">
                <label>Дата приёма</label>
                <input type="date" id="profileHireDate" readonly class="readonly-field" style="background: #f1f5f9; cursor: default;">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Серия паспорта</label>
                <input type="text" id="profilePassportSeries" maxlength="4" placeholder="1234">
            </div>
            <div class="form-group">
                <label>Номер паспорта</label>
                <input type="text" id="profilePassportNumber" maxlength="6" placeholder="567890">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Телефон</label>
                <input type="tel" id="profilePhone" placeholder="+7 (999) 999-99-99">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="profileEmail" placeholder="director@company.ru">
            </div>
        </div>

        <div class="address-section">
            <h4>Адрес проживания</h4>
            <div class="form-group">
                <label>Город</label>
                <input type="text" id="profileCity" placeholder="Москва">
            </div>
            <div class="form-group">
                <label>Улица</label>
                <input type="text" id="profileStreet" placeholder="ул. Тверская">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Дом</label>
                    <input type="text" id="profileHouse" placeholder="12">
                </div>
                <div class="form-group">
                    <label>Квартира</label>
                    <input type="text" id="profileApartment" placeholder="34">
                </div>
                <div class="form-group">
                    <label>Индекс</label>
                    <input type="text" id="profilePostalCode" placeholder="123456" maxlength="6">
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Отдел</label>
                <input type="text" id="profileDepartment" readonly class="readonly-field" style="background: #f1f5f9; cursor: default;" value="—">
            </div>
            <div class="form-group">
                <label>Должность</label>
                <input type="text" id="profilePosition" readonly class="readonly-field" style="background: #f1f5f9; cursor: default;" value="—">
            </div>
        </div>

        <div class="form-group">
            <label>Зарплата (₽)</label>
            <input type="text" id="profileSalary" readonly class="readonly-field" style="background: #f1f5f9; cursor: default;" value="—">
        </div>
        <div class="password-section">
            <h4>Смена пароля</h4>
            <div class="password-field">
                <input type="password" id="currentPassword" placeholder="Текущий пароль">
            </div>
            <div class="password-row">
                <input type="password" id="newPassword" placeholder="Новый пароль">
                <input type="password" id="confirmPassword" placeholder="Подтверждение">
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn" id="closeProfileBtn">Отмена</button>
            <button class="btn btn-primary" id="saveProfileBtn">Сохранить изменения</button>
        </div>
    </div>
</div>

    <div class="modal-overlay" id="employeeModal">
        <div class="modal">
            <h2 id="employeeModalTitle">Новый сотрудник</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Фамилия *</label>
                    <input type="text" id="lastName" placeholder="Иванов">
                </div>
                <div class="form-group">
                    <label>Имя *</label>
                    <input type="text" id="firstName" placeholder="Иван">
                </div>
            </div>
            
            <div class="form-group">
                <label>Отчество</label>
                <input type="text" id="middleName" placeholder="Иванович">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Дата рождения</label>
                    <input type="date" id="birthDate">
                </div>
                <div class="form-group">
                    <label>Дата приёма</label>
                    <input type="date" id="hireDate">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Серия паспорта</label>
                    <input type="text" id="passportSeries" maxlength="4" placeholder="1234">
                </div>
                <div class="form-group">
                    <label>Номер паспорта</label>
                    <input type="text" id="passportNumber" maxlength="6" placeholder="567890">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Телефон</label>
                    <input type="tel" id="phone" placeholder="+7 (999) 999-99-99">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="email" placeholder="employee@company.ru">
                </div>
            </div>

            <div class="address-section">
                <h4>Адрес проживания</h4>
                <div class="form-group">
                    <label>Город</label>
                    <input type="text" id="city" placeholder="Москва">
                </div>
                <div class="form-group">
                    <label>Улица</label>
                    <input type="text" id="street" placeholder="ул. Тверская">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Дом</label>
                        <input type="text" id="house" placeholder="12">
                    </div>
                    <div class="form-group">
                        <label>Квартира</label>
                        <input type="text" id="apartment" placeholder="34">
                    </div>
                    <div class="form-group">
                        <label>Индекс</label>
                        <input type="text" id="postalCode" placeholder="123456" maxlength="6">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Отдел</label>
                    <select id="department"></select>
                </div>
                <div class="form-group">
                    <label>Должность</label>
                    <select id="position"></select>
                </div>
            </div>

            <div class="form-group">
                <label>Зарплата (₽)</label>
                <input type="number" id="salary" placeholder="250000">
            </div>

            <div class="modal-footer">
                <button class="btn" id="closeEmployeeBtn">Отмена</button>
                <button class="btn btn-primary" id="saveEmployeeBtn">Сохранить</button>
            </div>
        </div>
    </div>

<div class="modal-overlay" id="hrModal">
    <div class="modal">
        <h2 id="hrModalTitle">Новый HR-сотрудник</h2>
        
        <div class="form-row">
            <div class="form-group">
                <label>Фамилия *</label>
                <input type="text" id="hrLastName" placeholder="Петрова">
            </div>
            <div class="form-group">
                <label>Имя *</label>
                <input type="text" id="hrFirstName" placeholder="Анна">
            </div>
        </div>
        
        <div class="form-group">
            <label>Отчество</label>
            <input type="text" id="hrMiddleName" placeholder="Сергеевна">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Дата рождения</label>
                <input type="date" id="hrBirthDate">
            </div>
            <div class="form-group">
                <label>Дата приёма</label>
                <input type="date" id="hrHireDate">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Серия паспорта</label>
                <input type="text" id="hrPassportSeries" maxlength="4" placeholder="1234">
            </div>
            <div class="form-group">
                <label>Номер паспорта</label>
                <input type="text" id="hrPassportNumber" maxlength="6" placeholder="567890">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Телефон</label>
                <input type="tel" id="hrPhone" placeholder="+7 (999) 999-99-99">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="hrEmail" placeholder="employee@company.ru">
            </div>
        </div>

        <div class="address-section">
            <h4>Адрес проживания</h4>
            <div class="form-group">
                <label>Город</label>
                <input type="text" id="hrCity" placeholder="Москва">
            </div>
            <div class="form-group">
                <label>Улица</label>
                <input type="text" id="hrStreet" placeholder="ул. Тверская">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Дом</label>
                    <input type="text" id="hrHouse" placeholder="12">
                </div>
                <div class="form-group">
                    <label>Квартира</label>
                    <input type="text" id="hrApartment" placeholder="34">
                </div>
                <div class="form-group">
                    <label>Индекс</label>
                    <input type="text" id="hrPostalCode" placeholder="123456" maxlength="6">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Должность</label>
            <select id="hrPosition">
                <option value="4">HR Generalist</option>
                <option value="7">Руководитель HR</option>
            </select>
        </div>

        <div class="form-group">
            <label>Зарплата (₽)</label>
            <input type="number" id="hrSalary" placeholder="180000">
        </div>

        <div id="hrPasswordBlock" style="display: none;">
            <div class="password-box">
                <p>Временный пароль</p>
                <div class="password-display" id="hrTempPassword"></div>
                <div class="warning">Пароль показывается только один раз</div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn" id="closeHrModal">Отмена</button>
            <button class="btn btn-primary" id="saveHrBtn">Сохранить</button>
        </div>
    </div>
</div>

    <div class="modal-overlay" id="departmentModal">
        <div class="modal">
            <h2 id="departmentModalTitle">Новый отдел</h2>
            <div class="form-group">
                <label>Название отдела *</label>
                <input type="text" id="departmentName" placeholder="Например: Разработка">
            </div>
            <div class="modal-footer">
                <button class="btn" id="closeDepartmentModal">Отмена</button>
                <button class="btn btn-primary" id="saveDepartmentBtn">Сохранить</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="positionModal">
        <div class="modal">
            <h2 id="positionModalTitle">Новая должность</h2>
            <div class="form-group">
                <label>Отдел *</label>
                <select id="positionDepartmentId"></select>
            </div>
            <div class="form-group">
                <label>Название должности *</label>
                <input type="text" id="positionName" placeholder="Например: Senior Developer">
            </div>
            <div class="modal-footer">
                <button class="btn" id="closePositionModal">Отмена</button>
                <button class="btn btn-primary" id="savePositionBtn">Сохранить</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="fireConfirmModal">
        <div class="modal">
            <h2>Подтверждение увольнения</h2>
            <p id="fireConfirmMessage" style="margin-bottom: 24px;">Уволить сотрудника?</p>
            <div class="modal-footer">
                <button class="btn" id="cancelFireBtn">Отмена</button>
                <button class="btn btn-danger" id="confirmFireBtn">Уволить</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="confirmModal">
        <div class="modal">
            <h2>Подтверждение</h2>
            <p id="confirmMessage" style="margin-bottom: 24px;">Вы уверены?</p>
            <div class="modal-footer">
                <button class="btn" id="cancelConfirmBtn">Отмена</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">Удалить</button>
            </div>
        </div>
    </div>

    <script src="../js/common.js"></script>
    <script src="../js/director.js"></script>

</body>
</html>