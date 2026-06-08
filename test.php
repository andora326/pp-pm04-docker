<?php
/**
 * Отчёт по практике ПМ04
 * Сопровождение и обслуживание ПО компьютерных систем
 */

$host = getenv('MYSQL_HOST') ?: 'database';
$user = getenv('MYSQL_USER') ?: 'root';
$pass = getenv('MYSQL_PASSWORD') ?: 'root';
$db = getenv('MYSQL_DATABASE') ?: 'book_trade_db';

echo "<h1>Практика ПМ04</h1>";
echo "<p>Дата: " . date('d.m.Y H:i:s') . "</p>";

try {
    // 1. ПОДКЛЮЧЕНИЕ К БД
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✅ 1. Подключение к БД - успешно</p>";
    
    // 2. SELECT (чтение данных)
    $books = $pdo->query("SELECT book_id, title, retail_price FROM books LIMIT 5")->fetchAll();
    echo "<p>✅ 2. Чтение данных (SELECT) - найдено " . count($books) . " книг</p>";
    
    // 3. INSERT (добавление)
    $insert = $pdo->prepare("INSERT INTO books (isbn, title, retail_price, stock_quantity) VALUES (?, ?, ?, ?)");
    $insert->execute(['TEST-777', 'Тестовая книга', 199, 10]);
    $newId = $pdo->lastInsertId();
    echo "<p>✅ 3. Добавление данных (INSERT) - ID: $newId</p>";
    
    // 4. UPDATE (обновление)
    $update = $pdo->prepare("UPDATE books SET retail_price = 99 WHERE book_id = ?");
    $update->execute([$newId]);
    echo "<p>✅ 4. Обновление данных (UPDATE) - цена изменена</p>";
    
    // 5. DELETE (удаление)
    $delete = $pdo->prepare("DELETE FROM books WHERE book_id = ?");
    $delete->execute([$newId]);
    echo "<p>✅ 5. Удаление данных (DELETE) - тестовая книга удалена</p>";
    
    // 6. JOIN (связь таблиц)
    $join = $pdo->query("
        SELECT b.title, a.full_name 
        FROM books b
        JOIN book_authors ba ON b.book_id = ba.book_id
        JOIN authors a ON ba.author_id = a.author_id
        LIMIT 3
    ")->fetchAll();
    echo "<p>✅ 6. Сложный запрос (JOIN) - показано " . count($join) . " связей книга-автор</p>";
    
    // 7. ЗАЩИТА ОТ SQL-ИНЪЕКЦИЙ (подготовленный запрос)
    $safe = $pdo->prepare("SELECT * FROM books WHERE title = ?");
    $safe->execute(['Война и мир']);
    echo "<p>✅ 7. Защита от SQL-инъекций - используются PDO подготовленные запросы</p>";
    
    // 8. ТАБЛИЦА РИСКОВ (вывод на экран)
    echo "<h2>Анализ рисков</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Риск</th><th>Вероятность</th><th>Мера защиты</th></tr>";
    echo "<tr><td>Потеря данных</td><td>Средняя</td><td>Регулярные бэкапы, Docker volumes</td></tr>";
    echo "<tr><td>SQL-инъекции</td><td>Высокая</td><td>PDO, подготовленные запросы</td></tr>";
    echo "<tr><td>Отказ контейнера</td><td>Низкая</td><td>restart: unless-stopped</td></tr>";
    echo "<tr><td>Утечка паролей</td><td>Средняя</td><td>Переменные окружения, не хранить в коде</td></tr>";
    echo "</table>";
    
    // 9. ХАРАКТЕРИСТИКИ КАЧЕСТВА
    echo "<h2>Характеристики качества ПО</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Характеристика</th><th>Оценка</th><th>Обоснование</th></tr>";
    echo "<tr><td>Функциональность</td><td>Высокая</td><td>CRUD операции работают</td></tr>";
    echo "<tr><td>Безопасность</td><td>Высокая</td><td>PDO, подготовленные запросы</td></tr>";
    echo "<tr><td>Надёжность</td><td>Средняя</td><td>Есть обработка ошибок</td></tr>";
    echo "<tr><td>Поддерживаемость</td><td>Высокая</td><td>Код структурирован, есть комментарии</td></tr>";
    echo "</table>";
    
    // 10. ИТОГ
    echo "<h2>Вывод</h2>";
    echo "<p>В ходе практики выполнены следующие задачи:</p>";
    echo "<ul>";
    echo "<li>Настройка Docker и Git</li>";
    echo "<li>Создание Dockerfile и docker-compose.yml</li>";
    echo "<li>Интеграция PHP-приложения с MySQL</li>";
    echo "<li>Реализация CRUD операций</li>";
    echo "<li>Резервное копирование БД</li>";
    echo "<li>Анализ рисков и качества ПО</li>";
    echo "</ul>";
    echo "<p style='color:green'><strong>✅ Все задачи практики выполнены</strong></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Ошибка: " . $e->getMessage() . "</p>";
}
?>