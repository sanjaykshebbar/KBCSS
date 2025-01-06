<?php
// Database connection
$host = 'localhost';
$dbname = 'kbcss_users';
$username = 'root'; // Update with your database username
$password = 'W1nd0vv$';     // Update with your database password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Execute custom SQL query
$output = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['custom_sql_query'])) {
    $query = $_POST['sql_query'] ?? '';
    if (!empty($query)) {
        try {
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $output = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $output = "Error: " . $e->getMessage();
        }
    }
}
?>
<html>
<head>
    <title>demo</title>
    <style>
        /* Basic page styling */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
            color: #333;
        }

        /* SQL query section styling */
        .query-section {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 1900px;
            margin: 0 auto;
        }

        .query-section h3 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        textarea {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            line-height: 1.5;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Query output section styling */
        .query-output {
            margin-top: 30px;
        }

        .query-output h4 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        table td {
            word-wrap: break-word;
            max-width: 200px;
        }

        table td, table th {
            text-overflow: ellipsis;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <!-- SQL Query Execution Section -->
    <div class="query-section">
        <h3>Execute SQL Query</h3>
        <form method="POST">
            <textarea name="sql_query" rows="6" cols="100" placeholder="Enter SQL query here..."></textarea><br>
            <button type="submit" name="custom_sql_query">Execute Query</button>
        </form>

        <?php if (!empty($output)): ?>
            <div class="query-output">
                <h4>Query Results:</h4>
                <?php if (is_array($output)): ?>
                    <table>
                        <thead>
                            <tr>
                                <?php foreach (array_keys($output[0]) as $column): ?>
                                    <th><?= htmlspecialchars($column) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($output as $row): ?>
                                <tr>
                                    <?php foreach ($row as $value): ?>
                                        <td><?= htmlspecialchars($value) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?= htmlspecialchars($output) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
