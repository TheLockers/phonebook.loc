// Переменная для отслеживания активного столбца фильтра
let activeFilterColumn = null;

// Переменная для хранения текущего редактируемого сотрудника
let currentEmployee = null;

// Позиция скролла до блокировки (для восстановления)
let scrollPosition = 0;

// Текущий пользователь и его права
let currentUserId = null;
let isUserAdmin = false;

// Получаем данные текущего пользователя при загрузке
document.addEventListener('DOMContentLoaded', function () {
    console.log('Скрипт загружен');

    // Получаем данные текущего пользователя
    const userIdElement = document.getElementById('currentUserId');
    const isAdminElement = document.getElementById('isUserAdmin');

    if (userIdElement) {
        currentUserId = parseInt(userIdElement.value);
        console.log('Текущий пользователь ID:', currentUserId);
    }

    if (isAdminElement) {
        isUserAdmin = isAdminElement.value === '1';
        console.log('Права администратора:', isUserAdmin);
    }

    // Проверяем наличие админ-панели
    const panel = document.getElementById('adminPanelContainer');
    if (!panel) {
        console.warn('Админ-панель не найдена на странице');
    } else {
        console.log('Админ-панель найдена');
    }

    // Проверяем наличие таблицы
    const table = document.getElementById('employees-table');
    if (!table) {
        console.warn('Таблица сотрудников не найдена');
    } else {
        console.log('Таблица сотрудников найдена');

        // Подсвечиваем строку текущего пользователя
        highlightCurrentUserRow();
    }
});

function toggleFilter(headerElement, columnIndex) {
    const filterContainer = headerElement.querySelector('.filter-input-container');

    // Закрываем предыдущий открытый фильтр
    if (activeFilterColumn !== null && activeFilterColumn !== columnIndex) {
        const prevContainer = document.querySelector('.filter-input-container.show');
        if (prevContainer) {
            prevContainer.classList.remove('show');
        }
        // Убираем show у всех th
        document.querySelectorAll('thead th').forEach(th => {
            th.classList.remove('show');
        });
    }

    // Переключаем текущий фильтр
    const isOpening = !filterContainer.classList.contains('show');
    filterContainer.classList.toggle('show');

    if (isOpening) {
        // Добавляем show ко всем th
        document.querySelectorAll('thead th').forEach(th => {
            th.classList.add('show');
        });
        activeFilterColumn = columnIndex;

        // Фокус на инпут при открытии
        setTimeout(() => {
            const inputElement = filterContainer.querySelector('input');
            if (inputElement) {
                inputElement.focus();
                inputElement.select();
            }
        }, 50);
    } else {
        // Убираем show у всех th
        document.querySelectorAll('thead th').forEach(th => {
            th.classList.remove('show');
        });
        activeFilterColumn = null;
    }
}

