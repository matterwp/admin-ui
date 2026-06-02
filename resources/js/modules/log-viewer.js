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
		let currentPage = initialPage === 'last' ? 1 : (parseInt(initialPage, 10) || 1);

		function getRows() {
			return Array.from(table.querySelectorAll('tbody tr'));
		}

		function getTotalPages(rows) {
			return Math.max(1, Math.ceil(rows.length / perPage));
		}

		function getEmptyTarget() {
			if (table.dataset.emptyTarget) {
				return document.getElementById(table.dataset.emptyTarget) || document.querySelector(table.dataset.emptyTarget);
			}

			if (table.dataset.emptySelector) {
				return document.querySelector(table.dataset.emptySelector);
			}

			return null;
		}

		function showPage(page = currentPage) {
			const rows = getRows();
			const totalPages = getTotalPages(rows);
			const targetPage = page === 'last' ? totalPages : page;
			currentPage = Math.max(1, Math.min(targetPage, totalPages));
			const start = (currentPage - 1) * perPage;
			const end = start + perPage;
			const emptyTarget = getEmptyTarget();
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

		table.addEventListener('mwp:table-refresh', () => showPage(currentPage));
		showPage(initialPage === 'last' ? 'last' : currentPage);
	});
}

export { initLogViewer, initPaginatedTables };
