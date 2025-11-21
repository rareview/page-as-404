/**
 * Admin scripts.
 *
 * This file handles admin-specific functionality like highlighting the settings field.
 *
 * @author Rareview <hello@rareview.com>
 *
 * @package Page As 404
 */

document.addEventListener('DOMContentLoaded', function() {
	const params = new URLSearchParams(window.location.search);
	if (params.get('highlight') === 'rareview-pa404-select') {
		const element = document.getElementById('rareview_pa404_page_id');
		if (element) {
			element.classList.add('rareview-pa404-highlight-setting');
			setTimeout(() => {
				element.classList.remove('rareview-pa404-highlight-setting');
				const url = new URL(window.location);
				url.searchParams.delete('highlight');
				window.history.replaceState({}, document.title, url);
			}, 2000);
		}
	}
});

