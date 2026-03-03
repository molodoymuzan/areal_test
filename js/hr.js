let currentEditId = null;
let currentFireId = null;

function updateGreeting() {
    const now = new Date();
    const hours = now.getHours();
    const userName = document.getElementById('userName').textContent.split(' ')[0];
    
    let greeting = 'Добрый вечер';
    if (hours >= 5 && hours < 12) greeting = 'Доброе утро';
    else if (hours >= 12 && hours < 18) greeting = 'Добрый день';
    
    document.getElementById('greeting').textContent = `${greeting}, ${userName}`;
    
    const dateStr = now.toLocaleDateString('ru-RU', {
        weekday: 'long', day: 'numeric', month: 'long'
    });
    document.getElementById('currentDate').textContent = dateStr;
}

function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').substring(0, 2);
}

function loadUserData() {
    fetch('../api/profile.php')
        .then(res => res.json())
        .then(data => {
            document.getElementById('userName').textContent = data.full_name;
            document.getElementById('userInitials').textContent = getInitials(data.full_name);
            document.getElementById('profileFullName').textContent = data.full_name;
            document.getElementById('profileInitials').textContent = getInitials(data.full_name);
            
            document.getElementById('profileFields').innerHTML = `
                <div class="profile-field">
                    <label>Фамилия</label>
                    <input type="text" value="${data.last_name || ''}" id="profileLastName">
                </div>
                <div class="profile-field">
                    <label>Имя</label>
                    <input type="text" value="${data.first_name || ''}" id="profileFirstName">
                </div>
                <div class="profile-field profile-field-full">
                    <label>Отчество</label>
                    <input type="text" value="${data.middle_name || ''}" id="profileMiddleName">
                </div>
                <div class="profile-field profile-field-full">
                    <label>Телефон</label>
                    <input type="tel" value="${data.phone || ''}" id="profilePhone">
                </div>
                <div class="profile-field profile-field-full">
                    <label>Email</label>
                    <input type="email" value="${data.email || ''}" id="profileEmail">
                </div>
            `;
        });
}

function renderFilters() {
    fetch('../api/structure.php')
        .then(res => res.json())
        .then(data => {
            let deptOptions = '<option value="all">Все отделы</option>';
            data.departments.forEach(d => {
                deptOptions += `<option value="${d.id}">${d.name}</option>`;
            });
            
            let posOptions = '<option value="all">Все должности</option>';
            data.positions.forEach(p => {
                posOptions += `<option value="${p.id}">${p.name}</option>`;
            });
            
            document.getElementById('filtersSection').innerHTML = `
                <div class="filter-item">
                    <label>Отдел</label>
                    <select id="departmentFilter">${deptOptions}</select>
                </div>
                <div class="filter-item">
                    <label>Должность</label>
                    <select id="positionFilter">${posOptions}</select>
                </div>
                <div class="filter-item">
                    <label>Поиск</label>
                    <input type="text" id="searchInput" placeholder="ФИО...">
                </div>
                <button class="btn-reset" id="resetBtn">Сбросить</button>
            `;
            
            document.getElementById('resetBtn').onclick = resetFilters;
            document.getElementById('searchInput').oninput = renderCards;
            document.getElementById('departmentFilter').onchange = renderCards;
            document.getElementById('positionFilter').onchange = renderCards;
        });
}

function resetFilters() {
    document.getElementById('departmentFilter').value = 'all';
    document.getElementById('positionFilter').value = 'all';
    document.getElementById('searchInput').value = '';
    renderCards();
}