function filterTable(columnIndex, searchText) {
    const table = document.getElementById('employees-table');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const cell = row.cells[columnIndex];
        const cellText = cell.textContent.toLowerCase();
        const search = searchText.toLowerCase();

        if (cellText.includes(search)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Закрытие фильтра при клике вне его
document.addEventListener('click', function (event) {
    if (!event.target.closest('th')) {
        const openFilters = document.querySelectorAll('.filter-input-container.show');
        openFilters.forEach(filter => filter.classList.remove('show'));
        activeFilterColumn = null;
        document.querySelectorAll('thead th').forEach(th => {
            th.classList.remove('show');
        });
    }
});

/**
 * Открывает админ-панель для редактирования сотрудника
 * @param {number} employeeId - ID сотрудника из базы данных
 */
function adminPanelOpen(employeeId) {
    console.log('Открытие админ-панели для сотрудника ID:', employeeId);

    // ПРОВЕРКА ПРАВ ДОСТУПА
    if (!checkEditPermission(employeeId)) {
        return; // Если нет прав, прекращаем выполнение
    }

    // Находим строку таблицы с сотрудником
    let row = document.querySelector(`tr[data-id="${employeeId}"]`); // используем let вместо const

    if (!row) {
        console.error('Строка с сотрудником не найдена! Ищем вручную...');

        // Альтернативный поиск - перебираем все строки
        const rows = document.querySelectorAll('#employees-table tbody tr');
        for (let i = 0; i < rows.length; i++) {
            const rowId = rows[i].getAttribute('data-id');
            console.log(`Строка ${i}: data-id = ${rowId}`);

            if (rowId == employeeId) {
                row = rows[i];
                break;
            }
        }

        if (!row) {
            console.error('Сотрудник не найден даже при ручном поиске');
            showNotification('Ошибка: сотрудник не найден', 'error');
            return;
        }
    }

    const photoData = row.dataset.photo;
    const imgElement = document.getElementById('employeeImage');

    if (photoData && photoData !== '') {
        // Если фото есть в БД
        imgElement.src = photoData;
        console.log('Фото загружено из БД');
    } else {
        // Если фото нет - ставим заглушку
        imgElement.src = 'image/default.png';
        console.log('Используем фото-заглушку');
    }

    // Собираем данные из строки таблицы
    const cells = row.querySelectorAll('td');

    // Проверяем, что есть все ячейки
    if (cells.length < 11) {
        console.error('Недостаточно ячеек в строке:', cells.length);
        showNotification('Ошибка: неполные данные сотрудника', 'error');
        return;
    }

    // Формируем объект с данными сотрудника
    const employeeData = {
        id: employeeId,
        last_name: cells[0].textContent.trim() || '',
        first_name: cells[1].textContent.trim() || '',
        middle_name: cells[2].textContent.trim() || '',
        position: cells[3].textContent.trim() || '',
        unit_name: cells[4].textContent.trim() || '',
        corps: cells[5].textContent.trim() || '',
        office_number: cells[6].textContent.trim() || '',
        internal_phone_number: cells[7].textContent.trim() || '',
        landline: cells[8].textContent.trim() || '',
        mobile_phone_number: cells[9].textContent.trim() || '',
        email: cells[10].textContent.trim() || '',
        created_at: row.dataset.createdAt || row.dataset.created_at || '—',
        updated_at: row.dataset.updatedAt || row.dataset.updated_at || '—',
        is_admin: row.dataset.isAdmin || row.dataset.is_admin || '0'
    };

    console.log('Данные сотрудника:', employeeData);

    // Заполняем форму данными сотрудника
    fillAdminForm(employeeData);

    // Сохраняем текущего сотрудника
    currentEmployee = employeeData;

    // Показываем панель
    showAdminPanel();

    // Прокручиваем к верху страницы
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // Блокируем прокрутку страницы
    blockPageScroll();

    // Фокусируемся на первом поле
    setTimeout(() => {
        const lastNameInput = document.getElementById('lastNameInput');
        if (lastNameInput) {
            lastNameInput.focus();
        }
    }, 300);
}

/**
 * Проверяет права на редактирование профиля
 * @param {number} employeeId - ID сотрудника для редактирования
 * @returns {boolean} - true если есть права, false если нет
 */
function checkEditPermission(employeeId) {
    const employeeIdNum = parseInt(employeeId);

    // Если пользователь не авторизован
    if (!currentUserId) {
        showNotification('Ошибка: вы не авторизованы', 'error');
        return false;
    }

    // Проверяем права
    if (!isUserAdmin && employeeIdNum !== currentUserId) {
        showNotification('Вы можете редактировать только свой профиль!', 'warning');
        console.log(`Отказ в доступе: пользователь ${currentUserId} пытается редактировать ${employeeId}`);
        return false;
    }

    // Разрешаем редактирование если:
    // 1. Пользователь админ (может редактировать всех)
    // 2. Пользователь редактирует свой профиль
    console.log(`Разрешение на редактирование: пользователь ${currentUserId} редактирует ${employeeId}`);
    return true;
}

function showAdminPanel() {
    const panel = document.getElementById('adminPanelContainer');
    const overlay = document.getElementById('adminPanelOverlay') || createOverlay();

    if (!panel) {
        console.error('Элемент adminPanelContainer не найден!');
        return;
    }

    // Показываем оверлей
    overlay.classList.add('show');

    // Показываем панель
    panel.classList.add('show');

    // Настраиваем поля в зависимости от прав
    setupAdminPanelFields();

    console.log('Админ-панель показана');
}

/**
 * Настраивает видимость полей в админ-панели в зависимости от прав
 */
function setupAdminPanelFields() {
    const employeeId = document.getElementById('employeeId').value;
    const employeeIdNum = parseInt(employeeId);

    // Поле для пароля (только при редактировании своего профиля)
    const passwordBox = document.getElementById('passwordBox');
    if (passwordBox) {
        if (employeeIdNum === currentUserId) {
            passwordBox.style.display = 'block';
        } else {
            passwordBox.style.display = 'none';
        }
    }

    // Поле админ-статуса (только для админов и при редактировании других пользователей)
    const adminStatusBox = document.querySelector('.admin-status');
    if (adminStatusBox) {
        if (isUserAdmin && employeeIdNum !== currentUserId) {
            adminStatusBox.style.display = 'block';
        } else {
            adminStatusBox.style.display = 'none';
        }
    }
}

// Создает оверлей если его нет
function createOverlay() {
    const overlay = document.createElement('div');
    overlay.id = 'adminPanelOverlay';
    overlay.className = 'admin-panel-overlay';
    overlay.onclick = closePanelAdmin;
    document.body.appendChild(overlay);

    // Добавляем стили для оверлея
    if (!document.querySelector('#adminPanelOverlayStyles')) {
        const style = document.createElement('style');
        style.id = 'adminPanelOverlayStyles';
        style.textContent = `
            .admin-panel-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.7);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s;
            }
            
            .admin-panel-overlay.show {
                opacity: 1;
                visibility: visible;
            }
        `;
        document.head.appendChild(style);
    }

    return overlay;
}

function fillAdminForm(employeeData) {
    // Скрытое поле с ID
    const employeeIdInput = document.getElementById('employeeId');
    if (employeeIdInput) {
        employeeIdInput.value = employeeData.id;
    }

    // Основные поля
    const fields = {
        'lastNameInput': 'last_name',
        'firstNameInput': 'first_name',
        'middleNameInput': 'middle_name',
        'positionInput': 'position',
        'unitInput': 'unit_name',
        'corpsInput': 'corps',
        'officeNumberInput': 'office_number',
        'internalPhoneInput': 'internal_phone_number',
        'landlineInput': 'landline',
        'mobilePhoneInput': 'mobile_phone_number',
        'emailInput': 'email'
    };

    // Заполняем все поля
    for (const [inputId, dataKey] of Object.entries(fields)) {
        const input = document.getElementById(inputId);
        if (input) {
            input.value = employeeData[dataKey] || '';
        }
    }

    // Даты
    const createdAt = document.getElementById('createdAt');
    if (createdAt) {
        createdAt.textContent = employeeData.created_at;
    }

    const updatedAt = document.getElementById('updatedAt');
    if (updatedAt) {
        updatedAt.textContent = employeeData.updated_at;
    }

    // Админ-статус
    const isAdminCheckbox = document.getElementById('isAdminCheckbox');
    if (isAdminCheckbox) {
        isAdminCheckbox.checked = employeeData.is_admin === '1';
    }

    console.log('Форма заполнена данными сотрудника');
}

// Функция закрытия админ-панели
function closePanelAdmin() {
    const panel = document.getElementById('adminPanelContainer');
    const overlay = document.getElementById('adminPanelOverlay');

    if (panel) {
        panel.classList.remove('show');
    }

    if (overlay) {
        overlay.classList.remove('show');
    }

    // Разблокируем прокрутку
    unblockPageScroll();

    // Сбрасываем форму
    resetAdminForm();
    currentEmployee = null;
}

function blockPageScroll() {
    // Сохраняем позицию скролла
    scrollPosition = window.scrollY || document.documentElement.scrollTop;
    console.log('Сохранили позицию скролла:', scrollPosition);

    // Добавляем класс для блокировки
    document.body.classList.add('no-scroll');
    document.documentElement.classList.add('no-scroll');

    // Прокручиваем к верху
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Функция разблокировки прокрутки
function unblockPageScroll() {
    // Убираем класс блокировки
    document.body.classList.remove('no-scroll');
    document.documentElement.classList.remove('no-scroll');

    // Восстанавливаем позицию скролла
    console.log('Восстанавливаем позицию:', scrollPosition);
    window.scrollTo(0, scrollPosition);
}

function resetAdminForm() {
    document.getElementById('employeeId').value = '';
    document.getElementById('lastNameInput').value = '';
    document.getElementById('firstNameInput').value = '';
    document.getElementById('middleNameInput').value = '';
    document.getElementById('positionInput').value = '';
    document.getElementById('unitInput').value = '';
    document.getElementById('corpsInput').value = '';
    document.getElementById('officeNumberInput').value = '';
    document.getElementById('internalPhoneInput').value = '';
    document.getElementById('landlineInput').value = '';
    document.getElementById('mobilePhoneInput').value = '';
    document.getElementById('emailInput').value = '';
    document.getElementById('createdAt').textContent = '—';
    document.getElementById('updatedAt').textContent = '—';

    // Сбрасываем поля пароля
    const newPasswordInput = document.getElementById('newPasswordInput');
    const confirmPasswordInput = document.getElementById('confirmPasswordInput');
    if (newPasswordInput) newPasswordInput.value = '';
    if (confirmPasswordInput) confirmPasswordInput.value = '';

    // Сбрасываем чекбокс админа
    const isAdminCheckbox = document.getElementById('isAdminCheckbox');
    if (isAdminCheckbox) isAdminCheckbox.checked = false;
}

// Функция сохранения изменений
async function savePanelAdmin() {
    const employeeId = document.getElementById('employeeId').value;

    // ПРОВЕРКА ПРАВ ДОСТУПА ПЕРЕД СОХРАНЕНИЕМ
    if (!checkEditPermission(employeeId)) {
        return;
    }

    // Собираем данные формы
    const formData = {
        id: employeeId,
        last_name: document.getElementById('lastNameInput').value,
        first_name: document.getElementById('firstNameInput').value,
        middle_name: document.getElementById('middleNameInput').value,
        corps: document.getElementById('corpsInput').value,
        office_number: document.getElementById('officeNumberInput').value,
        internal_phone_number: document.getElementById('internalPhoneInput').value,
        landline: document.getElementById('landlineInput').value,
        mobile_phone_number: document.getElementById('mobilePhoneInput').value,
        email: document.getElementById('emailInput').value
    };

    // Определяем действие с фото
    const img = document.getElementById('employeeImage');
    const currentPhotoSrc = img.src;
    const originalPhotoSrc = currentEmployee?.photo || '';

    // Проверяем, было ли фото изменено
    if (currentPhotoSrc.includes('default.png')) {
        // Фото удалено (стоит дефолтное)
        formData.photo_action = 'delete';
    } else if (currentPhotoSrc.startsWith('data:image/') && currentPhotoSrc !== originalPhotoSrc) {
        // Загружено новое фото
        formData.photo_action = 'update';
        formData.photo = currentPhotoSrc;
    } else {
        // Фото не менялось
        formData.photo_action = 'keep';
    }

    // Добавляем пароль если пользователь меняет свой пароль
    const newPassword = document.getElementById('newPasswordInput')?.value;
    const confirmPassword = document.getElementById('confirmPasswordInput')?.value;

    if (newPassword || confirmPassword) {
        // Проверяем, что это свой профиль (дополнительная защита)
        if (parseInt(employeeId) === currentUserId) {
            formData.new_password = newPassword;
            formData.confirm_password = confirmPassword;
        }
    }

    // Добавляем админ-статус если пользователь админ и редактирует не себя
    if (isUserAdmin && parseInt(employeeId) !== currentUserId) {
        const isAdminCheckbox = document.getElementById('isAdminCheckbox');
        if (isAdminCheckbox) {
            formData.is_admin = isAdminCheckbox.checked ? 1 : 0;
        }
    }

    // Валидация
    if (!validateForm(formData)) {
        return;
    }

    try {
        showLoading(true);

        // Отправляем данные
        const response = await fetch('update-employee.php', { // Исправлено на update-employee.php
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        if (response.ok) {
            const result = await response.json();

            if (result.success) {
                // Обновляем строку в таблице
                updateTableRow(formData);

                // Закрываем панель
                closePanelAdmin();

                // Показываем сообщение
                showNotification('Данные сохранены', 'success');

                // Если это был текущий пользователь, обновляем имя в шапке
                if (parseInt(employeeId) === currentUserId) {
                    updateUserHeader(formData);
                }
            } else {
                showNotification('Ошибка: ' + result.message, 'error');
            }
        } else {
            throw new Error('Ошибка сети: ' + response.status);
        }
    } catch (error) {
        console.error('Ошибка:', error);
        showNotification('Ошибка сохранения: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Простая проверка
        if (!file.type.startsWith('image/')) {
            alert('Выберите файл изображения (JPG, PNG, GIF)');
            return;
        }

        // Проверка размера (макс 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Файл слишком большой. Максимум 2MB.');
            return;
        }

        // Читаем файл и конвертируем в base64
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('employeeImage').src = e.target.result;
            showNotification('Фото загружено', 'success');
        };
        reader.readAsDataURL(file);
    }
}

function resetImage() {
    // Устанавливаем дефолтное изображение
    document.getElementById('employeeImage').src = 'image/default.png';
    // Сбрасываем значение файлового инпута
    document.getElementById('imageUpload').value = '';

    // Показываем сообщение
    showNotification('Фото удалено', 'info');
}

function validateForm(data) {
    // Проверка обязательных полей
    if (!data.last_name.trim()) {
        showNotification('Фамилия обязательна для заполнения', 'error');
        return false;
    }
    if (!data.first_name.trim()) {
        showNotification('Имя обязательно для заполнения', 'error');
        return false;
    }

    // Проверка email (если заполнен)
    if (data.email && !isValidEmail(data.email)) {
        showNotification('Введите корректный email адрес', 'error');
        return false;
    }

    // Проверка пароля (если меняется)
    if (data.new_password || data.confirm_password) {
        if (data.new_password !== data.confirm_password) {
            showNotification('Пароли не совпадают', 'error');
            return false;
        }
        if (data.new_password && data.new_password.length < 6) {
            showNotification('Пароль должен быть не менее 6 символов', 'error');
            return false;
        }
    }

    return true;
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Обновляет строку в таблице
 * @param {object} data - Новые данные сотрудника
 */
function updateTableRow(data) {
    const row = document.querySelector(`tr[data-id="${data.id}"]`);
    if (!row) {
        console.error('Не найдена строка для обновления с ID:', data.id);
        return;
    }

    const cells = row.querySelectorAll('td');
    cells[0].textContent = data.last_name;
    cells[1].textContent = data.first_name;
    cells[2].textContent = data.middle_name;
    cells[5].textContent = data.corps;
    cells[6].textContent = data.office_number;
    cells[7].textContent = data.internal_phone_number;
    cells[8].textContent = data.landline;
    cells[9].textContent = data.mobile_phone_number;
    cells[10].textContent = data.email;

    // Обновляем дату изменения
    row.dataset.updatedAt = new Date().toISOString().replace('T', ' ').substring(0, 19);

    // Обновляем фото в data-атрибуте если оно было изменено
    if (data.photo_action === 'update' && data.photo) {
        row.dataset.photo = data.photo;
    } else if (data.photo_action === 'delete') {
        row.dataset.photo = '';
    }
}

/**
 * Обновляет информацию о пользователе в шапке
 * @param {object} data - Новые данные пользователя
 */
function updateUserHeader(data) {
    const userNameElements = document.querySelectorAll('[data-user-name]');
    const fullName = `${data.last_name} ${data.first_name}`;

    userNameElements.forEach(element => {
        if (element.textContent.includes('Вы вошли как:')) {
            element.innerHTML = `Вы вошли как: <strong>${fullName}</strong>`;
        }
    });
}
/**
 * Подсвечивает строку текущего пользователя в таблице
 */
function highlightCurrentUserRow() {
    if (!currentUserId) return;

    const rows = document.querySelectorAll('#employees-table tbody tr');
    rows.forEach(row => {
        const rowId = parseInt(row.getAttribute('data-id'));
        if (rowId === currentUserId) {
            row.classList.add('current-user-row');
            console.log('Строка текущего пользователя подсвечена');
        }
    });
}

/**
 * Показывает индикатор загрузки
 * @param {boolean} show - Показать или скрыть индикатор
 */
function showLoading(show) {
    let loader = document.getElementById('loadingIndicator');

    if (show) {
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'loadingIndicator';
            loader.innerHTML = `
                <div style="
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: rgba(0,0,0,0.7);
                    color: white;
                    padding: 20px 30px;
                    border-radius: 10px;
                    z-index: 10000;
                    font-family: Arial, sans-serif;
                ">
                    <div style="text-align: center;">
                        <div style="
                            width: 40px;
                            height: 40px;
                            border: 4px solid #f3f3f3;
                            border-top: 4px solid #3498db;
                            border-radius: 50%;
                            animation: spin 1s linear infinite;
                            margin: 0 auto 10px;
                        "></div>
                        Сохранение...
                    </div>
                </div>
            `;
            document.body.appendChild(loader);

            // Добавляем анимацию
            const style = document.createElement('style');
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
        loader.style.display = 'block';
    } else {
        if (loader) {
            loader.style.display = 'none';
        }
    }
}

/**
 * Показывает уведомление
 * @param {string} message - Текст сообщения
 * @param {string} type - Тип уведомления: 'success', 'error', 'info', 'warning'
 */
function showNotification(message, type = 'info') {
    // Удаляем старые уведомления
    const oldNotifications = document.querySelectorAll('.notification');
    oldNotifications.forEach(notification => {
        notification.remove();
    });

    // Создаем новое уведомление
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;

    // Стили для уведомления
    const bgColor = type === 'success' ? '#4CAF50' :
        type === 'error' ? '#f44336' :
            type === 'warning' ? '#ff9800' : '#2196F3';

    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background-color: ${bgColor};
        color: white;
        border-radius: 4px;
        z-index: 10000;
        animation: slideIn 0.3s ease;
        font-family: Arial, sans-serif;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        max-width: 400px;
        word-wrap: break-word;
    `;

    document.body.appendChild(notification);

    // Добавляем CSS для анимации если еще нет
    if (!document.querySelector('#notificationStyles')) {
        const style = document.createElement('style');
        style.id = 'notificationStyles';
        style.textContent = `
            @keyframes slideIn {
                from { 
                    transform: translateX(100%) translateY(-20px); 
                    opacity: 0; 
                }
                to { 
                    transform: translateX(0) translateY(0); 
                    opacity: 1; 
                }
            }
            
            @keyframes slideOut {
                from { 
                    transform: translateX(0) translateY(0); 
                    opacity: 1; 
                }
                to { 
                    transform: translateX(100%) translateY(-20px); 
                    opacity: 0; 
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Удаляем уведомление через 3 секунды
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

// Закрытие админ-панели по клавише Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const panel = document.getElementById('adminPanelContainer');
        if (panel && panel.classList.contains('show')) {
            closePanelAdmin();
        }
    }

    // Сохранение по Ctrl+Enter
    if (e.ctrlKey && e.key === 'Enter') {
        const panel = document.getElementById('adminPanelContainer');
        if (panel && panel.classList.contains('show')) {
            e.preventDefault();
            savePanelAdmin();
        }
    }
});

// Обработчик для загрузки изображения
document.getElementById('imageUpload')?.addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    // Проверяем тип файла
    if (!file.type.match('image.*')) {
        showNotification('Пожалуйста, выберите файл изображения', 'error');
        e.target.value = '';
        return;
    }

    // Проверяем размер файла (максимум 5MB)
    if (file.size > 5 * 1024 * 1024) {
        showNotification('Размер файла не должен превышать 5MB', 'error');
        e.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('employeeImage').src = e.target.result;
        showNotification('Изображение загружено', 'success');
    };
    reader.onerror = function () {
        showNotification('Ошибка при загрузке изображения', 'error');
    };
    reader.readAsDataURL(file);
});