<?php
session_start();
include '../Administraton/php/db_connection.php';

// Check login
if (!isset($_SESSION['email'])) {
    die("Unauthorized access");
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$bookId = intval($_GET['id']);

// Fetch book details
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = :id");
$stmt->execute([':id' => $bookId]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book || !file_exists($book['image_path'])) {
    die("Book not found");
}

// Log download
$stmt = $pdo->prepare("INSERT INTO downloads (book_id, user_email) VALUES (:book_id, :user_email)");
$stmt->execute([
    ':book_id' => $bookId,
    ':user_email' => $_SESSION['email']
]);

// Update download count
$pdo->prepare("UPDATE books SET download_count = download_count + 1 WHERE id = :id")
    ->execute([':id' => $bookId]);

// Serve file
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.basename($book['image_path']).'"');
readfile($book['image_path']);
exit;
?>
