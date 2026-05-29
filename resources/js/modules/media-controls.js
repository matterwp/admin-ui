function initMediaControls() {
	document.querySelectorAll('[data-media-target]').forEach(button => {
		const input = document.querySelector(`[data-media-input="${button.dataset.mediaTarget}"]`);
		const preview = document.querySelector(`[data-media-preview="${button.dataset.mediaTarget}"]`);

		if (!input) {
			return;
		}

		button.addEventListener('click', event => {
			event.preventDefault();

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
		});
	});

	document.querySelectorAll('[data-media-remove]').forEach(button => {
		button.addEventListener('click', event => {
			event.preventDefault();
			const input = document.querySelector(`[data-media-input="${button.dataset.mediaRemove}"]`);
			const preview = document.querySelector(`[data-media-preview="${button.dataset.mediaRemove}"]`);

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
		});
	});
}

export { initMediaControls };
