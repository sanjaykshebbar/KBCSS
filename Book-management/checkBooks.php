<?php
include '../Administraton/php/db_connection.php';

$bookDirectory = "../Book-management/books";

// Get existing books
$stmt = $pdo->query("SELECT * FROM books");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output only books whose files exist
if (empty($books)) {
    echo "<p>No books available. Add some books to get started.</p>";
} else {
    foreach ($books as $book) {
        if (!file_exists($book['image_path'])) {
            // Optional: auto remove missing books from DB
            $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$book['id']]);
            continue;
        }

        $thumbnailSrc = pathinfo($book['image_path'], PATHINFO_EXTENSION) === 'pdf' ? 'pdf.png' : $book['image_path'];
        echo '
            <div class="book-card">
                <img data-pdf-thumbnail-file="' . $book['image_path'] . '" src="' . $thumbnailSrc . '" alt="' . htmlspecialchars($book['title']) . '">
                <h3>' . htmlspecialchars($book['title']) . '</h3>
                <button onclick="openPopup(\'bookDetailsPopup\', ' . $book['id'] . ')">View Details</button>
                <button onclick="openArchivePopup(' . $book['id'] . ')">Archive</button>
            </div>
        ';
    }
}
?>
