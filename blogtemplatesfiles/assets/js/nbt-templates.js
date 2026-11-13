jQuery(document).ready(function($) {
	var nbt_settings = {
		init: function() {
			var postboxes = $('.postbox');

			var postboxes_checkboxes = postboxes.find('input[type=checkbox]');

			var all_selectors = postboxes.find('input.all-selector');
			
			
			all_selectors.each(function(i,selector) {
				selector = $(selector);
				if ( selector.prop('checked') ) {
					var list_items = nbt_settings.get_list(selector);
					list_items.prop('disabled',true);
				}	
			});

			postboxes_checkboxes.on('change', function() {
				var item = $(this);

				if ( item.hasClass('all-selector') ) {
					var list_items = nbt_settings.get_list(item);
					
					if ( item.prop('checked') ) {
						list_items.prop('checked', false);
						list_items.prop('disabled',  true);
					}
					else {
						list_items.prop('disabled',  false);
					}
				}
			});

		},
		get_list: function( item ) {
			return item
				.closest('ul')
				.find('input[type=checkbox]' )
				.not('#' + item.prop('id'));
		}
	}
	nbt_settings.init();
	

	var searchInput = document.getElementById('search_for_blog');
	if (!searchInput) {
		return;
	}

	var cache = Object.create(null);
	var results = [];
	var activeIndex = -1;
	var requestController = null;
	var requestTimer = null;
	var requestVersion = 0;
	var listId = 'nbt-site-search-results';
	var wrapper = document.createElement('span');
	var list = document.createElement('ul');
	var status = document.createElement('span');

	wrapper.className = 'nbt-combobox';
	searchInput.parentNode.insertBefore(wrapper, searchInput);
	wrapper.appendChild(searchInput);
	list.id = listId;
	list.className = 'nbt-combobox__results';
	list.setAttribute('role', 'listbox');
	list.hidden = true;
	wrapper.appendChild(list);
	status.setAttribute('role', 'status');
	wrapper.appendChild(status);

	searchInput.setAttribute('role', 'combobox');
	searchInput.setAttribute('aria-autocomplete', 'list');
	searchInput.setAttribute('aria-controls', listId);
	searchInput.setAttribute('aria-expanded', 'false');
	searchInput.setAttribute('autocomplete', 'off');

	function cancelRequest() {
		window.clearTimeout(requestTimer);
		requestVersion++;
		if (requestController) {
			requestController.abort();
			requestController = null;
		}
	}

	function closeResults() {
		activeIndex = -1;
		list.hidden = true;
		searchInput.setAttribute('aria-expanded', 'false');
		searchInput.removeAttribute('aria-activedescendant');
	}

	function selectItem(item) {
		cancelRequest();
		searchInput.value = item.blog_name;
		$('#copy_blog_id').val(item.blog_id);
		closeResults();
	}

	function setActiveItem(index) {
		var options = list.querySelectorAll('[role="option"]');
		if (!options.length) {
			return;
		}
		activeIndex = (index + options.length) % options.length;
		options.forEach(function (option, optionIndex) {
			option.setAttribute('aria-selected', optionIndex === activeIndex ? 'true' : 'false');
		});
		searchInput.setAttribute('aria-activedescendant', options[activeIndex].id);
		options[activeIndex].scrollIntoView({block: 'nearest'});
	}

	function renderResults(items) {
		results = items;
		list.replaceChildren();
		results.forEach(function (item, index) {
			var option = document.createElement('li');
			option.id = listId + '-' + index;
			option.className = 'nbt-combobox__option';
			option.setAttribute('role', 'option');
			option.setAttribute('aria-selected', 'false');
			option.textContent = item.path + ' [' + item.blog_name + ']';
			option.addEventListener('mousedown', function (event) {
				event.preventDefault();
				selectItem(item);
			});
			list.appendChild(option);
		});
		list.hidden = results.length === 0;
		searchInput.setAttribute('aria-expanded', results.length ? 'true' : 'false');
		activeIndex = -1;
		searchInput.removeAttribute('aria-activedescendant');
	}

	function requestResults(term) {
		var version = requestVersion;
		if (cache[term]) {
			renderResults(cache[term]);
			return;
		}
		requestController = new AbortController();
		var params = new URLSearchParams({action: 'nbt_get_sites_search', term: term, nonce: export_to_text_js.nonce});

		fetch(export_to_text_js.ajaxurl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
			body: params.toString(),
			signal: requestController.signal
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Site search failed');
				}
				return response.json();
			})
			.then(function (items) {
				if (version !== requestVersion) {
					return;
				}
				if (!Array.isArray(items) || !items.every(function (item) {
					return item && typeof item.blog_name === 'string' && typeof item.path === 'string' &&
						((typeof item.blog_id === 'string' && /^[1-9]\d*$/.test(item.blog_id)) ||
							(Number.isInteger(item.blog_id) && item.blog_id > 0));
				})) {
					throw new Error('Invalid site search response');
				}
				cache[term] = items;
				renderResults(items);
			})
			.catch(function (error) {
				if (error.name !== 'AbortError' && version === requestVersion) {
					closeResults();
					status.textContent = export_to_text_js.searchError;
					console.error('Neue Blog-Vorlagen:', error);
				}
			});
	}

	searchInput.addEventListener('input', function () {
		cancelRequest();
		closeResults();
		status.textContent = '';
		$('#copy_blog_id').val('');
		if (searchInput.value.trim().length < 2) {
			closeResults();
			return;
		}
		requestTimer = window.setTimeout(function () {
			requestResults(searchInput.value.trim());
		}, 180);
	});

	searchInput.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			cancelRequest();
			closeResults();
			return;
		}
		if (list.hidden || !results.length) {
			return;
		}
		if (event.key === 'ArrowDown') {
			event.preventDefault();
			setActiveItem(activeIndex + 1);
		} else if (event.key === 'ArrowUp') {
			event.preventDefault();
			setActiveItem(activeIndex - 1);
		} else if (event.key === 'Enter' && activeIndex >= 0) {
			event.preventDefault();
			selectItem(results[activeIndex]);
		}
	});

	searchInput.addEventListener('blur', function () {
		cancelRequest();
		window.setTimeout(closeResults, 100);
	});
});