function renderCards() {
    const deptFilter = document.getElementById('departmentFilter')?.value || 'all';
    const posFilter = document.getElementById('positionFilter')?.value || 'all';
    const search = document.getElementById('searchInput')?.value || '';
    
    fetch(`../api/employees.php?department=${deptFilter}&position=${posFilter}&search=${search}`)
        .then(res => res.json())
        .then(employees => {
            let html = '';
            employees.forEach(e => {
                const initials = getInitials(e.full_name);
                const statusClass = e.status_id == 2 ? 'dismissed' : '';
                const statusText = e.status_id == 2 ? 'УВОЛЕН' : 'РАБОТАЕТ';
                const cardClass = e.status_id == 2 ? 'card dismissed' : 'card';
                
                const addressParts = [];
                if (e.city) addressParts.push(e.city);
                if (e.street) addressParts.push(e.street);
                if (e.house) addressParts.push('д. ' + e.house);
                if (e.apartment) addressParts.push('кв. ' + e.apartment);
                if (e.postal_code) addressParts.push('(' + e.postal_code + ')');
                const addressText = addressParts.join(', ') || '—';
                
                html += `
                    <div class="${cardClass}" data-id="${e.id}">
                        <div class="card-header">
                            <div class="card-avatar">${initials}</div>
                            <div class="card-title">
                                <h4>${e.full_name}</h4>
                            </div>
                            <div class="card-status ${statusClass}">${statusText}</div>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-row">
                                    <span class="info-label">Рождение</span>
                                    <span class="info-value">${e.birth_date || '—'}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Паспорт</span>
                                    <span class="info-value">${e.passport_series || ''} ${e.passport_number || ''}</span>
                                </div>
                            </div>

                            <div class="contacts">
                                <div class="contact-item">
                                    <span class="icon icon-phone"></span>
                                    <span>${e.phone || '—'}</span>
                                </div>
                                <div class="contact-item">
                                    <span class="icon icon-email"></span>
                                    <span>${e.email || '—'}</span>
                                </div>
                            </div>

                            <div class="address-block">
                                <div class="address-label">Адрес</div>
                                <div class="address-line">${addressText}</div>
                            </div>

                            <div class="position-footer">
                                <span class="department-tag">${e.department_name}</span>
                                <span class="position-tag highlight">${e.position_name}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Зарплата</span>
                                <span class="info-value salary">${e.salary ? Number(e.salary).toLocaleString() : '0'} ₽</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Принят</span>
                                <span class="info-value">${e.hire_date || '—'}</span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="card-btn edit" onclick="editEmployee(${e.id})" ${e.status_id == 2 ? 'disabled' : ''}>✎</button>
                            <button class="card-btn fire" onclick="confirmFire(${e.id}, '${e.full_name}')" ${e.status_id == 2 ? 'disabled' : ''}>✕</button>
                        </div>
                    </div>
                `;
            });
            document.getElementById('cardsGrid').innerHTML = html;
            
            updateStats();
        });
}

function updateStats() {
    fetch('../api/employees.php')
        .then(res => res.json())
        .then(employees => {
            const total = employees.length;
            const active = employees.filter(e => e.status_id == 1).length;
            const dismissed = employees.filter(e => e.status_id == 2).length;
            
            document.getElementById('totalCount').innerText = total;
            document.getElementById('activeCount').innerText = active;
            document.getElementById('dismissedCount').innerText = dismissed;
        });
}

function loadDepartments() {
    fetch('../api/structure.php')
        .then(res => res.json())
        .then(data => {
            const deptSelect = document.getElementById('department');
            let options = '<option value="">Выберите отдел</option>';
            data.departments.forEach(d => {
                options += `<option value="${d.id}">${d.name}</option>`;
            });
            deptSelect.innerHTML = options;
        });
}

document.getElementById('department')?.addEventListener('change', function() {
    const posSelect = document.getElementById('position');
    const deptId = this.value;
    
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
});

window.editEmployee = (id) => {
    fetch(`../api/employee.php?id=${id}`)
        .then(res => res.json())
        .then(emp => {
            if (emp.status_id == 2) return;
            
            currentEditId = id;
            document.getElementById('employeeModalTitle').innerText = 'Редактировать сотрудника';
            
            document.getElementById('lastName').value = emp.last_name || '';
            document.getElementById('firstName').value = emp.first_name || '';
            document.getElementById('middleName').value = emp.middle_name || '';
            document.getElementById('birthDate').value = emp.birth_date;
            document.getElementById('hireDate').value = emp.hire_date;
            document.getElementById('passportSeries').value = emp.passport_series || '';
            document.getElementById('passportNumber').value = emp.passport_number || '';
            document.getElementById('phone').value = emp.phone || '';
            document.getElementById('email').value = emp.email || '';
            document.getElementById('city').value = emp.city || '';
            document.getElementById('street').value = emp.street || '';
            document.getElementById('house').value = emp.house || '';
            document.getElementById('apartment').value = emp.apartment || '';
            document.getElementById('postalCode').value = emp.postal_code || '';
            document.getElementById('salary').value = emp.salary;
            
            document.getElementById('department').value = emp.department_id;
            setTimeout(() => {
                document.getElementById('position').value = emp.position_id;
            }, 100);
            
            document.getElementById('employeeModal').classList.add('active');
        });
};

