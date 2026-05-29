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
		const rows = table.querySelectorAll('tbody tr');
		const perPage = parseInt(table.dataset.perPage, 10) || 10;
		let currentPage = parseInt(table.dataset.currentPage, 10) || 1;
		const total = parseInt(table.dataset.total, 10) || rows.length;
		const totalPages = Math.max(1, Math.ceil(total / perPage));

		function showPage(page) {
			currentPage = Math.max(1, Math.min(page, totalPages));
			const start = (currentPage - 1) * perPage;
			const end = start + perPage;

			rows.forEach((row, index) => {
				row.classList.toggle('is-hidden', index < start || index >= end);
			});

			const prevBtn = table.querySelector('[data-mwp-page="prev"]');
			const nextBtn = table.querySelector('[data-mwp-page="next"]');
			const info = table.querySelector('.mwp-pagination__info');

			if (prevBtn) prevBtn.disabled = currentPage <= 1;
			if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
			if (info) info.textContent = 'Page ' + currentPage + ' of ' + totalPages;
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

			if (page && page >= 1 && page <= totalPages) {
				showPage(page);
			}
		});

		showPage(currentPage);
	});
}

export { initLogViewer, initPaginatedTables };
