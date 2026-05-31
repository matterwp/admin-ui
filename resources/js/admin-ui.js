import { initClipboard } from './modules/clipboard';
import { initMediaControls } from './modules/media-controls';
import { initLogViewer, initPaginatedTables } from './modules/log-viewer';

function hasElement(selector) {
	return Boolean(document.querySelector(selector));
}

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
	const focusReturn = new WeakMap();
	const modalTriggers = new WeakMap();
	const activeModals = [];
	let generatedFormId = 0;
	const focusableSelector = [
		'a[href]',
		'button:not([disabled])',
		'input:not([disabled])',
		'select:not([disabled])',
		'textarea:not([disabled])',
		'[tabindex]:not([tabindex="-1"])'
	].join(',');

	function moveModalToBody(modal) {
		if (modal.parentElement !== document.body) {
			document.body.appendChild(modal);
		}
	}

	function getFocusable(modal) {
		return Array.from(modal.querySelectorAll(focusableSelector)).filter(element => {
			return element.offsetParent !== null || element === document.activeElement;
		});
	}

	function getModalFromTrigger(trigger) {
		let modal = modalCache.get(trigger);

		if (modal) {
			return modal;
		}

		const controls = trigger.getAttribute('aria-controls');

		if (controls) {
			modal = document.getElementById(controls);
		}

		if (!modal) {
			modal = trigger.nextElementSibling;
		}

		if (!modal || !modal.matches('[data-mwp-modal]')) {
			return null;
		}

		moveModalToBody(modal);
		modalCache.set(trigger, modal);
		modalTriggers.set(modal, trigger);

		return modal;
	}

	function setBodyLock() {
		document.body.classList.toggle('mwp-modal-open', activeModals.length > 0);
	}

	function preserveFormSubmitTarget(modal, trigger) {
		const form = trigger?.closest('form');

		if (!form) {
			return;
		}

		if (!form.id) {
			generatedFormId += 1;
			form.id = `mwp-modal-form-${generatedFormId}`;
		}

		modal.querySelectorAll('button[type="submit"]:not([form]), input[type="submit"]:not([form])').forEach(button => {
			button.setAttribute('form', form.id);
		});
	}

	function focusModal(modal) {
		const focusable = getFocusable(modal);
		const target = modal.querySelector('[data-mwp-autofocus], [autofocus]') || focusable.find(element => !element.matches('[data-mwp-modal-close]')) || focusable[0] || modal.querySelector('.mwp-modal__dialog');

		if (target) {
			target.focus({ preventScroll: true });
		}
	}

	function openModal(modal, trigger) {
		if (!modal.hidden) {
			focusModal(modal);
			return;
		}

		focusReturn.set(modal, document.activeElement);
		modalTriggers.set(modal, trigger);
		preserveFormSubmitTarget(modal, trigger);
		modal.hidden = false;
		modal.setAttribute('aria-hidden', 'false');
		trigger?.setAttribute('aria-expanded', 'true');
		activeModals.push(modal);
		setBodyLock();
		modal.dispatchEvent(new CustomEvent('mwp:modal-open', { bubbles: true }));
		window.requestAnimationFrame(() => focusModal(modal));
	}

	function closeModal(modal, restoreFocus = true) {
		if (!modal || modal.hidden) {
			return;
		}

		modal.hidden = true;
		modal.setAttribute('aria-hidden', 'true');

		const activeIndex = activeModals.lastIndexOf(modal);
		if (activeIndex !== -1) {
			activeModals.splice(activeIndex, 1);
		}

		const trigger = modalTriggers.get(modal);
		trigger?.setAttribute('aria-expanded', 'false');
		setBodyLock();
		modal.dispatchEvent(new CustomEvent('mwp:modal-close', { bubbles: true }));

		if (restoreFocus) {
			const returnTarget = focusReturn.get(modal);

			if (returnTarget && document.contains(returnTarget)) {
				returnTarget.focus({ preventScroll: true });
			}
		}
	}

	document.addEventListener('click', event => {
		const trigger = event.target.closest('[data-mwp-modal-trigger], .mwp-modal-trigger');

		if (!trigger || trigger.disabled) {
			return;
		}

		const modal = getModalFromTrigger(trigger);

		if (!modal) {
			return;
		}

		event.preventDefault();
		openModal(modal, trigger);
	});

	document.addEventListener('click', event => {
		const close = event.target.closest('[data-mwp-modal-close]');

		if (!close) {
			return;
		}

		const modal = close.closest('[data-mwp-modal]');

		if (!modal) {
			return;
		}

		event.preventDefault();
		closeModal(modal);
	});

	document.addEventListener('keydown', event => {
		const modal = activeModals[activeModals.length - 1];

		if (!modal) {
			return;
		}

		if (event.key === 'Escape') {
			event.preventDefault();
			closeModal(modal);
			return;
		}

		if (event.key !== 'Tab') {
			return;
		}

		const focusable = getFocusable(modal);
		const first = focusable[0] || modal.querySelector('.mwp-modal__dialog');
		const last = focusable[focusable.length - 1] || first;

		if (!modal.contains(document.activeElement)) {
			event.preventDefault();
			first.focus();
		} else if (event.shiftKey && document.activeElement === first) {
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
	const initializers = [
		['[data-mwp-color-picker]', initColorPickers],
		['[data-mwp-modal]', initModals],
		['[data-mwp-lightbox-trigger]', initLightbox],
		['[data-mwp-accordion]', initAccordions],
		['[data-copy-value]', initClipboard],
		['[data-media-target]', initMediaControls],
		['[data-log-viewer]', initLogViewer],
		['[data-mwp-paginated-table]', initPaginatedTables],
	];

	initializers.forEach(([selector, fn]) => {
		if (!hasElement(selector)) {
			return;
		}

		try { fn(); } catch (e) { console.error('AdminUI init error:', fn.name, e); }
	});
}
