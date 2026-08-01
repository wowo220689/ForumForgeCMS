(function () {
  function decodeValue(value) {
    return String(value || '').replace(/\\n/g, '\n');
  }

  function targetTextarea(toolbar) {
    return document.getElementById(toolbar.dataset.editorTarget || '');
  }

  function insertAround(textarea, open, close, placeholder) {
    const start = textarea.selectionStart || 0;
    const end = textarea.selectionEnd || 0;
    const value = textarea.value;
    const selected = value.slice(start, end) || placeholder || '';
    textarea.value = value.slice(0, start) + open + selected + close + value.slice(end);
    textarea.focus();
    textarea.setSelectionRange(start + open.length, start + open.length + selected.length);
  }

  function insertText(textarea, text, close) {
    const start = textarea.selectionStart || 0;
    const end = textarea.selectionEnd || 0;
    const value = textarea.value;
    const content = decodeValue(text) + (close ? decodeValue(close) : '');
    textarea.value = value.slice(0, start) + content + value.slice(end);
    textarea.focus();
    textarea.setSelectionRange(start + content.length, start + content.length);
  }

  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function plainPreview(text) {
    return escapeHtml(text)
      .replace(/\[b\](.*?)\[\/b\]/gis, '<strong>$1</strong>')
      .replace(/\[i\](.*?)\[\/i\]/gis, '<em>$1</em>')
      .replace(/\[u\](.*?)\[\/u\]/gis, '<u>$1</u>')
      .replace(/\[s\](.*?)\[\/s\]/gis, '<s>$1</s>')
      .replace(/\[quote(?:=[^\]]+)?\]([\s\S]*?)\[\/quote\]/gi, '<blockquote>$1</blockquote>')
      .replace(/\[code\]([\s\S]*?)\[\/code\]/gi, '<pre><code>$1</code></pre>')
      .replace(/\n/g, '<br>');
  }

  function closeEmoticonPickers(except) {
    document.querySelectorAll('[data-emoticon-picker]').forEach(function (picker) {
      if (picker !== except) {
        picker.hidden = true;
      }
    });
  }

  document.addEventListener('click', function (event) {
    const emoticonButton = event.target.closest('.forum-emoticon-picker button[data-emoticon]');
    if (emoticonButton) {
      const toolbar = emoticonButton.closest('.forum-editor-toolbar');
      const textarea = targetTextarea(toolbar);
      if (textarea) {
        insertText(textarea, emoticonButton.dataset.emoticon + ' ');
      }
      const picker = emoticonButton.closest('[data-emoticon-picker]');
      if (picker) {
        picker.hidden = true;
      }
      return;
    }

    const button = event.target.closest('.forum-editor-toolbar button');
    if (!button) {
      if (!event.target.closest('.forum-editor-toolbar')) {
        closeEmoticonPickers();
      }
      return;
    }

    const toolbar = button.closest('.forum-editor-toolbar');
    const textarea = targetTextarea(toolbar);
    if (!textarea) {
      return;
    }

    if (button.dataset.wrap) {
      insertAround(textarea, decodeValue(button.dataset.wrap), decodeValue(button.dataset.close || ''), 'tekst');
      return;
    }

    if (button.dataset.insert) {
      insertText(textarea, button.dataset.insert);
      return;
    }

    if (button.dataset.line) {
      insertText(textarea, button.dataset.line, button.dataset.close || '');
      return;
    }

    if (button.dataset.prompt === 'url') {
      const url = window.prompt('Adres linku:');
      if (url) {
        insertAround(textarea, '[url=' + url + ']', '[/url]', 'opis linku');
      }
      return;
    }

    if (button.dataset.prompt === 'image') {
      const url = window.prompt('Adres obrazka:');
      if (url) {
        insertText(textarea, '[img]' + url + '[/img]');
      }
      return;
    }

    if (button.dataset.action === 'emoticons') {
      const picker = toolbar.querySelector('[data-emoticon-picker]');
      if (picker) {
        const shouldOpen = picker.hidden;
        closeEmoticonPickers(picker);
        picker.hidden = !shouldOpen;
      }
      return;
    }

    if (button.dataset.action === 'clear') {
      if (window.confirm('Wyczyścić treść pola?')) {
        textarea.value = '';
        textarea.focus();
      }
      return;
    }

    if (button.dataset.action === 'preview') {
      const preview = Array.from(document.querySelectorAll('[data-editor-preview]'))
        .find((element) => element.dataset.editorPreview === textarea.id);
      if (preview) {
        preview.hidden = !preview.hidden;
        preview.innerHTML = plainPreview(textarea.value);
      }
    }
  });

  document.addEventListener('change', function (event) {
    const select = event.target.closest('.forum-editor-toolbar select');
    if (!select || !select.value) {
      return;
    }

    const toolbar = select.closest('.forum-editor-toolbar');
    const textarea = targetTextarea(toolbar);
    if (!textarea) {
      return;
    }

    if (select.dataset.style === 'color') {
      insertAround(textarea, '[color=' + select.value + ']', '[/color]', 'tekst');
    }

    if (select.dataset.style === 'size') {
      insertAround(textarea, '[size=' + select.value + ']', '[/size]', 'tekst');
    }

    select.value = '';
  });
})();
