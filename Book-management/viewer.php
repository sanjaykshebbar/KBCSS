<?php
session_start();
if (!isset($_GET['file'])) {
    die("No file specified.");
}
$file = urldecode($_GET['file']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PDF Reader</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.5.207/pdf.min.js"></script>
    <style>
        body { margin:0; font-family: Arial, sans-serif; display: flex; }
        .sidebar { width: 120px; overflow-y: auto; background: #f5f5f5; padding: 5px; }
        .sidebar canvas { cursor: pointer; margin-bottom: 5px; border: 1px solid #ccc; }
        .viewer { flex: 1; background: #ddd; position: relative; display: flex; flex-direction: column; }
        .toolbar { background: #333; color: #fff; padding: 5px; display: flex; gap: 5px; align-items: center; }
        .toolbar button, .toolbar input { padding: 5px; border: none; border-radius: 3px; }
        .toolbar button:hover { background: #555; color: #fff; }
        #pdf-canvas { flex: 1; background: #fff; margin: auto; }
        .dark .viewer { background: #222; }
        .dark canvas { background: #333; }
        .dark .toolbar { background: #111; }
    </style>
</head>
<body>
    <div class="sidebar" id="thumbnail-sidebar"></div>
    <div class="viewer" id="viewer">
        <div class="toolbar">
            <button onclick="prevPage()">◀</button>
            <button onclick="nextPage()">▶</button>
            <span>Page <input type="number" id="page-num" value="1" min="1"> / <span id="page-count">?</span></span>
            <button onclick="zoomOut()">➖</button>
            <button onclick="zoomIn()">➕</button>
            <button onclick="fitToWidth()">Fit Width</button>
            <button onclick="fitToPage()">Fit Page</button>
            <button onclick="toggleDarkMode()">🌙</button>
            <button onclick="toggleFullscreen()">⛶</button>
            <a href="download.php?id=<?php echo htmlspecialchars($_GET['id']); ?>" target="_blank">
                <button>⬇ Download</button>
            </a>
            <button onclick="printPDF()">🖨 Print</button>
        </div>
        <canvas id="pdf-canvas"></canvas>
    </div>

<script>
const url = "<?php echo htmlspecialchars($file); ?>";
let pdfDoc, pageNum = 1, pageRendering = false, pageNumPending = null, scale = 1.2, canvas = document.getElementById('pdf-canvas'), ctx = canvas.getContext('2d');

// Load PDF
pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
    pdfDoc = pdfDoc_;
    document.getElementById('page-count').textContent = pdfDoc.numPages;
    renderPage(pageNum);
    renderThumbnails();
});

function renderPage(num) {
    pageRendering = true;
    pdfDoc.getPage(num).then(function(page) {
        let viewport = page.getViewport({scale: scale});
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        let renderContext = { canvasContext: ctx, viewport: viewport };
        let renderTask = page.render(renderContext);
        renderTask.promise.then(function() {
            pageRendering = false;
            if (pageNumPending !== null) { renderPage(pageNumPending); pageNumPending = null; }
        });
    });
    document.getElementById('page-num').value = num;
}

function renderThumbnails() {
    const sidebar = document.getElementById('thumbnail-sidebar');
    for (let i = 1; i <= pdfDoc.numPages; i++) {
        pdfDoc.getPage(i).then(page => {
            const thumbCanvas = document.createElement('canvas');
            const viewport = page.getViewport({scale: 0.2});
            thumbCanvas.height = viewport.height;
            thumbCanvas.width = viewport.width;
            page.render({ canvasContext: thumbCanvas.getContext('2d'), viewport: viewport });
            thumbCanvas.onclick = () => { pageNum = page.pageNumber; renderPage(pageNum); };
            sidebar.appendChild(thumbCanvas);
        });
    }
}

// Controls
function prevPage() { if (pageNum <= 1) return; pageNum--; renderPage(pageNum); }
function nextPage() { if (pageNum >= pdfDoc.numPages) return; pageNum++; renderPage(pageNum); }
document.getElementById('page-num').addEventListener('change', e => {
    let val = parseInt(e.target.value); if(val >= 1 && val <= pdfDoc.numPages) { pageNum = val; renderPage(pageNum); }
});
function zoomIn() { scale += 0.2; renderPage(pageNum); }
function zoomOut() { scale = Math.max(0.4, scale - 0.2); renderPage(pageNum); }
function fitToWidth() { scale = (window.innerWidth - 150) / canvas.width; renderPage(pageNum); }
function fitToPage() { scale = (window.innerHeight - 50) / canvas.height; renderPage(pageNum); }
function toggleDarkMode() { document.body.classList.toggle('dark'); }
function toggleFullscreen() { if (!document.fullscreenElement) { document.documentElement.requestFullscreen(); } else { document.exitFullscreen(); } }
function printPDF() { window.open(url); }
</script>

</body>
</html>
