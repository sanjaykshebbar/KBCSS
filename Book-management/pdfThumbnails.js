document.addEventListener('DOMContentLoaded', function () {
    const thumbnails = document.querySelectorAll('img[data-pdf-thumbnail-file]');
    
    thumbnails.forEach(thumbnail => {
        const pdfFilePath = thumbnail.dataset.pdfThumbnailFile;

        // Load PDF and generate a thumbnail
        pdfjsLib.getDocument(pdfFilePath).promise.then(pdf => {
            pdf.getPage(1).then(page => {
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                const viewport = page.getViewport({ scale: 0.3 }); // Adjust scale for smaller thumbnails

                canvas.width = viewport.width;
                canvas.height = viewport.height;

                page.render({
                    canvasContext: context,
                    viewport: viewport
                }).promise.then(() => {
                    thumbnail.src = canvas.toDataURL();
                });
            });
        }).catch(error => {
            console.error('Error generating thumbnail:', error);
        });
    });

    // Handle modal close functionality
    const closeButton = document.querySelector('.close-button');
    const pdfModal = document.querySelector('.pdf-modal');

    if (closeButton && pdfModal) {
        closeButton.addEventListener('click', () => {
            pdfModal.style.display = 'none';
        });
    }
});
