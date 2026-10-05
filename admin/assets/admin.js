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

const adminListTools = document.querySelector('[data-admin-list-tools]');
if (adminListTools) {
  const rows = Array.from(document.querySelectorAll('[data-admin-list-row]'));
  const searchInput = adminListTools.querySelector('[data-admin-list-search]');
  const statusFilter = adminListTools.querySelector('[data-admin-list-status]');
  const categoryFilter = adminListTools.querySelector('[data-admin-list-category]');
  const countLabel = adminListTools.querySelector('[data-admin-list-count]');
  const emptyRow = document.querySelector('[data-admin-list-empty]');
  const formatNumber = (number) => new Intl.NumberFormat('fa-IR').format(number);

  const filterAdminList = () => {
    const query = (searchInput?.value || '').trim().toLocaleLowerCase();
    const status = statusFilter?.value || '';
    const category = categoryFilter?.value || '';
    let visibleCount = 0;

    rows.forEach((row) => {
      const matchesQuery = !query || row.textContent.toLocaleLowerCase().includes(query);
      const matchesStatus = status === '' || row.dataset.published === status;
      const matchesCategory = category === '' || row.dataset.category === category;
      row.hidden = !(matchesQuery && matchesStatus && matchesCategory);
      if (!row.hidden) visibleCount += 1;
    });

    if (emptyRow) emptyRow.hidden = visibleCount > 0;
    if (countLabel) countLabel.textContent = `نمایش ${formatNumber(visibleCount)} از ${formatNumber(rows.length)} مورد`;
  };

  searchInput?.addEventListener('input', filterAdminList);
  statusFilter?.addEventListener('change', filterAdminList);
  categoryFilter?.addEventListener('change', filterAdminList);
  filterAdminList();
}

document.querySelectorAll('[data-slide-sortable]').forEach((list) => {
  let activeItem = null;
  let startX = 0;
  let startY = 0;
  let isDragging = false;
  let activePointerId = null;

  const slideItems = () => Array.from(list.querySelectorAll('[data-slide-sort-item]'));
  const updateSlideLabels = () => {
    const formatNumber = (number) => new Intl.NumberFormat('fa-IR').format(number);
    slideItems().forEach((item, index) => {
      const position = formatNumber(index + 1);
      const numberLabel = item.querySelector('.slide-preview-index');
      const handle = item.querySelector('[data-slide-drag-handle]');
      if (numberLabel) numberLabel.textContent = `اسلاید ${position}`;
      if (handle) handle.setAttribute('aria-label', `جابجایی اسلاید ${position}`);
    });
  };

  const moveItemBy = (item, offset) => {
    const items = slideItems();
    const index = items.indexOf(item);
    const targetIndex = index + offset;
    if (targetIndex < 0 || targetIndex >= items.length) return;
    const target = items[targetIndex];
    if (offset < 0) list.insertBefore(item, target);
    else list.insertBefore(target, item);
    updateSlideLabels();
  };

  list.addEventListener('pointerdown', (event) => {
    const handle = event.target.closest('[data-slide-drag-handle]');
    if (!handle || (event.pointerType === 'mouse' && event.button !== 0)) return;
    activeItem = handle.closest('[data-slide-sort-item]');
    if (!activeItem) return;
    startX = event.clientX;
    startY = event.clientY;
    activePointerId = event.pointerId;
    isDragging = false;
    handle.setPointerCapture?.(event.pointerId);
  });

  list.addEventListener('pointermove', (event) => {
    if (!activeItem || event.pointerId !== activePointerId) return;
    if (!isDragging && Math.hypot(event.clientX - startX, event.clientY - startY) < 5) return;
    isDragging = true;
    activeItem.classList.add('is-dragging');
    activeItem.setAttribute('aria-grabbed', 'true');
    const target = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-slide-sort-item]');
    if (target && target !== activeItem && list.contains(target)) {
      const targetRect = target.getBoundingClientRect();
      const itemRect = activeItem.getBoundingClientRect();
      const sameRow = Math.abs(itemRect.top - targetRect.top) < targetRect.height * .45;
      const isRtl = getComputedStyle(list).direction === 'rtl';
      const insertBefore = sameRow
        ? (isRtl ? event.clientX > targetRect.left + targetRect.width / 2 : event.clientX < targetRect.left + targetRect.width / 2)
        : event.clientY < targetRect.top + targetRect.height / 2;
      list.insertBefore(activeItem, insertBefore ? target : target.nextSibling);
      updateSlideLabels();
    }
    event.preventDefault();
  });

  const finishDrag = (event) => {
    if (!activeItem || (event && event.pointerId !== activePointerId)) return;
    activeItem.classList.remove('is-dragging');
    activeItem.removeAttribute('aria-grabbed');
    activeItem = null;
    activePointerId = null;
    isDragging = false;
    updateSlideLabels();
  };
  list.addEventListener('pointerup', finishDrag);
  list.addEventListener('pointercancel', finishDrag);
  list.addEventListener('lostpointercapture', finishDrag);

  list.querySelectorAll('[data-slide-drag-handle]').forEach((handle) => {
    handle.addEventListener('keydown', (event) => {
      const item = handle.closest('[data-slide-sort-item]');
      if (!item) return;
      if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
        event.preventDefault();
        moveItemBy(item, -1);
      } else if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
        event.preventDefault();
        moveItemBy(item, 1);
      }
    });
  });
  updateSlideLabels();
});
