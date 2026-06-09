function initLogViewer() {
	const viewers = document.querySelectorAll('[data-log-viewer]');

	viewers.forEach(viewer => {
		const entries = viewer.querySelectorAll('.mwp-log-entry');
		const filterButtons = viewer.querySelectorAll('[data-log-filter]');
		const searchInput = viewer.querySelector('[data-log-search]');

		let currentFilter = 'all';

		function updateVisibility() {
			const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
			entries.forEach(entry => {
				const matchesFilter = currentFilter === 'all' || entry.dataset.logLevel === currentFilter;
				const matchesSearch = !query || entry.textContent.toLowerCase().includes(query);
				entry.classList.toggle('is-hidden', !(matchesFilter && matchesSearch));
			});
		}

		viewer.addEventListener('click', event => {
			const button = event.target.closest('[data-log-filter]');
			if (!button) return;
			filterButtons.forEach(btn => btn.classList.remove('active'));
			button.classList.add('active');
			currentFilter = button.dataset.logFilter;
			updateVisibility();
		});

		if (searchInput) {
			searchInput.addEventListener('input', updateVisibility);
		}

		const activeBtn = viewer.querySelector('[data-log-filter].active');
		if (activeBtn) currentFilter = activeBtn.dataset.logFilter;
	});
}

function findPaginatedTableRoot(tableOrRoot) {
	const root = typeof tableOrRoot === 'string' ? document.querySelector(tableOrRoot) : tableOrRoot;

	if (!root) {
		return null;
	}

	return root.matches('[data-mwp-paginated-table], .mwp-table-wrap') ? root : root.closest('[data-mwp-paginated-table], .mwp-table-wrap');
}

function parseJsonData(value, fallback = {}) {
	if (!value) {
		return fallback;
	}

	try {
		return JSON.parse(value);
	} catch (error) {
		return fallback;
	}
}

function getEmptyTarget(table) {
	if (table.dataset.emptyTarget) {
		return document.getElementById(table.dataset.emptyTarget) || document.querySelector(table.dataset.emptyTarget);
	}

	if (table.dataset.emptySelector) {
		return document.querySelector(table.dataset.emptySelector);
	}

	return null;
}

function updatePaginationState(table, meta = {}) {
	const pagination = table.querySelector('[data-mwp-pagination], .mwp-pagination');
	const prevBtn = table.querySelector('[data-mwp-page="prev"]');
	const nextBtn = table.querySelector('[data-mwp-page="next"]');
	const info = table.querySelector('.mwp-pagination__info');
	const tbody = table.querySelector('tbody');
	const rows = tbody ? Array.from(tbody.querySelectorAll('tr')) : [];
	const perPage = parseInt(meta.per_page ?? table.dataset.perPage, 10) || 10;
	const total = parseInt(meta.total ?? table.dataset.total ?? rows.length, 10) || 0;
	const totalPages = Math.max(1, parseInt(meta.total_pages ?? table.dataset.totalPages, 10) || Math.ceil(total / perPage) || 1);
	const currentPage = Math.max(1, Math.min(parseInt(meta.page ?? meta.currentPage ?? table.dataset.currentPage, 10) || 1, totalPages));
	const emptyTarget = getEmptyTarget(table);
	const hasRows = rows.length > 0;

	table.dataset.currentPage = String(currentPage);
	table.dataset.perPage = String(perPage);
	table.dataset.total = String(total);
	table.dataset.totalPages = String(totalPages);
	table.classList.toggle('has-no-rows', !hasRows);

	if (prevBtn) prevBtn.disabled = currentPage <= 1;
	if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
	if (info) info.textContent = 'Page ' + currentPage + ' of ' + totalPages;
	if (pagination) pagination.hidden = totalPages <= 1;
	if (emptyTarget) emptyTarget.hidden = hasRows;

	table.dispatchEvent(new CustomEvent('mwp:table-page', {
		bubbles: true,
		detail: { currentPage, totalPages, perPage, total }
	}));
}

function setTableLoading(table, loading) {
	const buttons = table.querySelectorAll('[data-mwp-page]');
	table.classList.toggle('is-loading', loading);
	table.dataset.mwpLoading = loading ? 'true' : 'false';
	buttons.forEach(button => {
		button.disabled = loading ? true : button.disabled;
		button.classList.toggle('is-loading', loading);
	});
}

function replaceTableRows(tableOrRoot, rowsHtml = '', meta = {}) {
	const table = findPaginatedTableRoot(tableOrRoot);
	const tbody = table?.querySelector('tbody');

	if (!table || !tbody) {
		return null;
	}

	tbody.innerHTML = String(rowsHtml || '');

	const emptyTarget = getEmptyTarget(table);
	if (emptyTarget && meta.empty_html) {
		emptyTarget.outerHTML = String(meta.empty_html);
	}

	updatePaginationState(table, meta);
	return tbody;
}

