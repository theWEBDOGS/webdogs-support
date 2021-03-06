(typeof wds === 'object') && (function($) {
	$('.plugin-notes-container.editable, .plugin-notes-edit').on('click', function() {
		var $container = $(this).closest('.plugin-notes-container')
		  , $textarea = $('<textarea class="widefat">')
		  , $saveButton = $('<button class="button-primary button button-small">')
		  , $cancelButton = $('<button class="button-secondary button button-small">')
		;

		if($container.hasClass('editing')) {
			return;
		}

		$container.addClass('editing');

		$saveButton.text(wds.l10n.save);
		$cancelButton.text(wds.l10n.cancel);

		$container.data('plugin-markup', $container.html());

		$textarea.val($container.data('plugin-notes'));

		function adjust() {
			$textarea.height(1);
			$textarea.height($textarea[0].scrollHeight);
		}

		// Make sure the text area is the same number of lines contained within
		$textarea.on('keyup', adjust);

		$container.html('');
		$container.append($textarea, $saveButton, ' ', $cancelButton);

		adjust();

		$textarea.focus();

		function commit(body) {
			var $p = $('<p>'), html;

			if(typeof body === 'undefined') {
				html = $container.data('plugin-markup')
			}
			else {
				html = body.markup;
			}

			$container.html(html);
			$container.removeClass('editing');
		}

		$saveButton.on('click', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();

			$container.data('plugin-notes', $textarea.val());
			wp.ajax.post('wds-plugin-notes-save', {
				plugin: $container.parents('tr').attr('data-plugin'),
				notes: $textarea.val(),
				nonce: wds.nonce
			}).done(commit).fail(function() {
				$container.addClass('plugin-notes-error');
			});


		});

		$cancelButton.on('click', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();
			commit();
		});
	});
})(jQuery);
