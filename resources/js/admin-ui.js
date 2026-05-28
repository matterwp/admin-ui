function isHexColor(value) {
	return /^#[0-9a-f]{6}$/i.test(value);
}

function normalizeHexColor(value) {
	const color = value.trim();

	if (/^[0-9a-f]{6}$/i.test(color)) {
		return `#${color}`;
	}

	return color;
}

function initColorPickers() {
	document.querySelectorAll('[data-mwp-color-picker]').forEach(picker => {
		const swatch = picker.querySelector('[data-mwp-color-swatch]');
		const input = picker.querySelector('[data-mwp-color-input]');

		if (!swatch || !input) {
			return;
		}

		swatch.addEventListener('input', () => {
			input.value = swatch.value.toUpperCase();
			input.dispatchEvent(new Event('input', { bubbles: true }));
		});

		input.addEventListener('input', () => {
			const color = normalizeHexColor(input.value);

			if (isHexColor(color)) {
				swatch.value = color;
				input.value = color.toUpperCase();
			}
		});
	});
}

function initModals() {
	document.querySelectorAll('.mwp-modal-trigger').forEach(trigger => {
		const modal = trigger.nextElementSibling;

		if (!modal || !modal.matches('[data-mwp-modal]')) {
			return;
		}

		document.body.appendChild(modal);

		trigger.addEventListener('click', () => {
			modal.hidden = false;
			modal.querySelector('[data-mwp-modal-close]')?.focus();
		});
	});

	document.addEventListener('click', event => {
		const close = event.target.closest('[data-mwp-modal-close]');

		if (!close) {
			return;
		}

		close.closest('[data-mwp-modal]').hidden = true;
	});

	document.addEventListener('keydown', event => {
		if (event.key !== 'Escape') {
			return;
		}

		document.querySelectorAll('[data-mwp-modal]:not([hidden])').forEach(modal => {
			modal.hidden = true;
		});
	});
}

function ensureLightbox() {
	let lightbox = document.querySelector('[data-mwp-lightbox]');

	if (lightbox) {
		return lightbox;
	}

	lightbox = document.createElement('div');
	lightbox.className = 'mwp-lightbox';
	lightbox.hidden = true;
	lightbox.dataset.mwpLightbox = '';
	lightbox.innerHTML = `
		<div class="mwp-lightbox__overlay" data-mwp-lightbox-close></div>
		<div class="mwp-lightbox__dialog" role="dialog" aria-modal="true">
			<button class="mwp-icon-button mwp-lightbox__close" type="button" aria-label="Close" data-mwp-lightbox-close>&times;</button>
			<img class="mwp-lightbox__image" alt="">
			<div class="mwp-lightbox__caption"></div>
		</div>
	`;
	document.body.appendChild(lightbox);

	return lightbox;
}

function initLightbox() {
	document.addEventListener('click', event => {
		const trigger = event.target.closest('[data-mwp-lightbox-trigger]');

		if (!trigger) {
			return;
		}

		const lightbox = ensureLightbox();
		const image = lightbox.querySelector('.mwp-lightbox__image');
		const caption = lightbox.querySelector('.mwp-lightbox__caption');

		image.src = trigger.dataset.mwpLightboxSrc || '';
		image.alt = trigger.dataset.mwpLightboxAlt || '';
		caption.textContent = trigger.dataset.mwpLightboxCaption || '';
		caption.hidden = !caption.textContent;
		lightbox.hidden = false;
		lightbox.querySelector('[data-mwp-lightbox-close]')?.focus();
	});

	document.addEventListener('click', event => {
		const close = event.target.closest('[data-mwp-lightbox-close]');

		if (!close) {
			return;
		}

		close.closest('[data-mwp-lightbox]').hidden = true;
	});

	document.addEventListener('keydown', event => {
		if (event.key !== 'Escape') {
			return;
		}

		document.querySelectorAll('[data-mwp-lightbox]:not([hidden])').forEach(lightbox => {
			lightbox.hidden = true;
		});
	});
}

function initAccordions() {
	document.querySelectorAll('[data-mwp-accordion-trigger]').forEach(trigger => {
		trigger.addEventListener('click', () => {
			const content = document.getElementById(trigger.getAttribute('aria-controls'));
			const isOpen = trigger.getAttribute('aria-expanded') === 'true';

			trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');

			if (content) {
				content.hidden = isOpen;
			}
		});
	});
}

function escapeHtml(value) {
	return value
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;');
}

function highlightCss(code) {
	return escapeHtml(code)
		.replace(/(\/\*[\s\S]*?\*\/)/g, '<span class="token-comment">$1</span>')
		.replace(/([{};])/g, '<span class="token-punctuation">$1</span>')
		.replace(/([a-zA-Z-]+)(\s*:)/g, '<span class="token-property">$1</span><span class="token-punctuation">$2</span>')
		.replace(/(:\s*)([^;{}]+)/g, '$1<span class="token-value">$2</span>');
}

function highlightHtml(code) {
	return escapeHtml(code)
		.replace(/(&lt;!--[\s\S]*?--&gt;)/g, '<span class="token-comment">$1</span>')
		.replace(/(&lt;\/?)([a-zA-Z0-9:-]+)/g, '<span class="token-punctuation">$1</span><span class="token-tag">$2</span>')
		.replace(/([a-zA-Z0-9:-]+)(=)(&quot;.*?&quot;)/g, '<span class="token-attr">$1</span><span class="token-punctuation">$2</span><span class="token-value">$3</span>')
		.replace(/(&gt;)/g, '<span class="token-punctuation">$1</span>');
}

function initCodeAreas() {
	document.querySelectorAll('[data-mwp-code-area]').forEach(editor => {
		const input = editor.querySelector('[data-mwp-code-input]');
		const highlight = editor.querySelector('[data-mwp-code-highlight]');
		const language = editor.dataset.mwpCodeLanguage || 'html';

		if (!input || !highlight) {
			return;
		}

		function syncHighlight() {
			highlight.innerHTML = language === 'css' ? highlightCss(input.value) : highlightHtml(input.value);
		}

		function syncScroll() {
			highlight.parentElement.scrollTop = input.scrollTop;
			highlight.parentElement.scrollLeft = input.scrollLeft;
		}

		input.addEventListener('input', syncHighlight);
		input.addEventListener('scroll', syncScroll);
		syncHighlight();
	});
}

export function initAdminUI() {
	initColorPickers();
	initModals();
	initLightbox();
	initAccordions();
	initCodeAreas();
}
