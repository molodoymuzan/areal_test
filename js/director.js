let currentTab = 'employees';
let currentEditId = null;
let currentHrEditId = null;
let currentDeptEditId = null;
let currentPosEditId = null;
let currentFireId = null;
let deleteCallback = null;

function showTab(tab) {
    currentTab = tab;
    
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    
    if (tab === 'employees') {
        document.getElementById('tabEmployees')?.classList.add('active');
        document.getElementById('filtersSection').style.display = 'flex';
        document.getElementById('addBtn').style.display = 'inline-flex';
        document.getElementById('addBtnText').innerText = 'Добавить сотрудника';
        document.getElementById('structurePanel').style.display = 'none';
        document.getElementById('cardsGrid').style.display = 'grid';
        renderFilters();
        renderCards();
    } else if (tab === 'hr') {
        document.getElementById('tabHr')?.classList.add('active');
        document.getElementById('filtersSection').style.display = 'flex';
        document.getElementById('addBtn').style.display = 'inline-flex';
        document.getElementById('addBtnText').innerText = 'Добавить HR';
        document.getElementById('structurePanel').style.display = 'none';
        document.getElementById('cardsGrid').style.display = 'grid';
        renderHrFilters();
        renderHrCards();
    } else {
        document.getElementById('tabStructure')?.classList.add('active');
        document.getElementById('filtersSection').style.display = 'none';
        document.getElementById('addBtn').style.display = 'none';
        document.getElementById('structurePanel').style.display = 'block';
        document.getElementById('cardsGrid').style.display = 'none';
        renderStructure();
    }
}

document.getElementById('tabEmployees')?.addEventListener('click', () => showTab('employees'));
document.getElementById('tabHr')?.addEventListener('click', () => showTab('hr'));
document.getElementById('tabStructure')?.addEventListener('click', () => showTab('structure'));

