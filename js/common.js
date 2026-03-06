
function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').substring(0, 2);
}

function loadUserData() {
    fetch('../api/profile.php')
        .then(res => res.json())
        .then(data => {
            const userNameEl = document.getElementById('userName');
            if (userNameEl) userNameEl.textContent = data.full_name;
            
            const userInitialsEl = document.getElementById('userInitials');
            if (userInitialsEl) userInitialsEl.textContent = getInitials(data.full_name);
            
            const profileFullNameEl = document.getElementById('profileFullName');
            if (profileFullNameEl) profileFullNameEl.textContent = data.full_name;
            
            const profileInitialsEl = document.getElementById('profileInitials');
            if (profileInitialsEl) profileInitialsEl.textContent = getInitials(data.full_name);
            
            const now = new Date();
            const hours = now.getHours();
            const firstName = data.first_name || 'пользователь';
            
            let greeting = 'Добрый вечер';
            if (hours >= 5 && hours < 12) greeting = 'Доброе утро';
            else if (hours >= 12 && hours < 18) greeting = 'Добрый день';
            
            const greetingEl = document.getElementById('greeting');
            if (greetingEl) greetingEl.textContent = `${greeting}, ${firstName}!`;
            
            const dateEl = document.getElementById('currentDate');
            if (dateEl) {
                dateEl.textContent = now.toLocaleDateString('ru-RU', { 
                    weekday: 'long', day: 'numeric', month: 'long' 
                });
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки данных пользователя:', error);
            
            const userNameEl = document.getElementById('userName');
            if (userNameEl) userNameEl.textContent = 'Ошибка загрузки';
            
            const userInitialsEl = document.getElementById('userInitials');
            if (userInitialsEl) userInitialsEl.textContent = '--';
            
            const now = new Date();
            const hours = now.getHours();
            let greeting = 'Добрый вечер';
            if (hours >= 5 && hours < 12) greeting = 'Доброе утро';
            else if (hours >= 12 && hours < 18) greeting = 'Добрый день';
            
            const greetingEl = document.getElementById('greeting');
            if (greetingEl) greetingEl.textContent = `${greeting}, пользователь!`;
            
            const dateEl = document.getElementById('currentDate');
            if (dateEl) {
                dateEl.textContent = now.toLocaleDateString('ru-RU', { 
                    weekday: 'long', day: 'numeric', month: 'long' 
                });
            }
        });
}

function loadDepartments(selectId = 'department', excludeHr = false) {
    fetch('../api/structure.php')
        .then(res => res.json())
        .then(data => {
            const deptSelect = document.getElementById(selectId);
            if (deptSelect) {
                let options = '<option value="">Выберите отдел</option>';
                
                let departments = data.departments;
                if (excludeHr) {
                    departments = departments.filter(d => d.id != 3);
                }
                
                departments.forEach(d => {
                    options += `<option value="${d.id}">${d.name}</option>`;
                });
                deptSelect.innerHTML = options;
            }
        });
}

function updatePositionSelect(deptId, positionSelectId = 'position') {
    const posSelect = document.getElementById(positionSelectId);
    if (!posSelect) return;
    
    if (deptId) {
        fetch('../api/structure.php')
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">Выберите должность</option>';
                data.positions
                    .filter(p => p.department_id == deptId)
                    .forEach(p => {
                        options += `<option value="${p.id}">${p.name}</option>`;
                    });
                posSelect.innerHTML = options;
            });
    } else {
        posSelect.innerHTML = '<option value="">Выберите должность</option>';
    }
}

function phoneMask(event) {
    let input = event.target;
    let value = input.value.replace(/\D/g, '');
    let formattedValue = '';
    
    if (value.length > 0) {
        formattedValue = '+7';
        if (value.length > 1) {
            formattedValue += ' (' + value.substring(1, 4);
        }
        if (value.length >= 4) {
            formattedValue += ') ' + value.substring(4, 7);
        }
        if (value.length >= 7) {
            formattedValue += '-' + value.substring(7, 9);
        }
        if (value.length >= 9) {
            formattedValue += '-' + value.substring(9, 11);
        }
    }
    
    input.value = formattedValue;
}

function digitsOnly(event) {
    let input = event.target;
    input.value = input.value.replace(/\D/g, '');
}

function formatNameInput(event) {
    let input = event.target;
    input.value = input.value.charAt(0).toUpperCase() + input.value.slice(1).toLowerCase();
}

function saveEmployee(editId, roleId = 3) {
    if (!document.getElementById('lastName').value || !document.getElementById('firstName').value) {
        alert('Заполните фамилию и имя');
        return Promise.reject();
    }
    if (!document.getElementById('department').value) {
        alert('Выберите отдел');
        return Promise.reject();
    }
    if (!document.getElementById('position').value) {
        alert('Выберите должность');
        return Promise.reject();
    }
    
    const url = editId ? '../api/update_employee.php' : '../api/create_employee.php';
    const data = {
        id: editId,
        lastName: document.getElementById('lastName').value,
        firstName: document.getElementById('firstName').value,
        middleName: document.getElementById('middleName').value,
        birthDate: document.getElementById('birthDate').value,
        hireDate: document.getElementById('hireDate').value,
        passportSeries: document.getElementById('passportSeries').value,
        passportNumber: document.getElementById('passportNumber').value,
        phone: document.getElementById('phone').value,
        email: document.getElementById('email').value,
        city: document.getElementById('city').value,
        street: document.getElementById('street').value,
        house: document.getElementById('house').value,
        apartment: document.getElementById('apartment').value,
        postalCode: document.getElementById('postalCode').value,
        departmentId: document.getElementById('department').value,
        positionId: document.getElementById('position').value,
        salary: document.getElementById('salary').value,
        roleId: roleId
    };

    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }).then(res => res.json());
}

function fireEmployee(fireId) {
    return fetch('../api/fire_employee.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: fireId })
    }).then(res => res.json());
}
