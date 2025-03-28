<?php
include '../Administraton/php/db_connection.php';

if (isset($_GET['id'])) {
    $bookId = $_GET['id'];

    // Fetch book details
    $sql = "SELECT books.*, BookCategories.sub_category 
            FROM books 
            JOIN BookCategories ON books.category = BookCategories.id 
            WHERE books.id = :bookId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':bookId' => $bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($book) {
        $statusClass = $book['status'] === 'Available' ? 'green-text' : 'grey-text';

        // Set the image path, fallback for PDFs and missing images
        $imagePath = $book['image_path'];
        if (pathinfo($imagePath, PATHINFO_EXTENSION) === 'pdf') {
            $imagePath = 'pdf.png'; // Default PDF icon
        } elseif (empty($imagePath) || !file_exists($imagePath)) {
            $imagePath = 'path/to/default-image.jpg'; // Default image
        }

        // Book details content (WITHOUT popup div)
        echo '<div style="display: flex; flex-wrap: wrap;">';
        echo '<div style="flex: 1; text-align: center; margin-right: 20px;">';
        echo '<img src="' . htmlspecialchars($imagePath) . '" style="max-width: 100%; height: auto; margin-bottom: 20px;" alt="' . htmlspecialchars($book['title']) . '">';
        echo '</div>';
        echo '<div style="flex: 2; min-width: 300px;">';
        echo '<h1>' . htmlspecialchars($book['title']) . '</h1>';
        echo '<p><strong>Author:</strong> ' . htmlspecialchars($book['author']) . '</p>';
        echo '<p><strong>Category:</strong> ' . htmlspecialchars($book['sub_category']) . '</p>';
        echo '<p><strong>Book Status:</strong> <span class="' . $statusClass . '">' . htmlspecialchars($book['status']) . '</span></p>';
        echo '<p><strong>Description:</strong> ' . htmlspecialchars($book['description']) . '</p>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<p>Book not found.</p>';
    }
}
?>
