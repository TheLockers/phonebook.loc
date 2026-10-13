<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html" />
    <meta name="viewport" content="width=device-width, initial-scale=0.75" />
    <meta http-equiv="Cache-Control" content="max-age=31536000, public" />
    <meta name="description" content="" />
    <title>Телефонный справочник</title>
    <link rel="stylesheet" href="css/style.css" />
</head>

<body>
    <?php
    session_start();

    // Проверяем, авторизован ли пользователь
    $isLoggedIn = isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    $currentUserId = $_SESSION['user_id'] ?? null;

    // Отображаем сообщения об ошибках
    if (isset($_SESSION['error'])): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border-radius: 5px;">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (!$isLoggedIn): ?>
        <!-- ФОРМА ВХОДА -->
        <div class="container-login">
            <form class="login-form" action="validation-form/auth.php" method="post">
                <h1 class="login-text">Войти в систему</h1>
                <div class="email-input-box">
                    <div class="label">Почта:</div>
                    <input class="input" type="email" name="email" id="email" placeholder="Почта" required />
                </div>
                <div class="pass-input-box">
                    <div class="label">Пароль:</div>
                    <input class="input" type="password" name="password" id="password" placeholder="Пароль" required />
                </div>
                <div class="btn-login">
                    <button class="submit-btn" type="submit">Войти</button>
                </div>
                <?php if (isset($_SESSION['login_error'])): ?>
                    <div style="color: #f00; margin-top: 10px; text-align: center;">
                        <?= htmlspecialchars($_SESSION['login_error']) ?>
                        <?php unset($_SESSION['login_error']); ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>

    <?php else:
        require_once 'config.php';

        // Добавляем панель пользователя
        ?>
        <div style="position: fixed; top: 10px; right: 10px; background: white; padding: 10px 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); z-index: 1000;">
            <span>Вы вошли как: <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
            <?php if ($isAdmin): ?>
                <span style="color: #d00; margin-left: 5px;">(Администратор)</span>
            <?php endif; ?>
            <a href="validation-form/exit.php" style="margin-left: 10px; color: #d00; text-decoration: none;">Выйти</a>
        </div>

        <?php
        // Получаем данные из базы
        $db = getdb();
        $sql = "SELECT c.id, c.first_name, c.last_name, c.middle_name,
               p.position, u.unit_name, c.corps, c.office_number,
               c.internal_phone_number, c.landline, c.mobile_phone_number,
               c.email, c.created_at, c.updated_at, c.photo, c.is_admin
                FROM contacts c
                LEFT JOIN post p ON c.post_id = p.id
                LEFT JOIN units u ON c.unit_id = u.id
                ORDER BY c.last_name, c.first_name ASC";
        $stmt = $db->query($sql);
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <header class="head">
            <h1>Телефонный справочник сотрудников</h1>
        </header>
        <span class="span-main"></span>
        <table class="tabl" id="employees-table">
            <thead>
                <tr>
                    <th onclick="toggleFilter(this, 0)">
                        Фамилия
                        <div class="filter-input-container">
                            <input type="text" class="filter-input" placeholder="Фильтр по фамилии..."
                                oninput="filterTable(0, this.value)" />
                        </div>
                    </th>
                    <th onclick="toggleFilter(this, 1)">
                        Имя
                        <div class="filter-input-container">
                            <input type="text" class="filter-input" placeholder="Фильтр по имени..."
                                oninput="filterTable(1, this.value)" />
                        </div>
                    </th>
                    <th onclick="toggleFilter(this, 2)">
                        Отчество
                        <div class="filter-input-container">
                            <input type="text" class="filter-input" placeholder="Фильтр по отчеству..."
                                oninput="filterTable(2, this.value)" />
                        </div>
                    </th>
                    <th onclick="toggleFilter(this, 3)">
                        Должность
                        <div class="filter-input-container">
                            <input type="text" class="filter-input" placeholder="Фильтр по должности..."
                                oninput="filterTable(3, this.value)" />
                        </div>
                    </th>
                    <th onclick="toggleFilter(this, 4)">
                        Подразделение
                        <div class="filter-input-container">
                            <input type="text" class="filter-input" placeholder="Фильтр по подразделению..."
                                oninput="filterTable(4, this.value)" />
                        </div>
                    </th>
                    <th onclick="toggleFilter(this, 5)">
                        Корпус
                        <div class="filter-input-container">
                            <input type="text" class="filter-input" placeholder="Фильтр по корпусу..."
                                oninput="filterTable(5, this.value)" />
                        </div>
                    </th>
                    <th onclick="toggleFilter(this, 6)">
                        Номер кабинета
                        <div class="filter-input-container">
                            <input type="text" class="filter-input" placeholder="Фильтр по номеру кабинета..."
                                oninput="filterTable(6, this.value)" />
                        </div>
                    </th>
                    <th onclick="toggleFilter(this, 7)">
                        Внутренний телефонный номер
                        <div class="filter-input-container">
                            <input type="text" class="filter-input" placeholder="Фильтр по внут. номеру..."
                                oninput="filterTable(7, this.value)" />
                        </div>
                    </th>
                    <th onclick="toggleFilter(this, 8)">
                        Городской телефонный номер
                        <div class="filter-input-container">
                            <input type="text" class="filter-input" placeholder="Фильтр по городск. номеру..."
                                oninput="filterTable(8, this.value)" />
                        </div>
                    </th>
                    <th onclick="toggleFilter(this, 9)">
                        Мобильный телефонный номер
                        <div class="filter-input-container">
                            <input type="text" class="filter-input" placeholder="Фильтр по моб. номеру..."
                                oninput="filterTable(9, this.value)" />
                        </div>
                    </th>
                    <th onclick="toggleFilter(this, 10)">
                        Электронная почта
                        <div class="filter-input-container">
                            <input type="text" class="filter-input" placeholder="Фильтр по эл. почте..."
                                oninput="filterTable(10, this.value)" />
                        </div>
                    </th>
                    <th class="th-admin">Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $contact):
                    $canEdit = $isAdmin || $contact['id'] == $currentUserId;
                    ?>
                    <tr data-id="<?= $contact['id'] ?>"
                        data-created-at="<?= $contact['created_at'] ?>"
                        data-photo="<?= !empty($contact['photo']) ? 'data:image/jpeg;base64,' . base64_encode($contact['photo']) : '' ?>"
                        data-is-admin="<?= $contact['is_admin'] ?>">
                        <td><?= htmlspecialchars($contact['last_name']) ?></td>
                        <td><?= htmlspecialchars($contact['first_name']) ?></td>
                        <td><?= htmlspecialchars($contact['middle_name']) ?></td>
                        <td><?= htmlspecialchars($contact['position']) ?></td>
                        <td><?= htmlspecialchars($contact['unit_name']) ?></td>
                        <td><?= htmlspecialchars($contact['corps']) ?></td>
                        <td><?= htmlspecialchars($contact['office_number']) ?></td>
                        <td><?= htmlspecialchars($contact['internal_phone_number']) ?></td>
                        <td><?= htmlspecialchars($contact['landline']) ?></td>
                        <td><?= htmlspecialchars($contact['mobile_phone_number']) ?></td>
                        <td><?= htmlspecialchars($contact['email']) ?></td>
                        <td>
                            <?php if ($canEdit): ?>
                                <button class="admin-panel-link" onclick="adminPanelOpen(<?= $contact['id'] ?>)"
                                    title="<?= $contact['id'] == $currentUserId ? 'Редактировать свой профиль' : 'Редактировать профиль' ?>">
                                    <img class="admin-img" src="image/icon.png" alt="изменить" />
                                </button>
                            <?php elseif ($contact['id'] == $currentUserId): ?>
                                <span style="color: #666; font-style: italic;">(ваш профиль)</span>
                            <?php else: ?>
                                <span style="color: #999;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($isLoggedIn): ?>
            <!-- Админ-панель доступна для редактирования своего профиля или если админ -->
            <div class="admin-panel-container" id="adminPanelContainer">
                <input type="hidden" id="employeeId" value="" />
                <input type="hidden" id="currentUserId" value="<?= $currentUserId ?>" />
                <input type="hidden" id="isUserAdmin" value="<?= $isAdmin ? '1' : '0' ?>" />

                <div class="box lastname">
                    <label>Фамилия</label>
                    <input type="text" id="lastNameInput" class="admin-input" placeholder="Введите фамилию" />
                </div>

                <div class="box firstname">
                    <label>Имя</label>
                    <input type="text" id="firstNameInput" class="admin-input" placeholder="Введите имя" />
                </div>

                <div class="box middlename">
                    <label>Отчество</label>
                    <input type="text" id="middleNameInput" class="admin-input" placeholder="Введите отчество" />
                </div>

                <div class="box img">
                    <img id="employeeImage" src="image/default.png" alt="Фото" style="max-width: 100%; max-height: 195px; display: block; margin: auto; object-fit: cover;" />
                    <div class="pages">
                        <button type="button" onclick="document.getElementById('imageUpload').click()"
                            style="padding: 7px 10px; font-size: 16px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">Выбрать
                        </button>
                        <button type="button" onclick="resetImage()"
                            style="padding: 7px 10px; font-size: 16px; background: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;">Удалить
                        </button>
                        <input type="file" id="imageUpload" accept="image/*" style="display: none;"
                            onchange="previewImage(this)" />
                    </div>
                </div>
                <div class="box interal-number">
                    <label>Внутр номер</label>
                    <input type="text" id="internalPhoneInput" class="admin-input" placeholder="Введите внутренний номер" />
                </div>

                <div class="box city-number">
                    <label>Город номер</label>
                    <input type="text" id="landlineInput" class="admin-input" placeholder="Введите городской номер" />
                </div>

                <div class="box mobile-number">
                    <label>Мобл номер</label>
                    <input type="text" id="mobilePhoneInput" class="admin-input" placeholder="Введите мобильный номер" />
                </div>

                <div class="box email">
                    <label>Электронная почта</label>
                    <input type="email" id="emailInput" class="admin-input" placeholder="Введите email" />
                </div>

                <div class="box post">
                    <label>Должность</label>
                    <input type="text" id="positionInput" class="admin-input" placeholder="Введите должность" />
                </div>

                <div class="box position">
                    <label>Подразделение</label>
                    <input type="text" id="unitInput" class="admin-input" placeholder="Введите подразделение" />
                </div>

                <div class="box corpus">
                    <label>Корпус</label>
                    <input type="text" id="corpsInput" class="admin-input" placeholder="Введите корпус" />
                </div>

                <div class="box numberpost">
                    <label>Кабинет</label>
                    <input type="text" id="officeNumberInput" class="admin-input" placeholder="Введите номер кабинета" />
                </div>

                <!-- Поле для пароля (только для своего профиля) -->
                <div class="box password-box" id="passwordBox" style="display: none;">
                    <label>Новый пароль (оставьте пустым, если не меняете)</label>
                    <input type="password" id="newPasswordInput" class="admin-input" placeholder="Новый пароль" />
                    <input type="password" id="confirmPasswordInput" class="admin-input" placeholder="Подтвердите пароль" style="margin-top: 5px;" />
                </div>

                <!-- Поле для админ-статуса (только для админов) -->
                <?php if ($isAdmin): ?>
                    <div class="box admin-status">
                        <label>
                            <input type="checkbox" id="isAdminCheckbox" />
                            Админ
                        </label>
                    </div>
                <?php endif; ?>

                <div class="box cansel" onclick="closePanelAdmin()">
                    <button class="admin-btn admin-btn-cancel">Закрыть</button>
                </div>

                <div class="box save" onclick="savePanelAdmin()">
                    <button class="admin-btn admin-btn-save">Сохранить</button>
                </div>

                <div class="box first-created">
                    <label>Создан:</label>
                    <span class="span-time" id="createdAt">-</span>
                </div>

                <div class="box last-setting">
                    <label>Изменен:</label>
                    <span class="span-time" id="updatedAt">-</span>
                </div>
            </div>
            <div class="admin-panel-overlay" id="adminPanelOverlay" onclick="closePanelAdmin()"></div>
        <?php endif; ?>
    <?php endif; ?>
    <script src="js/script.js"></script>
</body>
</html>