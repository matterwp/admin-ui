function initLogViewer() {
	initLogFilters();
	initLogSearch();
}

function initLogFilters() {
	document.querySelectorAll('[data-log-filter]').forEach(button => {
		button.addEventListener('click', () => {
			const filter = button.dataset.logFilter;
			const container = button.closest('[data-log-viewer]');
			const entries = container ? container.querySelectorAll('.mwp-log-entry') : document.querySelectorAll('.mwp-log-entry');

			document.querySelectorAll('[data-log-filter]').forEach(btn => btn.classList.remove('active'));
			button.classList.add('active');

			entries.forEach(entry => {
				if (filter === 'all') {
					entry.classList.remove('is-hidden');
				} else {
					const level = entry.dataset.logLevel || 'info';
					entry.classList.toggle('is-hidden', level !== filter);
				}
			});
		});
	});
}

function initLogSearch() {
	document.querySelectorAll('[data-log-search]').forEach(input => {
		input.addEventListener('input', () => {
			const query = input.value.toLowerCase().trim();
			const container = input.closest('[data-log-viewer]');
			const entries = container ? container.querySelectorAll('.mwp-log-entry') : document.querySelectorAll('.mwp-log-entry');

			entries.forEach(entry => {
				const message = entry.querySelector('.mwp-log-entry__message');
				const text = message ? message.textContent.toLowerCase() : '';
				entry.classList.toggle('is-hidden', query !== '' && !text.includes(query));
			});
		});
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
