import { initClipboard } from './modules/clipboard';
import { initMediaControls } from './modules/media-controls';
import { initLogViewer, initPaginatedTables, loadTablePage, replaceTableRows } from './modules/log-viewer';

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

function mergeGlobalApi(api) {
	window.MatterAdminUI = {
		...(window.MatterAdminUI || {}),
		...api
	};
}

function getElement(target) {
	if (typeof target === 'string') {
		return document.querySelector(target);
	}

	return target || null;
}

function cssEscape(value) {
	if (window.CSS?.escape) {
		return CSS.escape(String(value));
	}

	return String(value).replace(/["\\#.:\\[\\]]/g, '\\$&');
}

function rowFromHtml(rowHtmlOrData) {
	if (rowHtmlOrData instanceof HTMLTableRowElement) {
		return rowHtmlOrData;
	}

	if (rowHtmlOrData instanceof HTMLElement) {
		return rowHtmlOrData.querySelector('tr') || rowHtmlOrData;
	}

	if (rowHtmlOrData && typeof rowHtmlOrData === 'object' && rowHtmlOrData.html) {
		return rowFromHtml(rowHtmlOrData.html);
	}

	const template = document.createElement('template');
	template.innerHTML = String(rowHtmlOrData || '').trim();
	return template.content.querySelector('tr');
}

function findTableRoot(tableOrRoot) {
	const root = getElement(tableOrRoot);

	if (!root) {
		return null;
	}

	return root.matches('[data-mwp-paginated-table], .mwp-table-wrap') ? root : root.closest('[data-mwp-paginated-table], .mwp-table-wrap');
}

function initTableApi() {
	mergeGlobalApi({
		table: {
			appendRow(tableOrRoot, rowHtmlOrData, options = {}) {
				const table = findTableRoot(tableOrRoot);
				const row = rowFromHtml(rowHtmlOrData);
				const tbody = table?.querySelector('tbody');

				if (!table || !row || !tbody) {
					return null;
				}

				tbody.appendChild(row);
				this.refresh(table, { page: options.page || table.dataset.currentPage || 'last' });
				return row;
			},
			updateRow(tableOrRoot, rowId, rowHtmlOrData) {
				const table = findTableRoot(tableOrRoot);
				const row = rowFromHtml(rowHtmlOrData);
				const escapedId = cssEscape(rowId);
				const current = table?.querySelector(`[data-mwp-row-id="${escapedId}"], [data-row-key="${escapedId}"], #${escapedId}`);

				if (!table || !row || !current) {
					return null;
				}

				current.replaceWith(row);
				this.refresh(table);
				return row;
			},
			removeRow(tableOrRoot, rowId) {
				const table = findTableRoot(tableOrRoot);
				const escapedId = cssEscape(rowId);
				const row = table?.querySelector(`[data-mwp-row-id="${escapedId}"], [data-row-key="${escapedId}"], #${escapedId}`);

				if (!table || !row) {
					return false;
				}

				row.remove();
				this.refresh(table);
				return true;
			},
			refresh(tableOrRoot, options = {}) {
				const table = findTableRoot(tableOrRoot);

				if (!table) {
					return;
				}

				table.dispatchEvent(new CustomEvent('mwp:table-refresh', {
					bubbles: true,
					detail: {
						page: options.page,
						rowsHtml: options.rowsHtml,
						meta: options.meta
					}
				}));
			},
			loadPage(tableOrRoot, page, options = {}) {
				return loadTablePage(tableOrRoot, page, options);
			},
			replaceRows(tableOrRoot, rowsHtml, meta = {}) {
				return replaceTableRows(tableOrRoot, rowsHtml, meta);
			}
		}
	});
}

function ensureNoticeContainer(options = {}) {
	const selector = options.container || '[data-mwp-notices]';
	let container = document.querySelector(selector);

	if (!container) {
		container = document.createElement('div');
		container.className = 'mwp-notice-wrapper';
		container.dataset.mwpNotices = '';
		document.body.appendChild(container);
	}

	return container;
}

function removeNotice(notice) {
	if (!notice || notice.dataset.mwpRemoving === 'true') {
		return;
	}

	notice.dataset.mwpRemoving = 'true';
	notice.classList.remove('is-visible');
	notice.classList.add('is-removing');
	window.setTimeout(() => notice.remove(), 300);
}

function showNotice(message, type = 'success', options = {}) {
	const container = ensureNoticeContainer(options);
	const notice = document.createElement('div');
	const duration = Number(options.duration ?? 3000);

	notice.className = `mwp-notice ${type}`;
	notice.setAttribute('role', 'alert');
	notice.setAttribute('aria-live', 'polite');
	notice.innerHTML = `<span class="notice-text">${String(message || '')}</span>`;
	container.appendChild(notice);
	window.requestAnimationFrame(() => {
		notice.classList.add('is-visible');
	});

	if (duration > 0) {
		window.setTimeout(() => removeNotice(notice), duration);
	}

	return notice;
}

function serializeAjaxRoot(formOrRoot) {
	const root = getElement(formOrRoot);

	if (!root) {
		return null;
	}

	if (root.matches('form')) {
		return new FormData(root);
	}

	const form = root.querySelector('form');
	return form ? new FormData(form) : new FormData();
}

function setLoading(root, loading) {
	const buttons = root.querySelectorAll('[data-mwp-submit], button[type="submit"], input[type="submit"]');
	root.classList.toggle('is-loading', loading);
	root.dataset.mwpLoading = loading ? 'true' : 'false';
	buttons.forEach(button => {
		button.disabled = loading;
		button.classList.toggle('is-loading', loading);
	});
}

function initAjaxApi() {
	async function submit(formOrRoot, options = {}) {
		const root = getElement(formOrRoot);
		const formData = serializeAjaxRoot(root);

		if (!root || !formData) {
			throw new Error('MatterAdminUI.ajax.submit requires a form or root element.');
		}

		const action = options.action || root.dataset.mwpAction || formData.get('action');
		const endpoint = options.url || root.getAttribute('action') || window.ajaxurl || root.dataset.mwpEndpoint || '';

		if (action && !formData.has('action')) {
			formData.set('action', action);
		}

		root.dispatchEvent(new CustomEvent('mwp:ajax-before', { bubbles: true, detail: { formData, options } }));
		setLoading(root, true);

		try {
			const response = await fetch(endpoint, {
				method: options.method || 'POST',
				body: formData,
				credentials: 'same-origin'
			});
			const contentType = response.headers.get('content-type') || '';
			const data = contentType.includes('application/json') ? await response.json() : await response.text();

			if (!response.ok || data?.success === false) {
				throw new Error(data?.data?.message || data?.message || response.statusText);
			}

			root.dispatchEvent(new CustomEvent('mwp:ajax-success', { bubbles: true, detail: { data, response } }));
			return data;
		} catch (error) {
			root.dispatchEvent(new CustomEvent('mwp:ajax-error', { bubbles: true, detail: { error } }));
			throw error;
		} finally {
			setLoading(root, false);
			root.dispatchEvent(new CustomEvent('mwp:ajax-complete', { bubbles: true }));
		}
	}

	mergeGlobalApi({
		ajax: { submit },
		notice: showNotice
	});
}

function initAjaxForms() {
	if (document.documentElement.dataset.mwpAjaxFormsReady === 'true') {
		return;
	}

	document.documentElement.dataset.mwpAjaxFormsReady = 'true';
	document.addEventListener('submit', event => {
		const root = event.target.closest('[data-mwp-ajax-form]');

		if (!root) {
			return;
		}

		event.preventDefault();
		window.MatterAdminUI.ajax.submit(root).catch(error => {
			if (root.dataset.mwpNotice !== 'false') {
				window.MatterAdminUI.notice(error.message, 'error');
			}
		});
	});
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

	function escapeSelector(value) {
		if (window.CSS?.escape) {
			return CSS.escape(String(value));
		}

		return String(value).replace(/["\\]/g, '\\$&');
	}

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

	function populateModal(modal, values = {}) {
		if (!modal || !values || typeof values !== 'object') {
			return;
		}

		Object.entries(values).forEach(([name, value]) => {
			const fieldName = escapeSelector(name);
			const fields = modal.querySelectorAll(`[name="${fieldName}"], [data-mwp-field="${fieldName}"]`);

			fields.forEach(field => {
				if (field.matches('input[type="checkbox"], input[type="radio"]')) {
					field.checked = Array.isArray(value) ? value.map(String).includes(field.value) : field.value === String(value) || value === true;
					return;
				}

				if ('value' in field) {
					field.value = value ?? '';
					field.dispatchEvent(new Event('input', { bubbles: true }));
					field.dispatchEvent(new Event('change', { bubbles: true }));
				} else {
					field.textContent = value ?? '';
				}
			});
		});
	}

	mergeGlobalApi({
		openModal(target, values = {}, trigger = null) {
			const modal = typeof target === 'string' ? document.getElementById(target) : target;

			if (!modal || !modal.matches('[data-mwp-modal]')) {
				return null;
			}

			moveModalToBody(modal);
			populateModal(modal, values);
			openModal(modal, trigger || document.querySelector(`[aria-controls="${escapeSelector(modal.id)}"]`));
			return modal;
		},
		closeModal(target, restoreFocus = true) {
			const modal = typeof target === 'string' ? document.getElementById(target) : target;
			closeModal(modal, restoreFocus);
		},
		populateModal(target, values = {}) {
			const modal = typeof target === 'string' ? document.getElementById(target) : target;
			populateModal(modal, values);
		}
	});

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
			<div class="mwp-lightbox__header">
				<div class="mwp-lightbox__heading"><h4></h4></div>
				<button class="mwp-icon-button mwp-lightbox__close" type="button" aria-label="Close" data-mwp-lightbox-close><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
			</div>
			<div class="mwp-lightbox__content">
				<img class="mwp-lightbox__image" alt="">
			</div>
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
		const title = lightbox.querySelector('.mwp-lightbox__heading h4');
		const caption = lightbox.querySelector('.mwp-lightbox__caption');

		title.textContent = trigger.dataset.mwpLightboxTitle || trigger.dataset.mwpLightboxAlt || '';
		title.closest('.mwp-lightbox__header').hidden = !title.textContent;
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

function initConfirmActions() {
	document.addEventListener('click', event => {
		const trigger = event.target.closest('[data-mwp-confirm]');

		if (!trigger || trigger.dataset.mwpConfirmHandled === 'true') {
			return;
		}

		const message = trigger.dataset.mwpConfirm || 'Are you sure?';

		if (!window.confirm(message)) {
			event.preventDefault();
			event.stopPropagation();
		}
	});
}

function initComponentTabs() {
	if (document.documentElement.dataset.mwpComponentTabsReady === 'true') {
		return;
	}

	document.documentElement.dataset.mwpComponentTabsReady = 'true';

	function getTabSet(element) {
		return element.closest('[data-mwp-tabs]');
	}

	function getTabs(root) {
		return Array.from(root.querySelectorAll('[data-mwp-tab]')).filter(tab => tab.closest('[data-mwp-tabs]') === root);
	}

	function getPanels(root) {
		return Array.from(root.querySelectorAll('[data-mwp-panel]')).filter(panel => panel.closest('[data-mwp-tabs]') === root);
	}

	function activate(root, tab, focus = false) {
		if (!root || !tab || tab.getAttribute('aria-disabled') === 'true') {
			return;
		}

		const id = tab.dataset.mwpTab;

		getTabs(root).forEach(item => {
			const active = item === tab;
			item.classList.toggle('is-active', active);
			item.setAttribute('aria-selected', active ? 'true' : 'false');
			item.tabIndex = active ? 0 : -1;
		});

		getPanels(root).forEach(panel => {
			const active = panel.dataset.mwpPanel === id;
			panel.hidden = !active;
			panel.classList.toggle('is-active', active);
		});

		root.dataset.mwpTabsActive = id;
		root.dispatchEvent(new CustomEvent('mwp:tabs-change', { bubbles: true, detail: { id, tab } }));

		if (focus) {
			tab.focus({ preventScroll: true });
		}
	}

	document.querySelectorAll('[data-mwp-tabs]').forEach(root => {
		const tabs = getTabs(root);
		const initial = tabs.find(tab => tab.dataset.mwpTab === root.dataset.mwpTabsActive) || tabs.find(tab => tab.classList.contains('is-active')) || tabs[0];

		if (initial) {
			activate(root, initial);
		}
	});

	document.addEventListener('click', event => {
		const tab = event.target.closest('[data-mwp-tab]');

		if (!tab) {
			return;
		}

		const root = getTabSet(tab);

		if (!root) {
			return;
		}

		const panel = getPanels(root).find(item => item.dataset.mwpPanel === tab.dataset.mwpTab);

		if (!panel) {
			return;
		}

		event.preventDefault();
		activate(root, tab);
	});

	document.addEventListener('keydown', event => {
		const tab = event.target.closest('[data-mwp-tab]');
		const root = tab ? getTabSet(tab) : null;

		if (!tab || !root) {
			return;
		}

		if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
			return;
		}

		const tabs = getTabs(root).filter(item => item.getAttribute('aria-disabled') !== 'true');
		const currentIndex = tabs.indexOf(tab);
		let nextIndex = currentIndex;

		if (event.key === 'Home') {
			nextIndex = 0;
		} else if (event.key === 'End') {
			nextIndex = tabs.length - 1;
		} else if (event.key === 'ArrowLeft') {
			nextIndex = currentIndex <= 0 ? tabs.length - 1 : currentIndex - 1;
		} else if (event.key === 'ArrowRight') {
			nextIndex = currentIndex >= tabs.length - 1 ? 0 : currentIndex + 1;
		}

		if (tabs[nextIndex]) {
			event.preventDefault();
			activate(root, tabs[nextIndex], true);
		}
	});
}

function initSwitchGrid() {
	if (document.documentElement.dataset.mwpSwitchGridReady === 'true') {
		return;
	}

	document.documentElement.dataset.mwpSwitchGridReady = 'true';

	function toggleItem(item) {
		const input = item?.querySelector('.mwp-switch input[type="checkbox"]');

		if (!input || input.disabled) {
			return;
		}

		input.checked = !input.checked;
		input.dispatchEvent(new Event('input', { bubbles: true }));
		input.dispatchEvent(new Event('change', { bubbles: true }));
	}

	document.addEventListener('click', event => {
		const item = event.target.closest('.mwp-switch-grid__item');

		if (!item || event.target.closest('.mwp-switch')) {
			return;
		}

		toggleItem(item);
	});

	document.addEventListener('keydown', event => {
		const item = event.target.closest('.mwp-switch-grid__item');

		if (!item || !['Enter', ' '].includes(event.key)) {
			return;
		}

		event.preventDefault();
		toggleItem(item);
	});
}

let defaultTabStorageKey = 'boilerplate_active_tab';

function getDefaultTabStorageKey() {
	return defaultTabStorageKey;
}

function setDefaultTabStorageKey(key, { migrateFrom } = {}) {
	if (typeof key !== 'string' || '' === key) {
		return;
	}

	if (typeof migrateFrom === 'string' && migrateFrom !== key) {
		const legacy = localStorage.getItem(migrateFrom);
		if (legacy !== null && localStorage.getItem(key) === null) {
			localStorage.setItem(key, legacy);
			localStorage.removeItem(migrateFrom);
		}
	}

	defaultTabStorageKey = key;
}

function resolveTabStorageKey(navItem) {
	const surface = navItem?.closest('.mwp-option-nav');
	const surfaceKey = surface?.getAttribute('data-ui-storage-key');
	return surfaceKey ? `${surfaceKey}_active_tab` : defaultTabStorageKey;
}

function firstTabIdInSurface(surface) {
	if (!surface) {
		return '';
	}

	const first = surface.querySelector('[data-ui-tab]');
	return first ? first.getAttribute('data-ui-tab') || '' : '';
}

function initNavigation() {
	const navItems = document.querySelectorAll('[data-ui-tab]');
	const navSurfaces = document.querySelectorAll('.mwp-option-nav');
	const optionGroups = document.querySelectorAll('[data-ui-panel]');

	function getTabElements(tabId) {
		if (!tabId) {
			return { navItem: null, panel: null };
		}

		return {
			navItem: document.querySelector(`[data-ui-tab="${tabId}"]`),
			panel: document.querySelector(`[data-ui-panel="${tabId}"]`)
		};
	}

	function updateActiveIndicator(navItem, animate = true) {
		const activeSurface = navItem.closest('.mwp-option-nav');

		if (!activeSurface) {
			return;
		}

		navSurfaces.forEach(surface => {
			if (surface !== activeSurface) {
				surface.classList.remove('has-active-indicator');
			}
		});

		const surfaceRect = activeSurface.getBoundingClientRect();
		const navItemRect = navItem.getBoundingClientRect();

		activeSurface.style.setProperty('--mwp-nav-active-x', `${navItemRect.left - surfaceRect.left + activeSurface.scrollLeft}px`);
		activeSurface.style.setProperty('--mwp-nav-active-y', `${navItemRect.top - surfaceRect.top + activeSurface.scrollTop}px`);
		activeSurface.style.setProperty('--mwp-nav-active-width', `${navItemRect.width}px`);
		activeSurface.style.setProperty('--mwp-nav-active-height', `${navItemRect.height}px`);
		activeSurface.classList.add('has-active-indicator');

		if (!animate) {
			activeSurface.classList.remove('is-indicator-ready');
			requestAnimationFrame(() => activeSurface.classList.add('is-indicator-ready'));
		} else {
			activeSurface.classList.add('is-indicator-ready');
		}
	}

	function setActiveTab(tabId, persist = true, clickedNavItem = null) {
		const { navItem, panel } = getTabElements(tabId);
		const activeNavItem = clickedNavItem || navItem;

		if (!activeNavItem || !panel) {
			return false;
		}

		navItems.forEach(nav => nav.classList.remove('active'));
		optionGroups.forEach(group => group.classList.remove('active'));

		activeNavItem.classList.add('active');
		panel.classList.add('active');
		updateActiveIndicator(activeNavItem, persist);

		if (persist) {
			const storageKey = resolveTabStorageKey(activeNavItem);
			if (storageKey) {
				localStorage.setItem(storageKey, tabId);
			}
		}

		return true;
	}

	navItems.forEach(item => {
		item.addEventListener('click', function () {
			const targetId = item.getAttribute('data-ui-tab');
			setActiveTab(targetId, true, item);
		});
	});

	navSurfaces.forEach(surface => {
		const surfaceKey = surface.getAttribute('data-ui-storage-key');
		const storageKey = surfaceKey ? `${surfaceKey}_active_tab` : defaultTabStorageKey;
		const savedTabId = localStorage.getItem(storageKey);

		if (!setActiveTab(savedTabId, false)) {
			setActiveTab(firstTabIdInSurface(surface), false);
		}
	});

	window.addEventListener('resize', () => {
		const activeNavItem = document.querySelector('[data-ui-tab].active');

		if (activeNavItem) {
			updateActiveIndicator(activeNavItem, false);
		}
	});
}

mergeGlobalApi({
	tabs: {
		setStorageKey: setDefaultTabStorageKey,
		getStorageKey: getDefaultTabStorageKey
	}
});

let defaultThemeStorageKey = 'mwp-theme';

function getDefaultThemeStorageKey() {
	return defaultThemeStorageKey;
}

function setDefaultThemeStorageKey(key, { migrateFrom } = {}) {
	if (typeof key !== 'string' || '' === key) {
		return;
	}

	if (typeof migrateFrom === 'string' && migrateFrom !== key) {
		const legacy = localStorage.getItem(migrateFrom);
		if (legacy !== null && localStorage.getItem(key) === null) {
			localStorage.setItem(key, legacy);
			localStorage.removeItem(migrateFrom);
		}
	}

	defaultThemeStorageKey = key;
}

function resolveThemeStorageKey(toggle) {
	const surfaceKey = toggle?.getAttribute('data-mwp-theme-key');
	return surfaceKey ? `${surfaceKey}_theme` : defaultThemeStorageKey;
}

function initThemeToggle() {
	const applyTheme = theme => {
		const next = theme === 'dark' ? 'dark' : 'light';
		document.documentElement.dataset.mwpTheme = next;
		document.querySelectorAll('[data-mwp-theme-toggle]').forEach(toggle => {
			toggle.dataset.mwpTheme = next;
			toggle.setAttribute('aria-pressed', next === 'dark' ? 'true' : 'false');
		});
	};

	const toggles = document.querySelectorAll('[data-mwp-theme-toggle]');
	toggles.forEach(toggle => {
		const storageKey = resolveThemeStorageKey(toggle);
		const stored = localStorage.getItem(storageKey);
		if (stored === 'dark' || stored === 'light') {
			toggle.dataset.mwpTheme = stored;
		}
	});

	applyTheme(localStorage.getItem(defaultThemeStorageKey) || document.documentElement.dataset.mwpTheme || 'light');

	document.addEventListener('click', event => {
		const toggle = event.target.closest('[data-mwp-theme-toggle]');

		if (!toggle) {
			return;
		}

		const storageKey = resolveThemeStorageKey(toggle);
		const next = document.documentElement.dataset.mwpTheme === 'dark' ? 'light' : 'dark';
		localStorage.setItem(storageKey, next);
		applyTheme(next);
	});
}

mergeGlobalApi({
	theme: {
		setStorageKey: setDefaultThemeStorageKey,
		getStorageKey: getDefaultThemeStorageKey
	}
});

function parseCondition(value) {
	try {
		return JSON.parse(value || '{}');
	} catch (error) {
		return {};
	}
}

function fieldValue(field) {
	if (!field) {
		return null;
	}

	if (field.matches('input[type="checkbox"]')) {
		return field.checked;
	}

	if (field.matches('input[type="radio"]')) {
		return field.form?.querySelector(`[name="${cssEscape(field.name)}"]:checked`)?.value || '';
	}

	return field.value;
}

function matchesCondition(condition, root) {
	if (!condition || typeof condition !== 'object') {
		return true;
	}

	if (!('field' in condition) && !('name' in condition)) {
		return Object.entries(condition).every(([name, expected]) => matchesCondition({ field: name, value: expected }, root));
	}

	const fieldName = condition.field || condition.name;
	const field = root.querySelector(`[name="${cssEscape(fieldName)}"], #${cssEscape(fieldName)}`);
	const current = fieldValue(field);
	const expected = condition.value ?? condition.equals ?? true;
	const matched = Array.isArray(expected) ? expected.map(String).includes(String(current)) : String(current) === String(expected) || current === expected;

	return condition.not ? !matched : matched;
}

function initDependencies() {
	const rows = Array.from(document.querySelectorAll('[data-mwp-visible-if], [data-mwp-disabled-if], [data-mwp-requires]'));

	if (!rows.length || document.documentElement.dataset.mwpDependenciesReady === 'true') {
		return;
	}

	document.documentElement.dataset.mwpDependenciesReady = 'true';

	function update() {
		rows.forEach(row => {
			const root = row.closest('form, .mwp-admin-app, #mwp-settings') || document.body;
			const visible = row.dataset.mwpVisibleIf ? matchesCondition(parseCondition(row.dataset.mwpVisibleIf), root) : true;
			const disabled = row.dataset.mwpDisabledIf ? matchesCondition(parseCondition(row.dataset.mwpDisabledIf), root) : false;
			const requires = row.dataset.mwpRequires ? matchesCondition(parseCondition(row.dataset.mwpRequires), root) : true;

			row.hidden = !visible;
			row.querySelectorAll('input, select, textarea, button').forEach(control => {
				control.disabled = disabled || !requires;
			});
		});
	}

	document.addEventListener('input', update);
	document.addEventListener('change', update);
	update();
}

export function initAdminUI() {
	initTableApi();
	initAjaxApi();
	initAjaxForms();
	initThemeToggle();

	const initializers = [
		['[data-ui-tab]', initNavigation],
		['[data-mwp-tabs]', initComponentTabs],
		['.mwp-switch-grid__item', initSwitchGrid],
		['[data-mwp-visible-if], [data-mwp-disabled-if], [data-mwp-requires]', initDependencies],
		['[data-mwp-color-picker]', initColorPickers],
		['[data-mwp-modal]', initModals],
		['[data-mwp-lightbox-trigger]', initLightbox],
		['[data-mwp-accordion]', initAccordions],
		['[data-copy-value]', initClipboard],
		['[data-media-target]', initMediaControls],
		['[data-log-viewer]', initLogViewer],
		['[data-mwp-paginated-table]', initPaginatedTables],
		['[data-mwp-confirm]', initConfirmActions],
	];

	initializers.forEach(([selector, fn]) => {
		if (!hasElement(selector)) {
			return;
		}

		try { fn(); } catch (e) { console.error('AdminUI init error:', fn.name, e); }
	});
}
