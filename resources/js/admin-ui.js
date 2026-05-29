import { initClipboard } from './modules/clipboard';
import { initMediaControls } from './modules/media-controls';
import { initLogViewer, initPaginatedTables } from './modules/log-viewer';

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
	document.addEventListener('input', event => {
		const swatch = event.target.closest('[data-mwp-color-swatch]');

		if (swatch) {
			const picker = swatch.closest('[data-mwp-color-picker]');
			const input = picker?.querySelector('[data-mwp-color-input]');

			if (input) {
				input.value = swatch.value.toUpperCase();
				input.dispatchEvent(new Event('input', { bubbles: true }));
			}

			return;
		}

		const input = event.target.closest('[data-mwp-color-input]');

		if (input) {
			const picker = input.closest('[data-mwp-color-picker]');
			const swatch = picker?.querySelector('[data-mwp-color-swatch]');
			const color = normalizeHexColor(input.value);

			if (isHexColor(color) && swatch) {
				swatch.value = color;
				input.value = color.toUpperCase();
			}
		}
	});
}

function initModals() {
	const modalCache = new WeakMap();
	let previousFocus = null;

	function moveModalToBody(modal) {
		if (modal.parentElement !== document.body) {
			document.body.appendChild(modal);
		}
	}

	document.addEventListener('click', event => {
		const trigger = event.target.closest('.mwp-modal-trigger');

		if (!trigger) {
			return;
		}

		let modal = modalCache.get(trigger);

		if (!modal) {
			modal = trigger.nextElementSibling;

			if (!modal || !modal.matches('[data-mwp-modal]')) {
				return;
			}

			moveModalToBody(modal);
			modalCache.set(trigger, modal);
		}

		previousFocus = document.activeElement;
		modal.hidden = false;
		modal.querySelector('[data-mwp-modal-close]')?.focus();
	});

	document.addEventListener('click', event => {
		const close = event.target.closest('[data-mwp-modal-close]');

		if (!close) {
			return;
		}

		close.closest('[data-mwp-modal]').hidden = true;

		if (previousFocus) {
			previousFocus.focus();
		}
	});

	document.addEventListener('keydown', event => {
		if (event.key !== 'Escape') {
			return;
		}

		document.querySelectorAll('[data-mwp-modal]:not([hidden])').forEach(modal => {
			modal.hidden = true;
		});

		if (previousFocus) {
			previousFocus.focus();
		}
	});

	document.addEventListener('keydown', event => {
		if (event.key !== 'Tab') {
			return;
		}

		const modal = event.target.closest('[data-mwp-modal]');

		if (!modal) {
			return;
		}

		const focusable = modal.querySelectorAll(
			'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
		);

		if (focusable.length === 0) {
			return;
		}

		const first = focusable[0];
		const last = focusable[focusable.length - 1];

		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
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
			<button class="mwp-icon-button mwp-lightbox__close" type="button" aria-label="Close" data-mwp-lightbox-close><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
			<img class="mwp-lightbox__image" alt="">
			<div class="mwp-lightbox__caption"></div>
		</div>
	`;
	document.body.appendChild(lightbox);

	return lightbox;
}

function initLightbox() {
	let previousFocus = null;

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
		previousFocus = document.activeElement;
		lightbox.hidden = false;
		lightbox.querySelector('[data-mwp-lightbox-close]')?.focus();
	});

	document.addEventListener('click', event => {
		const close = event.target.closest('[data-mwp-lightbox-close]');

		if (!close) {
			return;
		}

		close.closest('[data-mwp-lightbox]').hidden = true;

		if (previousFocus) {
			previousFocus.focus();
		}
	});

	document.addEventListener('keydown', event => {
		if (event.key !== 'Escape') {
			return;
		}

		document.querySelectorAll('[data-mwp-lightbox]:not([hidden])').forEach(lightbox => {
			lightbox.hidden = true;
		});

		if (previousFocus) {
			previousFocus.focus();
		}
	});

	document.addEventListener('keydown', event => {
		if (event.key !== 'Tab') {
			return;
		}

		const lightbox = event.target.closest('[data-mwp-lightbox]');

		if (!lightbox) {
			return;
		}

		const focusable = lightbox.querySelectorAll(
			'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
		);

		if (focusable.length === 0) {
			return;
		}

		const first = focusable[0];
		const last = focusable[focusable.length - 1];

		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	});
}

function initAccordions() {
	document.addEventListener('click', event => {
		const trigger = event.target.closest('[data-mwp-accordion-trigger]');

		if (!trigger) {
			return;
		}

		const content = document.getElementById(trigger.getAttribute('aria-controls'));
		const isOpen = trigger.getAttribute('aria-expanded') === 'true';

		trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');

		if (content) {
			content.hidden = isOpen;
		}
	});
}

export function initAdminUI() {
	[initColorPickers, initModals, initLightbox, initAccordions, initClipboard, initMediaControls, initLogViewer, initPaginatedTables].forEach(fn => {
		try { fn(); } catch (e) { console.error('AdminUI init error:', fn.name, e); }
	});
}
