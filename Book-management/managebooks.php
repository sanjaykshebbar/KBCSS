<?php
// Include the database connection
include '../Administraton/php/db_connection.php';

// Ensure folders exist
$bookDirectory = "../Book-management/books";
$archiveDirectory = "../Book-management/archived";

if (!file_exists($bookDirectory)) {
    mkdir($bookDirectory, 0777, true);
}
if (!file_exists($archiveDirectory)) {
    mkdir($archiveDirectory, 0777, true);
}


// Function to generate the next Book ID
define('BOOK_ID_PREFIX', 'BK');
function generateBookID($pdo) {
    $query = "SELECT id FROM books ORDER BY created_at DESC LIMIT 1";
    $stmt = $pdo->query($query);
    $lastBook = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $nextID = $lastBook ? ((int) substr($lastBook['id'], 2) + 1) : 1;
    return BOOK_ID_PREFIX . str_pad($nextID, 5, '0', STR_PAD_LEFT);
}

// Handle book upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_book'])) {
    $title = $_POST['title'];
    $author = !empty($_POST['author']) ? $_POST['author'] : 'Mysterious';
    $description = $_POST['description'];
    $category = $_POST['category'];
    $bookFile = $_FILES['book_file'];

    $uploadPath = $bookDirectory . '/' . $title;
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    $filePath = $uploadPath . '/' . basename($bookFile['name']);
    if (move_uploaded_file($bookFile['tmp_name'], $filePath)) {
        $sql = "INSERT INTO books (title, author, description, category, image_path, status, created_at) 
                VALUES (:title, :author, :description, :category, :image_path, 'Available', NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':author' => $author,
            ':description' => $description,
            ':category' => $category,
            ':image_path' => $filePath,
        ]);
        echo "<script>alert('Book uploaded successfully!');</script>";
    } else {
        echo "<script>alert('Failed to upload book.');</script>";
    }
}

// Handle book archiving
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_book'])) {
    $bookId = $_POST['archive_book_id'];

    // Get book info
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($book) {
        $sourcePath = $book['image_path'];
        $fileName = basename($sourcePath);
        $newDir = $archiveDirectory . '/' . $book['title'];

        if (!file_exists($newDir)) {
            mkdir($newDir, 0777, true);
        }

        $destinationPath = $newDir . '/' . $fileName;

        // Move the file
        if (file_exists($sourcePath) && rename($sourcePath, $destinationPath)) {
            // Remove book record
            $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$bookId]);
            echo "<script>alert('Book archived successfully!'); window.location.reload();</script>";
        } else {
            echo "<script>alert('Archiving failed. File not found or permission issue.');</script>";
        }
    }
}


// Fetch all books
$sql = "SELECT * FROM books";
$stmt = $pdo->query($sql);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories for dropdown
$categorySql = "SELECT * FROM BookCategories";
$categoryStmt = $pdo->query($categorySql);
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.5.207/pdf.min.js"></script>
    <script src="pdfThumbnails.js"></script>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
        }
        .header button {
            background-color: #0056b3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .header button:hover {
            background-color: #003f7f;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .book-card {
            width: 150px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .book-card img {
            width: 120px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .book-card h3 {
            font-size: 1em;
            margin: 10px 0;
        }
        .popup, .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .popup-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 40%;
            position: relative;
        }
        .close-popup {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5em;
            color: #333;
            cursor: pointer;
        }
        
    /* Ensure the popup-content container has proper width and padding */
.popup-content {
    background: white;
    padding: 20px;
    border-radius: 12px;
    width: 90%;
    max-width: 400px; /* Prevents it from being too wide */
    position: relative;
    box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3);
}

/* Upload Book Form Styling */
.popup-content form {
    display: flex;
    flex-direction: column;
    gap: 15px;
    width: 100%; /* Ensures form elements stay within the container */
}

/* Input, textarea, and select field styling */
.popup-content input,
.popup-content textarea,
.popup-content select {
    width: calc(100% - 24px); /* Adjusts width to fit container while considering padding */
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 1rem;
    transition: 0.3s;
    box-sizing: border-box; /* Prevents overflow */
    margin: auto; /* Centers elements if needed */
}

/* Prevents textareas from resizing beyond the container */
.popup-content textarea {
    resize: none;
    height: 80px;
}

/* Focus effect */
.popup-content input:focus,
.popup-content textarea:focus,
.popup-content select:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* Submit button styling */
.popup-content button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: bold;
    text-transform: uppercase;
    transition: 0.3s;
    width: 100%; /* Ensures button width matches input fields */
}

