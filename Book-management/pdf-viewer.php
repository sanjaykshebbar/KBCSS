<?php
session_start();

if (!isset($_GET['file'])) {
    die("No PDF file specified.");
}

$pdfFile = $_GET['file'];

// Set baseDir to 'books' folder
$baseDir = realpath(__DIR__ . '/books');
$realPath = realpath(__DIR__ . '/' . $pdfFile);

// Secure check: must be inside books/
if ($realPath === false || strpos($realPath, $baseDir) !== 0) {
    die("Invalid file (unsafe path).");
}

if (!file_exists($realPath)) {
    die("File not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Viewer</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.5.207/pdf.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .toolbar {
            background-color: #007bff;
            padding: 10px;
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
            width: 100%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .toolbar button {
            background-color: #0056b3;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 14px;
        }

        .toolbar button:hover {
            background-color: #003f7f;
        }

        #pdf-container {
            margin-top: 15px;
            text-align: center;
        }

        canvas {
            border: 1px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            background: white;
        }
    </style>
</head>

<body>

    <div class="toolbar">
        <button onclick="prevPage()">Previous</button>
        <button onclick="nextPage()">Next</button>
        <button onclick="zoomOut()">Zoom Out</button>
        <button onclick="zoomIn()">Zoom In</button>
        <button onclick="downloadPDF()">Download</button>
        <button onclick="printPDF()">Print</button>
        <span>Page: <span id="page_num">1</span> / <span id="page_count">?</span></span>
    </div>

    <div id="pdf-container">
        <canvas id="pdf-render"></canvas>
    </div>

    <script>
        const url = "<?php echo htmlspecialchars($pdfFile); ?>";

        let pdfDoc = null,
            pageNum = 1,
            pageRendering = false,
            pageNumPending = null,
            scale = 1,
            canvas = document.getElementById('pdf-render'),
            ctx = canvas.getContext('2d');

        pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            document.getElementById('page_count').textContent = pdfDoc.numPages;
            renderPage(pageNum);
        });

        function renderPage(num) {
            pageRendering = true;
            pdfDoc.getPage(num).then(function(page) {
                const viewport = page.getViewport({ scale: scale });
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };
                const renderTask = page.render(renderContext);

                renderTask.promise.then(function() {
                    pageRendering = false;
                    if (pageNumPending !== null) {
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    }
                });
            });

            document.getElementById('page_num').textContent = num;
        }

        function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        }

        function prevPage() {
            if (pageNum <= 1) return;
            pageNum--;
            queueRenderPage(pageNum);
        }

        function nextPage() {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum++;
            queueRenderPage(pageNum);
        }

        function zoomIn() {
            scale += 0.2;
            queueRenderPage(pageNum);
        }

        function zoomOut() {
            if (scale > 0.4) {
                scale -= 0.2;
                queueRenderPage(pageNum);
            }
        }

        function downloadPDF() {
            const link = document.createElement('a');
            link.href = url;
            link.download = url.split('/').pop();
            link.click();
        }

        function printPDF() {
            const printWindow = window.open(url);
            printWindow.addEventListener('load', () => {
                printWindow.print();
            });
        }
    </script>

</body>

</html>
