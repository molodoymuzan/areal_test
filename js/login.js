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
            if (data.role_id == 2) {
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