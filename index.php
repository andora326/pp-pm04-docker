<?php
$host = 'database';
$user = 'root';
$pass = 'root';
$dbname = 'book_trade_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Ошибка: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Интеграция с БД</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f0f2f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .status {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin-top: 0;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .search-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .search-form input {
            padding: 8px;
            width: 250px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-form button {
            padding: 8px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .clear-btn {
            background: #6c757d;
        }
        .price-info {
            background: #e7f3ff;
            color: #004085;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .no-results {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Интеграция приложения с БД</h1>
        
        <div class="status">
            ✅ Подключение к БД <?= $dbname ?> успешно!
        </div>

        <div class="card">
            <h3>Поиск книг</h3>
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="Введите название книги..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <input type="number" name="min_price" placeholder="Цена от (₽)" step="10" min="0" value="<?= isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '' ?>">
                <button type="submit">Искать</button>
                <a href="?"><button type="button" class="clear-btn" onclick="window.location.href='?'">Сбросить</button></a>
            </form>
            
            <?php
            $search_param = isset($_GET['search']) ? $_GET['search'] : '';
            $min_price_param = isset($_GET['min_price']) ? $_GET['min_price'] : '';
            
            if (!empty($search_param) || !empty($min_price_param)) {
                $sql = "SELECT title, retail_price, stock_quantity FROM books WHERE 1=1";
                $types = "";
                $params = [];
                
                if (!empty($search_param)) {
                    $sql .= " AND title LIKE ?";
                    $types .= "s";
                    $params[] = "%" . $search_param . "%";
                }
                
                if (!empty($min_price_param) && is_numeric($min_price_param) && $min_price_param > 0) {
                    $sql .= " AND retail_price >= ?";
                    $types .= "d"; 
                    $params[] = (float)$min_price_param;
                }
                
                $sql .= " LIMIT 10";
                
                $stmt = $conn->prepare($sql);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                
                echo '<div class="price-info">';
                echo '🔍 <strong>Параметры поиска:</strong> ';
                if (!empty($search_param)) echo 'Название: "' . htmlspecialchars($search_param) . '" ';
                if (!empty($min_price_param) && $min_price_param > 0) echo 'Цена от: ' . (float)$min_price_param . ' ₽';
                echo '</div>';
                
            } else {
                $result = $conn->query("SELECT title, retail_price, stock_quantity FROM books LIMIT 5");
            }
            ?>
            
            <table>
                <tr>
                    <th>Название</th>
                    <th>Цена (₽)</th>
                    <th>Остаток (шт.)</th>
                </tr>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= number_format($row['retail_price'], 2) ?></td>
                        <td><?= htmlspecialchars($row['stock_quantity']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: #dc3545;">
                            ❌ Книги не найдены по заданным критериям
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
            
            <?php 
            if (isset($stmt)) $stmt->close();
            ?>
        </div>

        <div class="card">
            <h3>Заказы</h3>
            <?php
            $stmt = $conn->prepare("
                SELECT o.order_id, c.company_name, o.total_amount, o.status 
                FROM orders o 
                JOIN customers c ON o.customer_id = c.customer_id 
                LIMIT 5
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            ?>
            <table>
                <tr>
                    <th>№ заказа</th>
                    <th>Клиент</th>
                    <th>Сумма (₽)</th>
                    <th>Статус</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['order_id']) ?></td>
                    <td><?= htmlspecialchars($row['company_name']) ?></td>
                    <td><?= number_format($row['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
            <?php $stmt->close(); ?>
        </div>

        <div class="card">
            <h3>Статистика</h3>
            <?php
            $books_count = $conn->query("SELECT COUNT(*) FROM books")->fetch_row()[0];
            $orders_count = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
            
            $avg_price = $conn->query("SELECT AVG(retail_price) FROM books")->fetch_row()[0];
            $max_price = $conn->query("SELECT MAX(retail_price) FROM books")->fetch_row()[0];
            $min_price_stat = $conn->query("SELECT MIN(retail_price) FROM books")->fetch_row()[0];
            ?>
            <table>
                <tr>
                    <th>Книг в каталоге</th>
                    <td><?= number_format($books_count) ?></td>
                </tr>
                <tr>
                    <th>Заказов</th>
                    <td><?= number_format($orders_count) ?></td>
                </tr>
                <tr>
                    <th>Средняя цена книги</th>
                    <td><?= number_format($avg_price, 2) ?> ₽</td>
                </tr>
                <tr>
                    <th>Диапазон цен</th>
                    <td>от <?= number_format($min_price_stat, 2) ?> до <?= number_format($max_price, 2) ?> ₽</td>
                </tr>
            </table>
        </div>

        <div class="card">
            <h3>🛡️ Защита информации</h3>
            <table>
                <tr>
                    <th>Метод защиты</th>
                    <th>Реализация</th>
                </tr>
                <tr>
                    <td>Защита от SQL-инъекций</td>
                    <td>Использование подготовленных запросов (prepare + bind_param)</td>
                </tr>
                <tr>
                    <td>Защита от XSS-атак</td>
                    <td>Экранирование вывода через htmlspecialchars()</td>
                </tr>
                <tr>
                    <td>Валидация входных данных</td>
                    <td>Проверка на пустоту, тип данных (is_numeric) и положительное значение</td>
                </tr>
                <tr>
                    <td>Поиск по цене</td>
                    <td>Динамическое построение SQL с условием retail_price >= ?</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>