function renderFilters() {
    fetch('../api/structure.php')
        .then(res => res.json())
        .then(data => {
            let deptOptions = '<option value="all">Все отделы</option>';
            data.departments.forEach(d => {
                deptOptions += `<option value="${d.id}">${d.name}</option>`;
            });
            
            const employeePositions = data.positions.filter(p => p.department_id != 3);
            
            let posOptions = '<option value="all">Все должности</option>';
            employeePositions.forEach(p => {
                posOptions += `<option value="${p.id}">${p.name}</option>`;
            });
            
            const filtersEl = document.getElementById('filtersSection');
            if (filtersEl) {
                filtersEl.innerHTML = `
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
            }
            
            document.getElementById('resetBtn')?.addEventListener('click', resetFilters);
            document.getElementById('searchInput')?.addEventListener('input', renderCards);
            document.getElementById('departmentFilter')?.addEventListener('change', renderCards);
            document.getElementById('positionFilter')?.addEventListener('change', renderCards);
        });
}

function renderHrFilters() {
    fetch('../api/structure.php')
        .then(res => res.json())
        .then(data => {
            const hrPositions = data.positions.filter(p => p.department_id == 3);
            
            let posOptions = '<option value="all">Все должности</option>';
            hrPositions.forEach(p => {
                posOptions += `<option value="${p.id}">${p.name}</option>`;
            });
            
            const filtersEl = document.getElementById('filtersSection');
            if (filtersEl) {
                filtersEl.innerHTML = `
                    <div class="filter-item">
                        <label>Статус</label>
                        <select id="statusFilter">
                            <option value="all">Все</option>
                            <option value="active">Работает</option>
                            <option value="dismissed">Уволен</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label>Должность</label>
                        <select id="hrPositionFilter">${posOptions}</select>
                    </div>
                    <div class="filter-item">
                        <label>Поиск</label>
                        <input type="text" id="searchInput" placeholder="ФИО...">
                    </div>
                    <button class="btn-reset" id="resetBtn">Сбросить</button>
                `;
            }
            
            document.getElementById('resetBtn')?.addEventListener('click', resetHrFilters);
            document.getElementById('searchInput')?.addEventListener('input', renderHrCards);
            document.getElementById('statusFilter')?.addEventListener('change', renderHrCards);
            document.getElementById('hrPositionFilter')?.addEventListener('change', renderHrCards);
        });
}

function resetFilters() {
    const deptFilter = document.getElementById('departmentFilter');
    const posFilter = document.getElementById('positionFilter');
    const searchInput = document.getElementById('searchInput');
    
    if (deptFilter) deptFilter.value = 'all';
    if (posFilter) posFilter.value = 'all';
    if (searchInput) searchInput.value = '';
    
    renderCards();
}

function resetHrFilters() {
    const statusFilter = document.getElementById('statusFilter');
    const positionFilter = document.getElementById('hrPositionFilter');
    const searchInput = document.getElementById('searchInput');
    
    if (statusFilter) statusFilter.value = 'all';
    if (positionFilter) positionFilter.value = 'all';
    if (searchInput) searchInput.value = '';
    
    renderHrCards();
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
                
                const addressText = formatAddress(e);
                
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

function renderHrCards() {
    const statusFilter = document.getElementById('statusFilter')?.value || 'all';
    const positionFilter = document.getElementById('hrPositionFilter')?.value || 'all';
    const search = document.getElementById('searchInput')?.value || '';
    
    fetch(`../api/hr.php?status=${statusFilter}&position=${positionFilter}&search=${search}`)
        .then(res => res.json())
        .then(hr => {
            let html = '';
            hr.forEach(e => {
                const initials = getInitials(e.full_name);
                const statusClass = e.status_id == 2 ? 'dismissed' : '';
                const statusText = e.status_id == 2 ? 'УВОЛЕН' : 'РАБОТАЕТ';
                const cardClass = e.status_id == 2 ? 'card dismissed' : 'card';
                
                const addressText = formatAddress(e);
                
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
                                <span class="position-tag">${e.position_name}</span>
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
                            <button class="card-btn edit" onclick="editHr(${e.id})">✎</button>
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
    Promise.all([
        fetch('../api/employees.php').then(res => res.json()),
        fetch('../api/hr.php').then(res => res.json())
    ]).then(([employees, hr]) => {
        const total = employees.length + hr.length;
        const active = employees.filter(e => e.status_id == 1).length + hr.filter(h => h.status_id == 1).length;
        const dismissed = employees.filter(e => e.status_id == 2).length + hr.filter(h => h.status_id == 2).length;
        
        document.getElementById('totalCount').innerText = total;
        document.getElementById('activeCount').innerText = active;
        document.getElementById('dismissedCount').innerText = dismissed;
    });
}

function renderStructure() {
    fetch('../api/structure.php')
        .then(res => res.json())
        .then(data => {
            renderDepartments(data.departments);
            renderPositions(data.positions);
            updateDepartmentFilter();
        });
}

function renderDepartments(departments) {
    const deptList = document.getElementById('departmentsList');
    if (!deptList) return;
    
    if (departments.length === 0) {
        deptList.innerHTML = '<div class="empty-message">Нет отделов. Создайте первый отдел</div>';
        return;
    }
    
    deptList.innerHTML = departments.map(dept => `
        <div class="structure-item" data-id="${dept.id}">
            <div class="structure-item-info">
                <div class="structure-item-name">${dept.name}</div>
                <div class="structure-item-meta">
                    Сотрудников: ${dept.employees_count || 0}
                </div>
            </div>
            <div class="structure-item-actions">
                <button class="structure-btn edit" onclick="editDepartment(${dept.id})" title="Редактировать">
                    <span class="icon-edit">✎</span>
                </button>
                <button class="structure-btn delete" onclick="confirmDeleteDepartment(${dept.id})" title="Удалить">
                    <span class="icon-delete">✕</span>
                </button>
            </div>
        </div>
    `).join('');
}

function renderPositions(positions) {
    const filterDeptId = document.getElementById('departmentFilterForPositions')?.value;
    const posList = document.getElementById('positionsList');
    if (!posList) return;
    
    let filtered = positions;
    if (filterDeptId) {
        filtered = positions.filter(p => p.department_id == filterDeptId);
    }
    
    if (filtered.length === 0) {
        posList.innerHTML = '<div class="empty-message">Нет должностей</div>';
        return;
    }
    
    posList.innerHTML = filtered.map(pos => `
        <div class="structure-item" data-id="${pos.id}">
            <div class="structure-item-info">
                <div class="structure-item-name">${pos.name}</div>
                <div class="structure-item-meta">${pos.department_name}</div>
            </div>
            <div class="structure-item-actions">
                <button class="structure-btn edit" onclick="editPosition(${pos.id})" title="Редактировать">
                    <span class="icon-edit">✎</span>
                </button>
                <button class="structure-btn delete" onclick="confirmDeletePosition(${pos.id})" title="Удалить">
                    <span class="icon-delete">✕</span>
                </button>
            </div>
        </div>
    `).join('');
}

function updateDepartmentFilter() {
    const filterSelect = document.getElementById('departmentFilterForPositions');
    if (!filterSelect) return;
    
    fetch('../api/structure.php')
        .then(res => res.json())
        .then(data => {
            let options = '<option value="">Все отделы</option>';
            data.departments.forEach(d => {
                options += `<option value="${d.id}">${d.name}</option>`;
            });
            
            let currentValue = filterSelect.value;
            filterSelect.innerHTML = options;
            if (currentValue) filterSelect.value = currentValue;
        });
}

document.getElementById('departmentFilterForPositions')?.addEventListener('change', renderStructure);

document.getElementById('department')?.addEventListener('change', function() {
    updatePositionSelect(this.value, 'position');
});

window.editEmployee = (id) => {
    fetch(`../api/employee.php?id=${id}`)
        .then(res => res.json())
        .then(emp => {
            if (emp.status_id == 2) return;
            
            currentEditId = id;
            document.getElementById('employeeModalTitle').innerText = 'Редактировать сотрудника';
            
            const fields = {
                lastName: document.getElementById('lastName'),
                firstName: document.getElementById('firstName'),
                middleName: document.getElementById('middleName'),
                birthDate: document.getElementById('birthDate'),
                hireDate: document.getElementById('hireDate'),
                passportSeries: document.getElementById('passportSeries'),
                passportNumber: document.getElementById('passportNumber'),
                phone: document.getElementById('phone'),
                email: document.getElementById('email'),
                city: document.getElementById('city'),
                street: document.getElementById('street'),
                house: document.getElementById('house'),
                apartment: document.getElementById('apartment'),
                postalCode: document.getElementById('postalCode'),
                salary: document.getElementById('salary'),
                department: document.getElementById('department'),
                position: document.getElementById('position')
            };
            
            if (fields.lastName) fields.lastName.value = emp.last_name || '';
            if (fields.firstName) fields.firstName.value = emp.first_name || '';
            if (fields.middleName) fields.middleName.value = emp.middle_name || '';
            if (fields.birthDate) fields.birthDate.value = emp.birth_date;
            if (fields.hireDate) fields.hireDate.value = emp.hire_date;
            if (fields.passportSeries) fields.passportSeries.value = emp.passport_series || '';
            if (fields.passportNumber) fields.passportNumber.value = emp.passport_number || '';
            if (fields.phone) fields.phone.value = emp.phone || '';
            if (fields.email) fields.email.value = emp.email || '';
            if (fields.city) fields.city.value = emp.city || '';
            if (fields.street) fields.street.value = emp.street || '';
            if (fields.house) fields.house.value = emp.house || '';
            if (fields.apartment) fields.apartment.value = emp.apartment || '';
            if (fields.postalCode) fields.postalCode.value = emp.postal_code || '';
            if (fields.salary) fields.salary.value = emp.salary;
            if (fields.department) fields.department.value = emp.department_id;
            
            setTimeout(() => {
                if (fields.position) fields.position.value = emp.position_id;
            }, 100);
            
            document.getElementById('employeeModal')?.classList.add('active');
        });
};

window.editHr = (id) => {
    fetch(`../api/employee.php?id=${id}`)
        .then(res => res.json())
        .then(hr => {
            currentHrEditId = id;
            document.getElementById('hrModalTitle').innerText = 'Редактировать HR';
            
            const fields = {
                lastName: document.getElementById('hrLastName'),
                firstName: document.getElementById('hrFirstName'),
                middleName: document.getElementById('hrMiddleName'),
                email: document.getElementById('hrEmail'),
                emailConfirm: document.getElementById('hrEmailConfirm'),
                phone: document.getElementById('hrPhone'),
                birthDate: document.getElementById('hrBirthDate'),
                hireDate: document.getElementById('hrHireDate'),
                passportSeries: document.getElementById('hrPassportSeries'),
                passportNumber: document.getElementById('hrPassportNumber'),
                city: document.getElementById('hrCity'),
                street: document.getElementById('hrStreet'),
                house: document.getElementById('hrHouse'),
                apartment: document.getElementById('hrApartment'),
                postalCode: document.getElementById('hrPostalCode'),
                salary: document.getElementById('hrSalary'),
                department: document.getElementById('hrDepartment'),
                position: document.getElementById('hrPosition')
            };
            
            if (fields.lastName) fields.lastName.value = hr.last_name || '';
            if (fields.firstName) fields.firstName.value = hr.first_name || '';
            if (fields.middleName) fields.middleName.value = hr.middle_name || '';
            if (fields.email) fields.email.value = hr.email || '';
            if (fields.emailConfirm) fields.emailConfirm.value = hr.email || '';
            if (fields.phone) fields.phone.value = hr.phone || '';
            if (fields.birthDate) fields.birthDate.value = hr.birth_date;
            if (fields.hireDate) fields.hireDate.value = hr.hire_date;
            if (fields.passportSeries) fields.passportSeries.value = hr.passport_series || '';
            if (fields.passportNumber) fields.passportNumber.value = hr.passport_number || '';
            if (fields.city) fields.city.value = hr.city || '';
            if (fields.street) fields.street.value = hr.street || '';
            if (fields.house) fields.house.value = hr.house || '';
            if (fields.apartment) fields.apartment.value = hr.apartment || '';
            if (fields.postalCode) fields.postalCode.value = hr.postal_code || '';
            if (fields.salary) fields.salary.value = hr.salary;
            if (fields.department) fields.department.value = hr.department_id;
            
            setTimeout(() => {
                if (fields.position) fields.position.value = hr.position_id;
            }, 100);
            
            document.getElementById('hrPasswordBlock').style.display = 'none';
            document.getElementById('hrModal')?.classList.add('active');
        });
};

window.editDepartment = (id) => {
    fetch('../api/structure.php')
        .then(res => res.json())
        .then(data => {
            const dept = data.departments.find(d => d.id == id);
            if (!dept) return;
            
            currentDeptEditId = id;
            document.getElementById('departmentModalTitle').innerText = 'Редактировать отдел';
            document.getElementById('departmentName').value = dept.name;
            document.getElementById('departmentModal')?.classList.add('active');
        });
};

window.editPosition = (id) => {
    fetch('../api/structure.php')
        .then(res => res.json())
        .then(data => {
            const pos = data.positions.find(p => p.id == id);
            if (!pos) return;
            
            currentPosEditId = id;
            document.getElementById('positionModalTitle').innerText = 'Редактировать должность';
            document.getElementById('positionName').value = pos.name;
            
            const deptSelect = document.getElementById('positionDepartmentId');
            if (deptSelect) {
                let options = '<option value="">Выберите отдел</option>';
                data.departments.forEach(d => {
                    options += `<option value="${d.id}" ${d.id == pos.department_id ? 'selected' : ''}>${d.name}</option>`;
                });
                deptSelect.innerHTML = options;
            }
            
            document.getElementById('positionModal')?.classList.add('active');
        });
};

window.confirmDeleteDepartment = (id) => {
    fetch('../api/structure.php')
        .then(res => res.json())
        .then(data => {
            const dept = data.departments.find(d => d.id == id);
            if (!dept) return;
            
            document.getElementById('confirmMessage').innerText = `Удалить отдел "${dept.name}"?`;
            
            deleteCallback = () => {
                fetch('../api/departments.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                })
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        renderStructure();
                    } else {
                        showNotification(response.error || 'Ошибка при удалении', 'error');
                    }
                    document.getElementById('confirmModal').classList.remove('active');
                });
            };
            
            document.getElementById('confirmModal').classList.add('active');
        });
};

window.confirmDeletePosition = (id) => {
    fetch('../api/structure.php')
        .then(res => res.json())
        .then(data => {
            const pos = data.positions.find(p => p.id == id);
            if (!pos) return;
            
            document.getElementById('confirmMessage').innerText = `Удалить должность "${pos.name}"?`;
            
            deleteCallback = () => {
                fetch('../api/positions.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                })
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        renderStructure();
                    } else {
                        showNotification(response.error || 'Ошибка при удалении', 'error');
                    }
                    document.getElementById('confirmModal').classList.remove('active');
                });
            };
            
            document.getElementById('confirmModal').classList.add('active');
        });
};

window.confirmFire = (id, name) => {
    currentFireId = id;
    document.getElementById('fireConfirmMessage').innerText = `Уволить сотрудника ${name}?`;
    document.getElementById('fireConfirmModal')?.classList.add('active');
};

document.getElementById('addBtn')?.addEventListener('click', () => {
    if (currentTab === 'employees') {
        currentEditId = null;
        document.getElementById('employeeModalTitle').innerText = 'Новый сотрудник';
        
        const fields = {
            lastName: document.getElementById('lastName'),
            firstName: document.getElementById('firstName'),
            middleName: document.getElementById('middleName'),
            birthDate: document.getElementById('birthDate'),
            hireDate: document.getElementById('hireDate'),
            passportSeries: document.getElementById('passportSeries'),
            passportNumber: document.getElementById('passportNumber'),
            phone: document.getElementById('phone'),
            email: document.getElementById('email'),
            city: document.getElementById('city'),
            street: document.getElementById('street'),
            house: document.getElementById('house'),
            apartment: document.getElementById('apartment'),
            postalCode: document.getElementById('postalCode'),
            salary: document.getElementById('salary'),
            department: document.getElementById('department'),
            position: document.getElementById('position')
        };
        
        if (fields.lastName) fields.lastName.value = '';
        if (fields.firstName) fields.firstName.value = '';
        if (fields.middleName) fields.middleName.value = '';
        if (fields.birthDate) fields.birthDate.value = '';
        if (fields.hireDate) fields.hireDate.value = new Date().toISOString().slice(0,10);
        if (fields.passportSeries) fields.passportSeries.value = '';
        if (fields.passportNumber) fields.passportNumber.value = '';
        if (fields.phone) fields.phone.value = '';
        if (fields.email) fields.email.value = '';
        if (fields.city) fields.city.value = '';
        if (fields.street) fields.street.value = '';
        if (fields.house) fields.house.value = '';
        if (fields.apartment) fields.apartment.value = '';
        if (fields.postalCode) fields.postalCode.value = '';
        if (fields.department) fields.department.value = '';
        if (fields.position && fields.position.id !== 'hrPosition') {
            fields.position.innerHTML = '<option value="">Выберите должность</option>';
        }
        if (fields.salary) fields.salary.value = '';
        
        document.getElementById('employeeModal')?.classList.add('active');
    } else if (currentTab === 'hr') {
        currentHrEditId = null;
        document.getElementById('hrModalTitle').innerText = 'Новый HR-сотрудник';
        
        const fields = {
            lastName: document.getElementById('hrLastName'),
            firstName: document.getElementById('hrFirstName'),
            middleName: document.getElementById('hrMiddleName'),
            email: document.getElementById('hrEmail'),
            emailConfirm: document.getElementById('hrEmailConfirm'),
            phone: document.getElementById('hrPhone'),
            birthDate: document.getElementById('hrBirthDate'),
            hireDate: document.getElementById('hrHireDate'),
            passportSeries: document.getElementById('hrPassportSeries'),
            passportNumber: document.getElementById('hrPassportNumber'),
            city: document.getElementById('hrCity'),
            street: document.getElementById('hrStreet'),
            house: document.getElementById('hrHouse'),
            apartment: document.getElementById('hrApartment'),
            postalCode: document.getElementById('hrPostalCode'),
            salary: document.getElementById('hrSalary'),
            department: document.getElementById('hrDepartment'),
            position: document.getElementById('hrPosition')
        };
        
        if (fields.lastName) fields.lastName.value = '';
        if (fields.firstName) fields.firstName.value = '';
        if (fields.middleName) fields.middleName.value = '';
        if (fields.email) fields.email.value = '';
        if (fields.emailConfirm) fields.emailConfirm.value = '';
        if (fields.phone) fields.phone.value = '';
        if (fields.birthDate) fields.birthDate.value = '';
        if (fields.hireDate) fields.hireDate.value = new Date().toISOString().slice(0,10);
        if (fields.passportSeries) fields.passportSeries.value = '';
        if (fields.passportNumber) fields.passportNumber.value = '';
        if (fields.city) fields.city.value = '';
        if (fields.street) fields.street.value = '';
        if (fields.house) fields.house.value = '';
        if (fields.apartment) fields.apartment.value = '';
        if (fields.postalCode) fields.postalCode.value = '';
        if (fields.department) fields.department.value = '';
        if (fields.salary) fields.salary.value = '';
        
        document.getElementById('hrPasswordBlock').style.display = 'none';
        document.getElementById('hrModal')?.classList.add('active');
    }
});

document.getElementById('addDepartmentBtn')?.addEventListener('click', () => {
    currentDeptEditId = null;
    document.getElementById('departmentModalTitle').innerText = 'Новый отдел';
    document.getElementById('departmentName').value = '';
    document.getElementById('departmentModal')?.classList.add('active');
});

document.getElementById('addPositionBtn')?.addEventListener('click', () => {
    currentPosEditId = null;
    document.getElementById('positionModalTitle').innerText = 'Новая должность';
    document.getElementById('positionName').value = '';
    
    fetch('../api/structure.php')
        .then(res => res.json())
        .then(data => {
            const deptSelect = document.getElementById('positionDepartmentId');
            if (deptSelect) {
                let options = '<option value="">Выберите отдел</option>';
                data.departments.forEach(d => {
                    options += `<option value="${d.id}">${d.name}</option>`;
                });
                deptSelect.innerHTML = options;
            }
        });
    
    document.getElementById('positionModal')?.classList.add('active');
});

document.getElementById('profileBtn')?.addEventListener('click', () => {
    fetch('../api/profile.php')
        .then(res => res.json())
        .then(data => {
            loadProfileData(data);
            document.getElementById('profileModal').classList.add('active');
        })
        .catch(error => {
            console.error('Ошибка загрузки профиля:', error);
            showNotification('Ошибка при загрузке данных профиля', 'error');
        });
});

document.getElementById('closeProfileBtn')?.addEventListener('click', () => {
    document.getElementById('profileModal')?.classList.remove('active');
});

document.getElementById('closeEmployeeBtn')?.addEventListener('click', () => {
    document.getElementById('employeeModal')?.classList.remove('active');
});

document.getElementById('closeHrModal')?.addEventListener('click', () => {
    document.getElementById('hrModal')?.classList.remove('active');
    currentHrEditId = null;
});

document.getElementById('closeDepartmentModal')?.addEventListener('click', () => {
    document.getElementById('departmentModal')?.classList.remove('active');
});

document.getElementById('closePositionModal')?.addEventListener('click', () => {
    document.getElementById('positionModal')?.classList.remove('active');
});

document.getElementById('cancelFireBtn')?.addEventListener('click', () => {
    document.getElementById('fireConfirmModal')?.classList.remove('active');
    currentFireId = null;
});

document.getElementById('cancelConfirmBtn')?.addEventListener('click', () => {
    document.getElementById('confirmModal')?.classList.remove('active');
    deleteCallback = null;
});

document.getElementById('logoutBtn')?.addEventListener('click', () => {
    if (confirm('Выйти?')) {
        window.location.href = '../logout.php';
    }
});

document.getElementById('saveEmployeeBtn').onclick = () => {
    saveEmployee(currentEditId, 3).then(response => {
        if (response && response.success) {
            document.getElementById('employeeModal').classList.remove('active');
            renderCards();
        } else {
            showNotification('Ошибка при сохранении', 'error');
        }
    }).catch(() => {});
};

document.getElementById('saveHrBtn')?.addEventListener('click', () => {
    if (document.getElementById('saveHrBtn').textContent === 'Закрыть') {
        document.getElementById('hrModal').classList.remove('active');
        document.getElementById('saveHrBtn').textContent = 'Сохранить';
        document.getElementById('closeHrModal').style.display = 'inline-block';
        document.getElementById('hrPasswordBlock').style.display = 'none';
        currentHrEditId = null;
        renderHrCards();
        return;
    }
    
    if (!document.getElementById('hrLastName').value) {
        showNotification('Введите фамилию', 'error');
        return;
    }
    if (!document.getElementById('hrFirstName').value) {
        showNotification('Введите имя', 'error');
        return;
    }
    if (!document.getElementById('hrEmail').value) {
        showNotification('Введите email', 'error');
        return;
    }
    if (!document.getElementById('hrPosition').value) {
        showNotification('Выберите должность', 'error');
        return;
    }
    
    const positionId = parseInt(document.getElementById('hrPosition').value);
    let roleId = 2;
    
    if (positionId === 7) {
        roleId = 1;
    }
    
    const url = currentHrEditId ? '../api/update_employee.php' : '../api/create_employee.php';
    const data = {
        id: currentHrEditId,
        lastName: document.getElementById('hrLastName').value,
        firstName: document.getElementById('hrFirstName').value,
        middleName: document.getElementById('hrMiddleName').value,
        birthDate: document.getElementById('hrBirthDate').value,
        hireDate: document.getElementById('hrHireDate').value,
        passportSeries: document.getElementById('hrPassportSeries').value,
        passportNumber: document.getElementById('hrPassportNumber').value,
        phone: document.getElementById('hrPhone').value,
        email: document.getElementById('hrEmail').value,
        city: document.getElementById('hrCity').value,
        street: document.getElementById('hrStreet').value,
        house: document.getElementById('hrHouse').value,
        apartment: document.getElementById('hrApartment').value,
        postalCode: document.getElementById('hrPostalCode').value,
        departmentId: 3,
        positionId: positionId,
        salary: document.getElementById('hrSalary').value,
        roleId: roleId
    };

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            if (response.tempPassword) {
                document.getElementById('hrTempPassword').textContent = response.tempPassword;
                document.getElementById('hrPasswordBlock').style.display = 'block';
                document.getElementById('saveHrBtn').textContent = 'Закрыть';
                document.getElementById('closeHrModal').style.display = 'none';
                
                document.querySelectorAll('#hrModal input, #hrModal select').forEach(el => {
                    el.disabled = true;
                });
            } else {
                document.getElementById('hrModal').classList.remove('active');
                currentHrEditId = null;
                renderHrCards();
            }
        } else {
            showNotification('Ошибка при сохранении', 'error');
        }
    });
});

document.getElementById('saveDepartmentBtn')?.addEventListener('click', () => {
    const name = document.getElementById('departmentName').value;
    if (!name) {
        showNotification('Введите название отдела', 'error');
        return;
    }
    
    const url = currentDeptEditId ? '../api/departments.php?action=update' : '../api/departments.php?action=create';
    const data = {
        id: currentDeptEditId,
        name: name
    };

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            document.getElementById('departmentModal').classList.remove('active');
            renderStructure();
            loadDepartments('department');
            loadDepartments('hrDepartment');
            if (currentTab === 'employees') {
                renderFilters();
            }
        } else {
            showNotification('Ошибка при сохранении', 'error');
        }
    });
});

document.getElementById('savePositionBtn')?.addEventListener('click', () => {
    const name = document.getElementById('positionName').value;
    const departmentId = document.getElementById('positionDepartmentId').value;
    
    if (!name) {
        showNotification('Введите название должности', 'error');
        return;
    }
    if (!departmentId) {
        showNotification('Выберите отдел', 'error');
        return;
    }
    
    const url = currentPosEditId ? '../api/positions.php?action=update' : '../api/positions.php?action=create';
    const data = {
        id: currentPosEditId,
        name: name,
        department_id: departmentId
    };

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            document.getElementById('positionModal').classList.remove('active');
            renderStructure();
            if (currentTab === 'employees') {
                renderFilters();
            }
        } else {
            showNotification('Ошибка при сохранении', 'error');
        }
    });
});

document.getElementById('saveProfileBtn')?.addEventListener('click', handleProfileSave);

document.getElementById('confirmFireBtn').onclick = () => {
    if (!currentFireId) return;
    fireEmployee(currentFireId).then(response => {
        if (response.success) {
            document.getElementById('fireConfirmModal').classList.remove('active');
            currentFireId = null;
            if (currentTab === 'employees') {
                renderCards();
            } else {
                renderHrCards();
            }
        }
    });
};

document.getElementById('confirmDeleteBtn')?.addEventListener('click', () => {
    if (deleteCallback) {
        deleteCallback();
        deleteCallback = null;
    }
});

document.getElementById('phone')?.addEventListener('input', phoneMask);
document.getElementById('hrPhone')?.addEventListener('input', phoneMask);
document.getElementById('postalCode')?.addEventListener('input', digitsOnly);
document.getElementById('hrPostalCode')?.addEventListener('input', digitsOnly);
document.getElementById('passportSeries')?.addEventListener('input', digitsOnly);
document.getElementById('passportNumber')?.addEventListener('input', digitsOnly);
document.getElementById('hrPassportSeries')?.addEventListener('input', digitsOnly);
document.getElementById('hrPassportNumber')?.addEventListener('input', digitsOnly);

document.getElementById('lastName')?.addEventListener('input', formatNameInput);
document.getElementById('firstName')?.addEventListener('input', formatNameInput);
document.getElementById('middleName')?.addEventListener('input', formatNameInput);
document.getElementById('hrLastName')?.addEventListener('input', formatNameInput);
document.getElementById('hrFirstName')?.addEventListener('input', formatNameInput);
document.getElementById('hrMiddleName')?.addEventListener('input', formatNameInput);

loadUserData();
loadDepartments('department', true);
showTab('employees');