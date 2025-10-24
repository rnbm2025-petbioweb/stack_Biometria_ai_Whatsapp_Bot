function mostrarPreview(inputId, previewId) {
  const input = document.getElementById(inputId);
  const preview = document.getElementById(previewId);

  if (!input || !preview) return;

  input.addEventListener('change', () => {
    const file = input.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = e => {
        preview.innerHTML = `<img src="${e.target.result}" class="w-32 mx-auto mt-2 rounded shadow" />`;
        localStorage.setItem(inputId, e.target.result);
      };
      reader.readAsDataURL(file);
    }
  });

  const stored = localStorage.getItem(inputId);
  if (stored) {
    preview.innerHTML = `<img src="${stored}" class="w-32 mx-auto mt-2 rounded shadow" />`;
  }
}