.popup-content button:hover {
    background-color: #0056b3;
}
.pdf-preview {
    width: 120px;
    height: 150px;
    border: 1px solid #ddd;
    border-radius: 5px;
    object-fit: cover;
    background: #f0f0f0;
    cursor: pointer;
}


    </style>
</head>
<body>

    <div class="header">
        <h1>Manage Books</h1>
        <button onclick="openPopup('addBookPopup')">Add Book</button>
        
        
    </div>

    <div class="container">
        <?php if (empty($books)) { ?>
            <p>No books available. Add some books to get started.</p>
        <?php } else { ?>
            <?php foreach ($books as $book) { 
                $thumbnailSrc = pathinfo($book['image_path'], PATHINFO_EXTENSION) === 'pdf' ? 'pdf.png' : $book['image_path'];
            ?>
                <div class="book-card">
                    <img data-pdf-thumbnail-file="<?php echo $book['image_path']; ?>" src="<?php echo $thumbnailSrc; ?>" alt="<?php echo $book['title']; ?>">
                    <h3><?php echo $book['title']; ?></h3>
                    <p>Downloaded <?php echo $book['download_count']; ?> times</p>
                    <button onclick="openPopup('bookDetailsPopup', <?php echo $book['id']; ?>)">View Details</button>
                    <button onclick="openArchivePopup(<?php echo $book['id']; ?>)">Archive</button>
                    <button onclick="window.open('pdf-viewer.php?file=<?php echo urlencode($book['image_path']); ?>&id=<?php echo $book['id']; ?>', '_blank')">Open PDF</button>
                </div>
            <?php } ?>
        <?php } ?>
    </div>

    <div class="overlay" id="addBookPopup">
        <div class="popup-content">
            <span class="close-popup" onclick="closePopup('addBookPopup')">&times;</span>
            <h2>Add Book</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Book Name" required>
                <input type="text" name="author" placeholder="Author">
                <textarea name="description" placeholder="Description" maxlength="150" required></textarea>
                <select name="category" required>
                    <?php foreach ($categories as $category) { ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo $category['sub_category']; ?></option>
                    <?php } ?>
                </select>
                <input type="file" name="book_file" required>
                <button type="submit" name="upload_book">Upload</button>
            </form>
        </div>
    </div>

    <div class="overlay" id="bookDetailsPopup">
        <div class="popup-content">
            <span class="close-popup" onclick="closePopup('bookDetailsPopup')">&times;</span>
            <div id="bookDetailsContent"></div>
        </div>
    </div>

    <!-- Archive book Popup-->
    <div class="overlay" id="archiveBookPopup">
    <div class="popup-content">
        <span class="close-popup" onclick="closePopup('archiveBookPopup')">&times;</span>
        <h2>Archive Book</h2>
        <p>Are you sure you want to archive this book?</p>
        <form method="POST">
            <input type="hidden" name="archive_book_id" id="archive_book_id">
            <button type="submit" name="archive_book">Yes, Archive</button>
        </form>
    </div>
</div>


    <script>
       // Open Popup and Load Book Details
function openPopup(id, bookId = null) {
    let popup = document.getElementById(id);
    popup.style.display = 'flex';  // Ensure the popup is visible

    if (bookId) {
        fetch('getBookDetails.php?id=' + bookId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('bookDetailsContent').innerHTML = data;
            })
            .catch(error => console.error("Error fetching book details:", error));
    }
}

// Close Popup
function closePopup(id) {
    document.getElementById(id).style.display = 'none';
}

//Book archiving
function openArchivePopup(bookId) {
    document.getElementById('archive_book_id').value = bookId;
    document.getElementById('archiveBookPopup').style.display = 'flex';
}

// Auto-check for missing books every 10 seconds
setInterval(() => {
    fetch('checkBooks.php')
        .then(response => response.text())
        .then(html => {
            document.querySelector('.container').innerHTML = html;
        });
}, 10000); // every 10 seconds


// Render PDF Thumbnails
document.querySelectorAll('.pdf-preview').forEach(canvas => {
    const url = canvas.getAttribute('data-pdf');
    if (url && url.endsWith('.pdf')) {
        const loadingTask = pdfjsLib.getDocument(url);
        loadingTask.promise.then(pdf => {
            pdf.getPage(1).then(page => {
                const viewport = page.getViewport({ scale: 1 });
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                page.render(renderContext);
            });
        }).catch(error => {
            console.error("Error rendering PDF preview:", error);
        });
    }
});

    </script>
    

</body>
</html>
