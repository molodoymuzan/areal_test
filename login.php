<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR · вход</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h1>HR · учет сотрудников</h1>
                <p>Вход в систему</p>
            </div>

            <div class="error-message" id="errorMessage">
                <span class="error-icon">⚠</span> Неверный email или пароль
            </div>

            <form id="loginForm">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="email" placeholder="Введите email">
                </div>
                
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" id="password" placeholder="Введите пароль">
                </div>

                <button type="button" class="btn" id="loginBtn">Войти</button>
            </form>
        </div>
    </div>

    <script src="js/login.js"></script>
</body>
</html>