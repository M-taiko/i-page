document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.post-composer');
    if (!form) return;
    const find = selector => form.querySelector(selector);
    const value = name => form.elements[name]?.value || '';
    const preview = find('#composerPreview');
    let imageUrl;
    function update() {
        for (const [name, id] of [['title', 'previewTitle'], ['body', 'previewBody']]) {
            const element = find('#' + id);
            element.textContent = value(name) || element.dataset.empty;
        }
        find('#previewChannel').textContent = find('#channel_id').selectedOptions[0]?.textContent || '';
        preview.dataset.style = value('card_style') || 'compact';
        const badge = find('#previewBadge');
        badge.hidden = !value('badge_key');
        badge.textContent = value('badge_label') || find('#badge_key').selectedOptions[0]?.textContent || '';
        find('#previewPin').hidden = !find('#pinned').checked;
        find('#previewReactions').textContent = [...form.querySelectorAll('[name="reactions[]"]:checked')].map(input => input.nextElementSibling.textContent.trim().split(' ')[0]).join(' ') || '👍';
        const extra = find('#previewExtra');
        extra.replaceChildren();
        const format = value('post_format');
        if (format === 'poll') {
            form.querySelectorAll('[name="poll_options[]"]').forEach(input => {
                if (!input.value.trim()) return;
                const row = document.createElement('div');
                row.className = 'preview-option'; row.textContent = '○ ' + input.value; extra.append(row);
            });
        } else if (format === 'rating') {
            extra.textContent = '☆ ☆ ☆ ☆ ☆';
        } else if (format === 'event') {
            extra.textContent = ['📅 ' + value('event_starts_at').replace('T', ' '), value('event_location')].filter(Boolean).join(' · ');
        }
    }
    function updateImage() {
        if (imageUrl) URL.revokeObjectURL(imageUrl);
        const file = value('post_format') === 'gallery' ? find('#gallery').files[0] : find('#image').files[0];
        const img = find('#previewImage');
        img.hidden = !file || !file.type.startsWith('image/');
        if (!img.hidden) { imageUrl = URL.createObjectURL(file); img.src = imageUrl; } else img.removeAttribute('src');
    }
    form.addEventListener('input', update);
    form.addEventListener('change', () => { update(); updateImage(); });
    form.addEventListener('click', event => {
        if (event.target.closest('.poll-remove')) queueMicrotask(update);
    });
    update();
});
