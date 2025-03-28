<?php
include '../Administraton/php/db_connection.php';

if (isset($_GET['id'])) {
    $bookId = $_GET['id'];

    // Get the book details
    $sql = "SELECT * FROM books WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($book) {
        $oldPath = $book['image_path'];
        $newPath = str_replace('books', 'archived', $oldPath); // Move to archived folder

        // Ensure archived folder exists
        if (!file_exists(dirname($newPath))) {
            mkdir(dirname($newPath), 0777, true);
        }

        // Move file
        if (rename($oldPath, $newPath)) {
            // Update DB status
            $updateSql = "UPDATE books SET status = 'Archived', image_path = :newPath WHERE id = :id";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute(['newPath' => $newPath, 'id' => $bookId]);

            echo "Book archived successfully!";
        } else {
            echo "Failed to move the book file.";
        }
    } else {
        echo "Book not found.";
    }
} else {
    echo "Invalid request.";
}
?>
