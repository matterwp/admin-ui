function initMediaControls() {
	function setMediaFieldState(uid, hasImage) {
		const field = document.querySelector(`[data-media-field="${uid}"]`);
		const remove = field?.querySelector('[data-media-remove]');

		if (field) {
			field.classList.toggle('has-image', hasImage);
			field.classList.toggle('is-empty', !hasImage);
		}

		if (remove) {
			remove.hidden = !hasImage;
		}
	}

	function dispatchMediaEvent(uid, eventName, detail = {}) {
		const field = document.querySelector(`[data-media-field="${uid}"]`);

		if (!field) {
			return;
		}

		field.dispatchEvent(new CustomEvent(eventName, {
			bubbles: true,
			detail: {
				uid,
				field,
				...detail,
			},
		}));
	}

	function getAttachmentPreviewUrl(attachment, preferredSize) {
		if (preferredSize && attachment.sizes?.[preferredSize]?.url) {
			return attachment.sizes[preferredSize].url;
		}

		return attachment.sizes?.thumbnail?.url || attachment.url;
	}

	document.addEventListener('click', event => {
		const button = event.target.closest('[data-media-target]');

		if (button) {
			event.preventDefault();
			const input = document.querySelector(`[data-media-input="${button.dataset.mediaTarget}"]`);
			const preview = document.querySelector(`[data-media-preview="${button.dataset.mediaTarget}"]`);

			if (!input) {
				return;
			}

			const frame = wp.media({
				library: { type: button.dataset.mediaLibraryType || 'image' },
				title: button.dataset.mediaTitle || 'Select Image',
				button: { text: button.dataset.mediaButton || 'Use Image' },
				multiple: false,
			});

			const currentValue = parseInt(input.value, 10);
			if (currentValue) {
				frame.on('open', () => {
					const selection = frame.state().get('selection');
					const attachment = wp.media.attachment(currentValue);
					attachment.fetch();
					selection.add(attachment ? [attachment] : []);
				});
			}

			frame.on('select', () => {
				const attachment = frame.state().get('selection').first().toJSON();
				const previewSize = button.dataset.mediaPreviewSize || 'thumbnail';
				const previewUrl = getAttachmentPreviewUrl(attachment, previewSize);
				input.value = attachment.id;

				if (preview) {
					const img = preview.querySelector('img') || document.createElement('img');
					img.src = previewUrl;
					img.alt = attachment.alt || '';
					preview.innerHTML = '';
					preview.appendChild(img);
					preview.classList.add('has-image');
				}

				setMediaFieldState(button.dataset.mediaTarget, true);
				input.dispatchEvent(new Event('change', { bubbles: true }));
				dispatchMediaEvent(button.dataset.mediaTarget, 'mwp:media-selected', {
					attachment,
					input,
					preview,
				});
			});

			frame.open();
			return;
		}

		const removeBtn = event.target.closest('[data-media-remove]');

		if (removeBtn) {
			event.preventDefault();
			const input = document.querySelector(`[data-media-input="${removeBtn.dataset.mediaRemove}"]`);
			const preview = document.querySelector(`[data-media-preview="${removeBtn.dataset.mediaRemove}"]`);

			if (input) {
				input.value = '';
			}

			if (preview) {
				preview.innerHTML = '';
				preview.classList.remove('has-image');
			}

			setMediaFieldState(removeBtn.dataset.mediaRemove, false);

			if (input) {
				input.dispatchEvent(new Event('change', { bubbles: true }));
			}

			dispatchMediaEvent(removeBtn.dataset.mediaRemove, 'mwp:media-removed', {
				input,
				preview,
			});
		}
	});
}

export { initMediaControls };
