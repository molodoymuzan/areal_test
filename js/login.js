document.getElementById('loginBtn').onclick = () => {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();

    if (!email || !password) {
        showError();
        return;
    }

    fetch('/areal_test/api/auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.password_change_required) {
                showPasswordChangeModal(email);
            } else if (data.role_id == 2) {
                window.location.href = 'hr/index.php';
            } else if (data.role_id == 1) {
                window.location.href = 'director/index.php';
            }
        } else {
            showError();
        }
    });
};

document.getElementById('password').onkeypress = (e) => {
    if (e.key === 'Enter') {
        document.getElementById('loginBtn').click();
    }
};

document.getElementById('email').oninput = hideError;
document.getElementById('password').oninput = hideError;

function showError() {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.style.display = 'flex';
    setTimeout(() => errorDiv.style.display = 'none', 3000);
}

function hideError() {
    document.getElementById('errorMessage').style.display = 'none';
}

function showPasswordChangeModal(userEmail) {
    document.getElementById('loginForm').style.display = 'none';
    document.querySelector('.error-message').style.display = 'none';
    
    const modalHtml = `
        <div class="password-change-form">
            <div class="icon-lock">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1e293b" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <h2>Смена пароля</h2>
            <p>В целях безопасности смените<br>временный пароль</p>
            
            <div class="form-group">
                <label>Новый пароль</label>
                <input type="password" id="newPassword" placeholder="минимум 8 символов">
            </div>
            
            <div class="form-group">
                <label>Подтверждение</label>
                <input type="password" id="confirmPassword" placeholder="повторите пароль">
            </div>
            
            <button class="btn" id="submitPasswordChange">Сменить пароль</button>
            
            <p class="hint">После смены пароля вы будете перенаправлены в личный кабинет</p>
        </div>
    `;
    
    document.querySelector('.login-card').insertAdjacentHTML('beforeend', modalHtml);
    
    document.getElementById('submitPasswordChange').onclick = () => {
        const newPass = document.getElementById('newPassword').value;
        const confirm = document.getElementById('confirmPassword').value;
        
        if (!newPass || !confirm) {
            showTemporaryMessage('Заполните все поля');
            return;
        }
        
        if (newPass !== confirm) {
            showTemporaryMessage('Пароли не совпадают');
            return;
        }
        
        if (newPass.length < 8) {
            showTemporaryMessage('Пароль должен быть минимум 8 символов');
            return;
        }
        
        console.log('Email to send:', userEmail);
        
        fetch('/areal_test/api/change_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: userEmail, newPassword: newPass })
        })
        .then(res => res.json())
        .then(data => {
    if (data.success) {
        fetch('/areal_test/api/update_session.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password_change_required: 0 })
        })
        .then(() => {
            const form = document.querySelector('.password-change-form');
            form.style.animation = 'fadeOut 0.3s ease-out forwards';
            setTimeout(() => {
                if (data.role_id == 2) {
                    window.location.href = 'hr/index.php';
                } else {
                    window.location.href = 'director/index.php';
                }
            }, 300);
        });
    } else {
        showTemporaryMessage(data.error || 'Ошибка при смене пароля');
    }
});    };
    
    function showTemporaryMessage(text) {
        const msg = document.createElement('div');
        msg.textContent = text;
        msg.className = 'temp-error-message';
        
        const oldMsg = document.querySelector('.temp-error-message');
        if (oldMsg) oldMsg.remove();
        
        document.querySelector('.password-change-form').appendChild(msg);
        
        setTimeout(() => msg.remove(), 3000);
    }
}