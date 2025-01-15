<?php
// Include the database connection
include '../Administraton/php/db_connection.php'; // Adjusted to reflect the correct path

// Start session
session_start();

// Initialize variables for filtering
$filterEnabled = isset($_POST['filter_enabled']) && $_POST['filter_enabled'] === 'on';
$filterEmail = $filterEnabled ? $_POST['filter_email'] ?? '' : '';
$filterIP = $filterEnabled ? $_POST['filter_ip'] ?? '' : '';

try {
    // Fetch login activity with or without filters
    $query = "SELECT * FROM login_activity";
    $conditions = [];

    if ($filterEnabled) {
        if (!empty($filterEmail)) {
            $conditions[] = "email LIKE :email";
        }
        if (!empty($filterIP)) {
            $conditions[] = "ip_address LIKE :ip_address";
        }
    }

    if ($conditions) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY login_time DESC";

    $stmt = $pdo->prepare($query);

    if ($filterEnabled) {
        if (!empty($filterEmail)) {
            $stmt->bindValue(':email', '%' . $filterEmail . '%');
        }
        if (!empty($filterIP)) {
            $stmt->bindValue(':ip_address', '%' . $filterIP . '%');
        }
    }

    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching login activity: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Activity</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin-top: 30px;
        }
        .table {
            width: 100%;
            table-layout: fixed;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
            padding: 12px 15px;
        }
        .table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .table tr:hover {
            background-color: #e9ecef;
        }
        .table td {
            word-wrap: break-word;
            word-break: break-word;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Login Activity</h2>

        <form method="POST" class="mb-4">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="filterSwitch" name="filter_enabled" <?php echo $filterEnabled ? 'checked' : ''; ?>>
                <label class="form-check-label" for="filterSwitch">Enable Filter</label>
            </div>
            <div class="row g-3 <?php echo $filterEnabled ? '' : 'd-none'; ?>" id="filterFields">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="filter_email" placeholder="Filter by Email" value="<?php echo htmlspecialchars($filterEmail); ?>">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="filter_ip" placeholder="Filter by IP Address" value="<?php echo htmlspecialchars($filterIP); ?>">
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
                <button type="reset" class="btn btn-secondary" onclick="resetForm()">Reset</button>
            </div>
        </form>

        <table class="table table-bordered" id="loginTable">
            <thead>
                <tr>
                    <th>Login ID</th>
                    <th>User ID</th>
                    <th>Email</th>
                    <th>User Type</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                    <th>Session Duration (seconds)</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $activity) { ?>
                    <tr>
                        <td><?php echo $activity['Login_id']; ?></td>
                        <td><?php echo $activity['id']; ?></td>
                        <td><?php echo $activity['email']; ?></td>
                        <td><?php echo $activity['userType']; ?></td>
                        <td><?php echo $activity['login_time']; ?></td>
                        <td><?php echo $activity['logout_time'] ?: 'Still Logged In'; ?></td>
                        <td><?php echo $activity['session_duration'] ?: 'Ongoing'; ?></td>
                        <td><?php echo $activity['ip_address']; ?></td>
                        <td><?php echo $activity['user_agent']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
        const filterSwitch = document.getElementById('filterSwitch');
        const filterFields = document.getElementById('filterFields');

        filterSwitch.addEventListener('change', () => {
            if (filterSwitch.checked) {
                filterFields.classList.remove('d-none');
            } else {
                filterFields.classList.add('d-none');
            }
        });

        function resetForm() {
            filterSwitch.checked = false;
            filterFields.classList.add('d-none');
        }
    </script>
</body>
</html>
