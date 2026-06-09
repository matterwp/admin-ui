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

function getTableComponent(table) {
	return table.closest('[data-mwp-data-table]') || table.parentElement;
}

function getTableControls(table) {
	return getTableComponent(table)?.querySelector('[data-mwp-table-filters]') || null;
}

function getControlParams(table) {
	const controls = getTableControls(table);
	const params = {};

	controls?.querySelectorAll('[data-mwp-table-filter].active').forEach(button => {
		const name = button.dataset.mwpTableFilter;
		const value = button.dataset.mwpFilterValue;

		if (name && value && button.dataset.mwpFilterEmpty !== 'true') {
			params[name] = value;
		}
	});

	const search = controls?.querySelector('[data-mwp-table-search]');
	if (search?.value.trim()) {
		params.search = search.value.trim();
	}

	return params;
}

function updateSortHeaders(table) {
	const orderby = table.dataset.mwpOrderby || '';
	const order = table.dataset.mwpOrder === 'desc' ? 'desc' : 'asc';

	table.querySelectorAll('[data-mwp-table-sort]').forEach(button => {
		const th = button.closest('th');
		const active = button.dataset.mwpTableSort === orderby;
		button.dataset.mwpSortOrder = active ? order : 'asc';
		button.classList.toggle('is-active', active);

		if (th) {
			if (active) {
				th.setAttribute('aria-sort', order === 'desc' ? 'descending' : 'ascending');
			} else {
				th.removeAttribute('aria-sort');
			}
		}
	});
}

function rowMatchesControls(row, params) {
	return Object.entries(params).every(([name, value]) => {
		if (name === 'search') {
			return row.textContent.toLowerCase().includes(String(value).toLowerCase());
		}

		return String(row.getAttribute(`data-mwp-filter-${name}`) || '') === String(value);
	});
}

function sortClientRows(rows, table) {
	const orderby = table.dataset.mwpOrderby || '';
	const direction = table.dataset.mwpOrder === 'desc' ? -1 : 1;

	if (!orderby) {
		return rows;
	}

	return [...rows].sort((left, right) => {
		const leftValue = left.querySelector(`[data-mwp-column="${orderby}"]`)?.dataset.mwpSortValue || '';
		const rightValue = right.querySelector(`[data-mwp-column="${orderby}"]`)?.dataset.mwpSortValue || '';
		const leftNumber = Number(leftValue);
		const rightNumber = Number(rightValue);

		if (leftValue !== '' && rightValue !== '' && Number.isFinite(leftNumber) && Number.isFinite(rightNumber)) {
			return (leftNumber - rightNumber) * direction;
		}

		return leftValue.localeCompare(rightValue, undefined, { numeric: true, sensitivity: 'base' }) * direction;
	});
}

function getTableStatus(table, type) {
	return table.querySelector(`[data-mwp-table-${type}]`);
}

function setTableError(table, error = null) {
	const status = getTableStatus(table, 'error');
	if (!status) {
		return;
	}

	const message = status.querySelector('span');
	if (message && error?.message) {
		message.textContent = error.message;
	}

	status.hidden = !error;
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
	const status = getTableStatus(table, 'loading');
	table.classList.toggle('is-loading', loading);
	table.dataset.mwpLoading = loading ? 'true' : 'false';
	if (status) status.hidden = !loading;
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
		const template = document.createElement('template');
		template.innerHTML = String(meta.empty_html).trim();
		const replacement = template.content.firstElementChild;

		if (replacement) {
			if (emptyTarget.id && !replacement.id) {
				replacement.id = emptyTarget.id;
			}
			emptyTarget.replaceWith(replacement);
		}
	}

	setTableError(table);
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
		...getControlParams(table),
		...(options.params || {})
	};
	const search = options.search ?? params.search ?? table.dataset.mwpSearch;

	formData.set('action', action);
	formData.set('page', String(targetPage));
	formData.set('per_page', String(options.perPage || table.dataset.perPage || 10));

	if (table.dataset.mwpNonce) {
		formData.set('security', table.dataset.mwpNonce);
	}

	if (search) {
		formData.set('search', search);
		delete params.search;
	}

	if (table.dataset.mwpOrderby) {
		formData.set('orderby', table.dataset.mwpOrderby);
		formData.set('order', table.dataset.mwpOrder === 'desc' ? 'desc' : 'asc');
	}

	Object.entries(params).forEach(([key, value]) => {
		formData.set(key, value);
	});

	setTableError(table);
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
		setTableError(table, error);
		throw error;
	} finally {
		setTableLoading(table, false);
		if (!didReplaceRows) {
			updatePaginationState(table);
		}
	}
}

