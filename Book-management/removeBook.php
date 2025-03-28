<?php
// Include the database connection
include '../Administraton/php/db_connection.php';

// Define directories
$bookDirectory = "../Book-management/books";
$archiveDirectory = "../Book-management/archived";

// Ensure archive folder exists
if (!file_exists($archiveDirectory)) {
    mkdir($archiveDirectory, 0777, true);
}

// Handle book removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_book'])) {
    $bookId = $_POST['book_id'];
    $reason = $_POST['reason'];

    if (empty($reason)) {
        echo "<script>alert('Removal reason is mandatory!');</script>";
    } else {
        // Fetch book details
        $stmt = $pdo->prepare("SELECT * FROM books WHERE ID = :bookId AND book_status = 'Available'");
        $stmt->execute([':bookId' => $bookId]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book) {
            $bookPath = $book['Book_image'];
            $newPath = str_replace("books", "archived", $bookPath);

            // Move file to archived folder
            if (rename($bookPath, $newPath)) {
                // Update database
                $updateStmt = $pdo->prepare("UPDATE books SET book_status = 'Archived', notes = :reason WHERE ID = :bookId");
                $updateStmt->execute([':reason' => $reason, ':bookId' => $bookId]);

                echo "<script>alert('Book successfully archived!'); window.location.reload();</script>";
            } else {
                echo "<script>alert('Error moving the book!');</script>";
            }
        }
    }
}

// Fetch active books
$activeBooks = $pdo->query("SELECT * FROM books WHERE book_status = 'Available'")->fetchAll(PDO::FETCH_ASSOC);

// Fetch archived count
$archivedCount = $pdo->query("SELECT COUNT(*) FROM books WHERE book_status = 'Archived'")->fetchColumn();
$availableCount = $pdo->query("SELECT COUNT(*) FROM books WHERE book_status = 'Available'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Books</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { width: 80%; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .summary { background: #ddd; padding: 10px; margin: 10px 0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background: #007bff; color: white; }
        .remove-btn { background: red; color: white; padding: 5px 10px; border: none; cursor: pointer; border-radius: 5px; }
        .remove-btn:hover { background: darkred; }
        .popup, .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; }
        .popup-content { background: white; padding: 20px; border-radius: 8px; width: 40%; position: relative; text-align: center; }
        .close-popup { position: absolute; top: 10px; right: 10px; font-size: 1.5em; cursor: pointer; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>Remove Books</h1>
        </div>

        <div class="summary">
            <strong>Summary:</strong> Available Books: <?php echo $availableCount; ?> | Archived Books: <?php echo $archivedCount; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Book Name</th>
                    <th>Knowledge Type</th>
                    <th>Book Image</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($activeBooks)) { ?>
                    <tr><td colspan="4">No active books available.</td></tr>
                <?php } else { ?>
                    <?php foreach ($activeBooks as $book) { ?>
                        <tr>
                            <td><?php echo $book['Book_Name']; ?></td>
                            <td><?php echo $book['Knowledge_Type']; ?></td>
                            <td><img src="<?php echo $book['Book_image']; ?>" alt="Book Image" width="50"></td>
                            <td>
                                <button class="remove-btn" onclick="openPopup(<?php echo $book['ID']; ?>)">Remove</button>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="overlay" id="removeBookPopup">
        <div class="popup-content">
            <span class="close-popup" onclick="closePopup()">&times;</span>
            <h2>Remove Book</h2>
            <form method="POST">
                <input type="hidden" id="book_id" name="book_id">
                <textarea name="reason" id="reason" placeholder="Enter reason for removal" required></textarea><br>
                <button type="submit" name="remove_book">Confirm Removal</button>
            </form>
        </div>
    </div>

    <script>
        function openPopup(bookId) {
            document.getElementById('book_id').value = bookId;
            document.getElementById('removeBookPopup').style.display = 'flex';
        }

        function closePopup() {
            document.getElementById('removeBookPopup').style.display = 'none';
        }
    </script>

</body>
</html>
