function mostrarPreviewReducida(inputId, previewId, storageKey, maxWidth = 800, quality = 0.7) {
  const input = document.getElementById(inputId);
  const file = input.files[0];

  if (file && file.type.startsWith("image/")) {
    const reader = new FileReader();
    reader.onload = function (e) {
      const img = new Image();
      img.onload = function () {
        const canvas = document.createElement('canvas');
        const scaleSize = maxWidth / img.width;
        canvas.width = maxWidth;
        canvas.height = img.height * scaleSize;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

        const resizedDataUrl = canvas.toDataURL("image/jpeg", quality);

        const preview = document.getElementById(previewId);
        preview.src = resizedDataUrl;
        preview.classList.remove('hidden');

        localStorage.setItem(storageKey, resizedDataUrl);
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }
}
