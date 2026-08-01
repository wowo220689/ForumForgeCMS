(function () {
  const inputs = document.querySelectorAll('input[type="file"][data-forum-image]');

  if (!inputs.length || typeof DataTransfer === 'undefined') {
    return;
  }

  function readImage(file) {
    return new Promise((resolve, reject) => {
      const url = URL.createObjectURL(file);
      const image = new Image();

      image.onload = () => {
        URL.revokeObjectURL(url);
        resolve(image);
      };
      image.onerror = () => {
        URL.revokeObjectURL(url);
        reject(new Error('Nie udało się odczytać obrazu.'));
      };
      image.src = url;
    });
  }

  function canvasToWebp(canvas) {
    return new Promise((resolve, reject) => {
      canvas.toBlob((blob) => {
        if (!blob) {
          reject(new Error('Przeglądarka nie potrafi zapisać obrazu jako WebP.'));
          return;
        }
        resolve(blob);
      }, 'image/webp', 0.86);
    });
  }

  async function convertInput(input) {
    const original = input.files && input.files[0];
    if (!original) {
      return;
    }

    if (input.dataset.forumImageProcessed === '1') {
      return;
    }

    const size = parseInt(input.dataset.forumSize || '160', 10);
    const fit = input.dataset.forumFit || 'cover';
    const image = await readImage(original);
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');

    canvas.width = size;
    canvas.height = size;
    context.clearRect(0, 0, size, size);

    if (fit === 'contain') {
      const scale = Math.min(size / image.naturalWidth, size / image.naturalHeight);
      const targetWidth = Math.round(image.naturalWidth * scale);
      const targetHeight = Math.round(image.naturalHeight * scale);
      const targetX = Math.floor((size - targetWidth) / 2);
      const targetY = Math.floor((size - targetHeight) / 2);
      context.drawImage(image, targetX, targetY, targetWidth, targetHeight);
    } else {
      const sourceSize = Math.min(image.naturalWidth, image.naturalHeight);
      const sourceX = Math.floor((image.naturalWidth - sourceSize) / 2);
      const sourceY = Math.floor((image.naturalHeight - sourceSize) / 2);
      context.drawImage(image, sourceX, sourceY, sourceSize, sourceSize, 0, 0, size, size);
    }

    const blob = await canvasToWebp(canvas);
    const filename = (original.name || 'image').replace(/\.[^.]+$/, '') + '.webp';
    const converted = new File([blob], filename, { type: 'image/webp' });
    const transfer = new DataTransfer();
    transfer.items.add(converted);
    input.files = transfer.files;
    input.dataset.forumImageProcessed = '1';
  }

  for (const input of inputs) {
    input.addEventListener('change', () => {
      delete input.dataset.forumImageProcessed;
    });

    const form = input.form;
    if (!form || form.dataset.forumImageSubmitReady === '1') {
      continue;
    }

    form.dataset.forumImageSubmitReady = '1';
    form.addEventListener('submit', async (event) => {
      if (form.dataset.forumImagesProcessed === '1') {
        return;
      }

      const formInputs = Array.from(form.querySelectorAll('input[type="file"][data-forum-image]'));
      const hasFiles = formInputs.some((field) => field.files && field.files.length);
      if (!hasFiles) {
        return;
      }

      event.preventDefault();
      const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');

      try {
        for (const field of formInputs) {
          await convertInput(field);
        }
        form.dataset.forumImagesProcessed = '1';
        if (submitter && typeof form.requestSubmit === 'function') {
          form.requestSubmit(submitter);
        } else {
          form.submit();
        }
      } catch (error) {
        alert(error.message || 'Nie udało się przygotować obrazu do wysłania.');
      }
    });
  }
})();