function initTableControls(table) {
	const controls = getTableControls(table);
	let searchTimer = null;

	if (controls && controls.dataset.mwpTableFiltersReady !== 'true') {
		controls.dataset.mwpTableFiltersReady = 'true';

		controls.addEventListener('click', event => {
			const button = event.target.closest('[data-mwp-table-filter]');
			if (!button) {
				return;
			}

			const name = button.dataset.mwpTableFilter;
			controls.querySelectorAll(`[data-mwp-table-filter="${name}"]`).forEach(filterButton => {
				const active = filterButton === button;
				filterButton.classList.toggle('active', active);
				filterButton.setAttribute('aria-pressed', active ? 'true' : 'false');
			});

			table.dataset.mwpFilters = JSON.stringify(getControlParams(table));
			table.dispatchEvent(new CustomEvent('mwp:table-filter', {
				bubbles: true,
				detail: { filters: getControlParams(table) }
			}));
			table.dispatchEvent(new CustomEvent('mwp:table-refresh', {
				bubbles: true,
				detail: { page: 1 }
			}));
		});

		controls.querySelector('[data-mwp-table-search]')?.addEventListener('input', () => {
			window.clearTimeout(searchTimer);
			searchTimer = window.setTimeout(() => {
				const params = getControlParams(table);
				table.dataset.mwpFilters = JSON.stringify(params);
				table.dataset.mwpSearch = params.search || '';
				table.dispatchEvent(new CustomEvent('mwp:table-filter', {
					bubbles: true,
					detail: { filters: params }
				}));
				table.dispatchEvent(new CustomEvent('mwp:table-refresh', {
					bubbles: true,
					detail: { page: 1 }
				}));
			}, 250);
		});
	}

	table.addEventListener('click', event => {
		const button = event.target.closest('[data-mwp-table-sort]');
		if (!button) {
			return;
		}

		const sortKey = button.dataset.mwpTableSort;
		const currentKey = table.dataset.mwpOrderby || '';
		const currentOrder = table.dataset.mwpOrder === 'desc' ? 'desc' : 'asc';
		const nextOrder = currentKey === sortKey && currentOrder === 'asc' ? 'desc' : 'asc';

		table.dataset.mwpOrderby = sortKey;
		table.dataset.mwpOrder = nextOrder;
		updateSortHeaders(table);
		table.dispatchEvent(new CustomEvent('mwp:table-sort', {
			bubbles: true,
			detail: { orderby: sortKey, order: nextOrder }
		}));
		table.dispatchEvent(new CustomEvent('mwp:table-refresh', {
			bubbles: true,
			detail: { page: 1 }
		}));
	});

	updateSortHeaders(table);
}

function initPaginatedTables() {
	document.querySelectorAll('[data-mwp-paginated-table]').forEach(table => {
		if (table.dataset.mwpPaginationReady === 'true') {
			return;
		}

		table.dataset.mwpPaginationReady = 'true';
		initTableControls(table);
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
			if (!pagination) {
				return 1;
			}

			return Math.max(1, Math.ceil(rows.length / perPage));
		}

		function showPage(page = currentPage) {
			const rows = getRows();
			const params = getControlParams(table);
			const filteredRows = sortClientRows(rows.filter(row => rowMatchesControls(row, params)), table);
			const totalPages = getTotalPages(filteredRows);
			const targetPage = page === 'last' ? totalPages : page;
			currentPage = Math.max(1, Math.min(targetPage, totalPages));
			const pageSize = pagination ? perPage : Math.max(1, filteredRows.length);
			const start = (currentPage - 1) * pageSize;
			const end = start + pageSize;
			const emptyTarget = getEmptyTarget(table);
			const hasRows = filteredRows.length > 0;
			const filteredSet = new Set(filteredRows);
			const desiredOrder = [...filteredRows, ...rows.filter(row => !filteredSet.has(row))];
			const orderChanged = desiredOrder.some((row, index) => rows[index] !== row);

			if (orderChanged) {
				desiredOrder.forEach(row => tbody?.appendChild(row));
			}

			rows.forEach(row => row.classList.add('is-hidden'));
			filteredRows.forEach((row, index) => {
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
				detail: { currentPage, totalPages, perPage: pageSize, total: filteredRows.length }
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
