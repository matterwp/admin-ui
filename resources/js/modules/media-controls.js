function initMediaControls() {
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
				library: { type: 'image' },
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
				input.value = attachment.id;

				if (preview) {
					const img = preview.querySelector('img') || document.createElement('img');
					img.src = attachment.sizes?.thumbnail?.url || attachment.url;
					img.alt = attachment.alt || '';
					preview.innerHTML = '';
					preview.appendChild(img);
					preview.classList.add('has-image');
				}

				input.dispatchEvent(new Event('change', { bubbles: true }));
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

			if (input) {
				input.dispatchEvent(new Event('change', { bubbles: true }));
			}
		}
	});
}

export { initMediaControls };
