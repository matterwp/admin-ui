function initClipboard() {
	document.addEventListener('click', event => {
		const trigger = event.target.closest('[data-copy-value]');

		if (!trigger) {
			return;
		}

		const text = trigger.dataset.copyValue;

		if (!text) {
			return;
		}

		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(text).then(() => {
				showCopiedFeedback(trigger);
			}).catch(() => {
				fallbackCopy(text, trigger);
			});
		} else {
			fallbackCopy(text, trigger);
		}
	});
}

function fallbackCopy(text, trigger) {
	const textarea = document.createElement('textarea');
	textarea.value = text;
	textarea.style.position = 'fixed';
	textarea.style.opacity = '0';
	document.body.appendChild(textarea);
	textarea.select();

	try {
		document.execCommand('copy');
		showCopiedFeedback(trigger);
	} catch (e) {
		// Clipboard not available — do nothing.
	}

	document.body.removeChild(textarea);
}

function showCopiedFeedback(trigger) {
	const feedback = trigger.dataset.copyFeedback || 'Copied!';
	const tooltip = document.createElement('span');
	tooltip.className = 'mwp-copy-tooltip';
	tooltip.textContent = feedback;
	tooltip.setAttribute('aria-live', 'polite');
	trigger.style.position = 'relative';
	trigger.appendChild(tooltip);

	setTimeout(() => {
		tooltip.remove();
	}, 1500);
}

export { initClipboard };