async function loadTablePage(tableOrRoot, page, options = {}) {
	const table = findPaginatedTableRoot(tableOrRoot);

	if (!table) {
		return null;
	}

	const targetPage = parseInt(page, 10) || parseInt(table.dataset.currentPage, 10) || 1;
	const beforeEvent = new CustomEvent('mwp:table-before-load', {
		bubbles: true,
		cancelable: true,
		detail: { page: targetPage, options }
	});
	table.dispatchEvent(beforeEvent);

	if (beforeEvent.defaultPrevented) {
		return null;
	}

	const endpoint = options.endpoint || table.dataset.mwpEndpoint || window.ajaxurl || '';
	const action = options.action || table.dataset.mwpAction || '';

	if (!endpoint || !action) {
		return null;
	}

	const formData = new FormData();
	const params = {
		...parseJsonData(table.dataset.mwpFilters, {}),
		...(options.params || {})
	};
	const search = options.search ?? table.dataset.mwpSearch;

	formData.set('action', action);
	formData.set('page', String(targetPage));
	formData.set('per_page', String(options.perPage || table.dataset.perPage || 10));

	if (table.dataset.mwpNonce) {
		formData.set('security', table.dataset.mwpNonce);
	}

	if (search) {
		formData.set('search', search);
	}

	Object.entries(params).forEach(([key, value]) => {
		formData.set(key, value);
	});

	setTableLoading(table, true);
	let didReplaceRows = false;

	try {
		const response = await fetch(endpoint, {
			method: options.method || 'POST',
			body: formData,
			credentials: 'same-origin'
		});
		const contentType = response.headers.get('content-type') || '';
		const result = contentType.includes('application/json') ? await response.json() : await response.text();

		if (!response.ok || result?.success === false) {
			throw new Error(result?.data?.message || result?.message || response.statusText);
		}

		const data = result?.data || result;
		replaceTableRows(table, data.rows_html || '', data);
		didReplaceRows = true;
		table.dispatchEvent(new CustomEvent('mwp:table-load-success', {
			bubbles: true,
			detail: { data, response }
		}));
		return data;
	} catch (error) {
		table.dispatchEvent(new CustomEvent('mwp:table-load-error', {
			bubbles: true,
			detail: { error }
		}));
		throw error;
	} finally {
		setTableLoading(table, false);
		if (!didReplaceRows) {
			updatePaginationState(table);
		}
	}
}

function initPaginatedTables() {
	document.querySelectorAll('[data-mwp-paginated-table]').forEach(table => {
		if (table.dataset.mwpPaginationReady === 'true') {
			return;
		}

		table.dataset.mwpPaginationReady = 'true';
		const tbody = table.querySelector('tbody');
		const pagination = table.querySelector('[data-mwp-pagination], .mwp-pagination');
		const perPage = parseInt(table.dataset.perPage, 10) || 10;
		const initialPage = table.dataset.initialPage || table.dataset.currentPage || '1';
		const paginationMode = table.dataset.paginationMode || 'client';
		let currentPage = initialPage === 'last' ? 1 : (parseInt(initialPage, 10) || 1);

		if (paginationMode === 'server') {
			table.addEventListener('click', event => {
				const button = event.target.closest('[data-mwp-page]');
				if (!button) {
					return;
				}

				let page;
				if (button.dataset.mwpPage === 'prev') {
					page = (parseInt(table.dataset.currentPage, 10) || 1) - 1;
				} else if (button.dataset.mwpPage === 'next') {
					page = (parseInt(table.dataset.currentPage, 10) || 1) + 1;
				} else {
					page = parseInt(button.dataset.mwpPage, 10);
				}

				if (page) {
					loadTablePage(table, page).catch(error => {
						if (window.MatterAdminUI?.notice) {
							window.MatterAdminUI.notice(error.message, 'error');
						}
					});
				}
			});

			table.addEventListener('mwp:table-refresh', event => {
				const detail = event.detail || {};
				if (detail.rowsHtml !== undefined) {
					replaceTableRows(table, detail.rowsHtml, detail.meta || {});
					return;
				}

				loadTablePage(table, detail.page || table.dataset.currentPage || 1).catch(() => {});
			});

			updatePaginationState(table);
			return;
		}

		function getRows() {
			return Array.from(table.querySelectorAll('tbody tr'));
		}

		function getTotalPages(rows) {
			return Math.max(1, Math.ceil(rows.length / perPage));
		}

		function showPage(page = currentPage) {
			const rows = getRows();
			const totalPages = getTotalPages(rows);
			const targetPage = page === 'last' ? totalPages : page;
			currentPage = Math.max(1, Math.min(targetPage, totalPages));
			const start = (currentPage - 1) * perPage;
			const end = start + perPage;
			const emptyTarget = getEmptyTarget(table);
			const hasRows = rows.length > 0;

			rows.forEach((row, index) => {
				row.classList.toggle('is-hidden', index < start || index >= end);
			});

			const prevBtn = table.querySelector('[data-mwp-page="prev"]');
			const nextBtn = table.querySelector('[data-mwp-page="next"]');
			const info = table.querySelector('.mwp-pagination__info');

			if (prevBtn) prevBtn.disabled = currentPage <= 1;
			if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
			if (info) info.textContent = 'Page ' + currentPage + ' of ' + totalPages;
			if (pagination) pagination.hidden = totalPages <= 1;
			if (emptyTarget) emptyTarget.hidden = hasRows;
			table.classList.toggle('has-no-rows', !hasRows);
			table.dataset.currentPage = String(currentPage);
			table.dispatchEvent(new CustomEvent('mwp:table-page', {
				bubbles: true,
				detail: { currentPage, totalPages, perPage, total: rows.length }
			}));
		}

		table.addEventListener('click', event => {
			const button = event.target.closest('[data-mwp-page]');
			if (!button) {
				return;
			}

			let page;
			if (button.dataset.mwpPage === 'prev') {
				page = currentPage - 1;
			} else if (button.dataset.mwpPage === 'next') {
				page = currentPage + 1;
			} else {
				page = parseInt(button.dataset.mwpPage, 10);
			}

			if (page) {
				showPage(page);
			}
		});

		if (tbody) {
			const observer = new MutationObserver(() => showPage(currentPage));
			observer.observe(tbody, { childList: true });
		}

		table.addEventListener('mwp:table-refresh', event => showPage(event.detail?.page || currentPage));
		showPage(initialPage === 'last' ? 'last' : currentPage);
	});
}

export { initLogViewer, initPaginatedTables, loadTablePage, replaceTableRows };
