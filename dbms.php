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

// Fetch table data for users, login_activity, and password_resets
function fetchTableData($conn, $table) {
    // Wrap the table name in backticks to handle special characters like '&'
    $stmt = $conn->prepare("SELECT * FROM `$table`");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle Add, Edit, and Delete operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'] ?? null;

    // Add record for users table
    if (isset($_POST['add_record']) && $table == 'users') {
        $columns = json_decode($_POST['columns'], true);

        if ($table && is_array($columns)) {
            // Generate MD5 for password before inserting
            if (!empty($columns['password'])) {
                $columns['password'] = md5($columns['password']);
            }

            $columnsString = implode(", ", array_keys($columns));
            $placeholders = ":" . implode(", :", array_keys($columns));

            $sql = "INSERT INTO `$table` ($columnsString) VALUES ($placeholders)";
            $stmt = $conn->prepare($sql);

            foreach ($columns as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->execute();
            echo "<script>alert('Record added successfully!'); window.location.href = window.location.href;</script>";
        }
    }

    // Delete selected records
    if (isset($_POST['delete_selected']) && $table) {
        $selectedIds = $_POST['selected_ids'] ?? [];

        if (!empty($selectedIds)) {
            $idsString = implode(",", $selectedIds);
            $sql = "DELETE FROM `$table` WHERE id IN ($idsString)";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            echo "<script>alert('Selected records deleted successfully!'); window.location.href = window.location.href;</script>";
        }
    }

    // Execute custom SQL query
    $output = '';
    if (isset($_POST['custom_sql_query'])) {
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
}

// Table list for displaying records
$tableNames = ['users', 'login_activity', 'password_resets', 'q&a'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Management - Excel View</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .tabs {
            display: flex;
            border-bottom: 1px solid #ccc;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid #ccc;
            border-bottom: none;
        }
        .tab.active {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .select-all {
            margin-bottom: 10px;
        }
        form {
            margin-top: 10px;
        }
        .json-area {
            width: 100%;
        }
    </style>
    <script>
        function switchTab(tabIndex) {
            const tabs = document.querySelectorAll('.tab');
            const contents = document.querySelectorAll('.tab-content');

            // Remove the 'active' class from all tabs and content sections
            tabs.forEach((tab, index) => {
                tab.classList.remove('active');
                contents[index].classList.remove('active');
            });

            // Add the 'active' class to the selected tab and content section
            tabs[tabIndex].classList.add('active');
            contents[tabIndex].classList.add('active');
        }

        // Select all checkbox functionality
        function toggleSelectAll(source) {
            const checkboxes = document.querySelectorAll('.record-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = source.checked;
            });
        }

        // Function to generate MD5 hash for password and update JSON textarea
        function generateMd5() {
            const passwordField = document.getElementById('password');
            const md5Password = md5(passwordField.value);
            const columnsArea = document.getElementById('columns');
            const columns = JSON.parse(columnsArea.value || '{}');
            columns.password = md5Password;
            columnsArea.value = JSON.stringify(columns, null, 4);
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-md5/2.10.0/md5.min.js"></script>
</head>
<body>
    <h1>Database Management - Excel-like Interface</h1>
    <div class="tabs">
        <div class="tab active" onclick="switchTab(0)">Data Entry (Users Table)</div>
        <div class="tab" onclick="switchTab(1)">Users Table (View & Delete)</div>
        <div class="tab" onclick="switchTab(2)">Login Activity Table (View & Delete)</div>
        <div class="tab" onclick="switchTab(3)">Password Resets Table (View & Delete)</div>
        <div class="tab" onclick="switchTab(4)">Execute SQL Query</div>
        <div class="tab" onclick="switchTab(5)">Q&A Table (View & Delete)</div>
    </div>

    <!-- Data Entry Sheet (for Users Table) -->
    <div class="tab-content active">
        <h3>Data Entry for Users</h3>
        <form method="POST">
            <div>
                <h4>Enter User Details (JSON Format will be generated on clicking Generate MD5)</h4>
                <label for="columns">Enter columns as JSON:</label>
                <textarea name="columns" id="columns" rows="10" cols="60" class="json-area"></textarea><br>

                <label for="password">Password:</label>
                <input type="text" id="password" name="password" required><br>

                <button type="button" onclick="generateMd5()">Generate MD5</button><br>

                <button type="submit" name="add_record">Add Record</button>
                <input type="hidden" name="table" value="users">
            </div>
        </form>
    </div>

    <!-- Users Table (View & Delete) -->
    <div class="tab-content">
        <h3>Users Table (View & Delete)</h3>
        <?php $data = fetchTableData($conn, 'users'); ?>
        <form method="POST">
            <div class="select-all">
                <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)"> Select All
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Select</th>
                        <?php if (!empty($data)): ?>
                            <?php foreach (array_keys($data[0]) as $column): ?>
                                <th><?= $column ?></th>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <th>No records found</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><input type="checkbox" class="record-checkbox" name="selected_ids[]" value="<?= htmlspecialchars($row['id']) ?>"></td>
                            <?php foreach ($row as $value): ?>
                                <td><?= htmlspecialchars($value) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="delete_selected" onclick="return confirm('Are you sure you want to delete selected records?')">Delete Selected</button>
            <input type="hidden" name="table" value="users">
        </form>
    </div>

    <!-- Login Activity Table (View & Delete) -->
    <div class="tab-content">
        <h3>Login Activity Table (View & Delete)</h3>
        <?php $data = fetchTableData($conn, 'login_activity'); ?>
        <form method="POST">
            <div class="select-all">
                <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)"> Select All
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Select</th>
                        <?php if (!empty($data)): ?>
                            <?php foreach (array_keys($data[0]) as $column): ?>
                                <th><?= $column ?></th>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <th>No records found</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><input type="checkbox" class="record-checkbox" name="selected_ids[]" value="<?= htmlspecialchars($row['id']) ?>"></td>
                            <?php foreach ($row as $value): ?>
                                <td><?= htmlspecialchars($value) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="delete_selected" onclick="return confirm('Are you sure you want to delete selected records?')">Delete Selected</button>
            <input type="hidden" name="table" value="login_activity">
        </form>
    </div>

    <!-- Password Resets Table (View & Delete) -->
    <div class="tab-content">
        <h3>Password Resets Table (View & Delete)</h3>
        <?php $data = fetchTableData($conn, 'password_resets'); ?>
        <form method="POST">
            <div class="select-all">
                <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)"> Select All
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Select</th>
                        <?php if (!empty($data)): ?>
                            <?php foreach (array_keys($data[0]) as $column): ?>
                                <th><?= $column ?></th>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <th>No records found</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><input type="checkbox" class="record-checkbox" name="selected_ids[]" value="<?= htmlspecialchars($row['id']) ?>"></td>
                            <?php foreach ($row as $value): ?>
                                <td><?= htmlspecialchars($value) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="delete_selected" onclick="return confirm('Are you sure you want to delete selected records?')">Delete Selected</button>
            <input type="hidden" name="table" value="password_resets">
        </form>
    </div>

    <!-- Execute SQL Query -->
    <div class="tab-content">
        <h3>Execute SQL Query</h3>
        <form method="POST">
            <textarea name="sql_query" rows="5" cols="60"></textarea><br>
            <button type="submit" name="custom_sql_query">Execute Query</button>
        </form>
        <h4>Output:</h4>
        <pre>
            <?php
            if (!empty($output)) {
                print_r($output);
            }
            ?>
        </pre>
    </div>

    <!-- Q&A Table (View & Delete) -->
    <div class="tab-content">
        <h3>Q&A Table (View & Delete)</h3>
        <?php $data = fetchTableData($conn, 'q&a'); ?>
        <form method="POST">
            <table>
                <thead>
                    <tr>
                        <?php if (!empty($data)): ?>
                            <?php foreach (array_keys($data[0]) as $column): ?>
                                <th><?= $column ?></th>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <th>No records found</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>

                            <?php foreach ($row as $value): ?>
                                <td><?= htmlspecialchars($value) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="delete_selected" onclick="return confirm('Are you sure you want to delete selected records?')">Delete Selected</button>
            <input type="hidden" name="table" value="q&a">
        </form>
    </div>
</body>
</html>