window.confirmFire = (id, name) => {
    currentFireId = id;
    document.getElementById('fireConfirmMessage').innerText = `Уволить сотрудника ${name}?`;
    document.getElementById('fireConfirmModal').classList.add('active');
};

document.getElementById('addBtn').onclick = () => {
    currentEditId = null;
    document.getElementById('employeeModalTitle').innerText = 'Новый сотрудник';
    
    document.getElementById('lastName').value = '';
    document.getElementById('firstName').value = '';
    document.getElementById('middleName').value = '';
    document.getElementById('birthDate').value = '';
    document.getElementById('hireDate').value = new Date().toISOString().slice(0,10);
    document.getElementById('passportSeries').value = '';
    document.getElementById('passportNumber').value = '';
    document.getElementById('phone').value = '';
    document.getElementById('email').value = '';
    document.getElementById('city').value = '';
    document.getElementById('street').value = '';
    document.getElementById('house').value = '';
    document.getElementById('apartment').value = '';
    document.getElementById('postalCode').value = '';
    document.getElementById('department').value = '';
    document.getElementById('position').innerHTML = '<option value="">Выберите должность</option>';
    document.getElementById('salary').value = '';
    
    document.getElementById('employeeModal').classList.add('active');
};

document.getElementById('saveEmployeeBtn').onclick = () => {
    alert('Функция сохранения будет добавлена позже');
};

document.getElementById('confirmFireBtn').onclick = () => {
    alert('Функция увольнения будет добавлена позже');
    document.getElementById('fireConfirmModal').classList.remove('active');
    currentFireId = null;
};

document.getElementById('cancelFireBtn').onclick = () => {
    document.getElementById('fireConfirmModal').classList.remove('active');
    currentFireId = null;
};

document.getElementById('profileBtn').onclick = () => {
    document.getElementById('profileModal').classList.add('active');
};

document.getElementById('closeProfileBtn').onclick = () => {
    document.getElementById('profileModal').classList.remove('active');
};

document.getElementById('saveProfileBtn').onclick = () => {
    alert('Изменения сохранены');
    document.getElementById('profileModal').classList.remove('active');
};

document.getElementById('closeEmployeeBtn').onclick = () => {
    document.getElementById('employeeModal').classList.remove('active');
};

document.getElementById('logoutBtn').onclick = () => {
    if (confirm('Выйти?')) {
        window.location.href = '../logout.php';
    }
};

document.getElementById('profileModal').onclick = (e) => {
    if (e.target === document.getElementById('profileModal')) {
        document.getElementById('profileModal').classList.remove('active');
    }
};

document.getElementById('employeeModal').onclick = (e) => {
    if (e.target === document.getElementById('employeeModal')) {
        document.getElementById('employeeModal').classList.remove('active');
    }
};

document.getElementById('fireConfirmModal').onclick = (e) => {
    if (e.target === document.getElementById('fireConfirmModal')) {
        document.getElementById('fireConfirmModal').classList.remove('active');
    }
};

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

document.getElementById('phone').addEventListener('input', phoneMask);
document.getElementById('passportSeries').addEventListener('input', digitsOnly);
document.getElementById('passportNumber').addEventListener('input', digitsOnly);
document.getElementById('postalCode').addEventListener('input', digitsOnly);

loadUserData();
loadDepartments();
renderFilters();
renderCards();
updateGreeting();
setInterval(updateGreeting, 3600000);