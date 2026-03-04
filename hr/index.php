<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR · специалист</title>
    <link rel="stylesheet" href="../css/hr.css">
</head>
<body>
    <div class="app">
        <div class="header">
            <div class="user">
                <div class="avatar" id="userInitials"></div>
                <div class="user-info">
                    <h3 id="userName"></h3>
                    <p>HR-специалист</p>
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

        <div class="filters" id="filtersSection"></div>

        <button class="add-btn" id="addBtn">
            <span>+</span> Добавить сотрудника
        </button>

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
        <div class="modal">
            <h2>Личный кабинет</h2>
            <div class="profile-header">
                <div class="profile-avatar-large" id="profileInitials"></div>
                <div class="profile-info">
                    <div class="profile-name" id="profileFullName"></div>
                    <div class="profile-role">HR-специалист</div>
                </div>
            </div>
            <div class="profile-grid" id="profileFields"></div>
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

<script src="../js/common.js"></script>
<script src="../js/hr.js"></script>

</body>
</html>