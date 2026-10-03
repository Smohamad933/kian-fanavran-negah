document.querySelectorAll('form[data-confirm]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    if (!window.confirm(form.dataset.confirm || 'ادامه می‌دهید؟')) event.preventDefault();
  });
});

document.querySelectorAll('.color-control input[type="color"]').forEach((colorInput) => {
  colorInput.addEventListener('input', () => {
    const textInput = colorInput.parentElement.querySelector('input[type="text"]');
    if (textInput) textInput.value = colorInput.value.toUpperCase();
  });
});

document.querySelectorAll('.admin-nav a.is-active').forEach((activeLink) => {
  if (activeLink.parentElement.scrollWidth > activeLink.parentElement.clientWidth) {
    activeLink.scrollIntoView({ block: 'nearest', inline: 'center' });
  }
});
