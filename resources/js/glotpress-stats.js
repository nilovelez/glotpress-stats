jQuery( document ).ready( function ( $ ) {
	if ( $( '#stats-table' ).length && typeof GLOTSTATS_TRANSLATION !== 'undefined' ) {
		$( '#stats-table' ).DataTable( {
			pageLength: 50,
			searching: false,
			lengthMenu: [ [ 25, 50, 100, -1 ], [ 25, 50, 100, 'All' ] ],
			language: {
				decimal: GLOTSTATS_TRANSLATION.decimal,
				emptyTable: GLOTSTATS_TRANSLATION.emptyTable,
				info: GLOTSTATS_TRANSLATION.info,
				infoEmpty: GLOTSTATS_TRANSLATION.infoEmpty,
				infoFiltered: GLOTSTATS_TRANSLATION.infoFiltered,
				thousands: GLOTSTATS_TRANSLATION.thousands,
				lengthMenu: GLOTSTATS_TRANSLATION.lengthMenu,
				loadingRecords: GLOTSTATS_TRANSLATION.loadingRecords,
				processing: GLOTSTATS_TRANSLATION.processing,
				search: GLOTSTATS_TRANSLATION.search,
				zeroRecords: GLOTSTATS_TRANSLATION.zeroRecords,
				paginate: {
					first: GLOTSTATS_TRANSLATION.paginate_first,
					last: GLOTSTATS_TRANSLATION.paginate_last,
					next: GLOTSTATS_TRANSLATION.paginate_next,
					previous: GLOTSTATS_TRANSLATION.paginate_previous,
				},
				aria: {
					sortAscending: GLOTSTATS_TRANSLATION.aria_sortAscending,
					sortDescending: GLOTSTATS_TRANSLATION.aria_sortDescending,
				},
			},
		} );
	}

	function copyHtmlFallback( html ) {
		var $container = $( '<div>' )
			.css( {
				position: 'fixed',
				left: '-9999px',
				top: '0',
			} )
			.attr( 'contenteditable', 'true' )
			.html( html )
			.appendTo( 'body' );

		var range = document.createRange();
		var selection = window.getSelection();

		range.selectNodeContents( $container[0] );
		selection.removeAllRanges();
		selection.addRange( range );

		document.execCommand( 'copy' );
		selection.removeAllRanges();
		$container.remove();
	}

	function copyBlockToClipboard( $element ) {
		var $content = $element.find( '.glotpress-stats-copy-content' );

		if ( ! $content.length ) {
			return;
		}

		var html = $content.html();
		var plainText = $content.text();

		if ( ! html ) {
			return;
		}

		var copiedLabel =
			typeof GLOTSTATS_COPY !== 'undefined' ? GLOTSTATS_COPY.copied : 'Copied to clipboard';
		var clickToCopyLabel =
			typeof GLOTSTATS_COPY !== 'undefined' ? GLOTSTATS_COPY.clickToCopy : 'Click to copy';
		var $hint = $element.find( '.glotpress-stats-copy-hint' );

		function showCopiedState() {
			$hint.addClass( 'glotpress-stats-copy-hint--copied' );
			$hint.text( copiedLabel );

			window.setTimeout( function () {
				$hint.removeClass( 'glotpress-stats-copy-hint--copied' );
				$hint.text( clickToCopyLabel );
			}, 2000 );
		}

		if ( navigator.clipboard && window.ClipboardItem ) {
			navigator.clipboard
				.write( [
					new ClipboardItem( {
						'text/html': new Blob( [ html ], { type: 'text/html' } ),
						'text/plain': new Blob( [ plainText ], { type: 'text/plain' } ),
					} ),
				] )
				.then( showCopiedState )
				.catch( function () {
					copyHtmlFallback( html );
					showCopiedState();
				} );
			return;
		}

		copyHtmlFallback( html );
		showCopiedState();
	}

	$( '.glotpress-stats-copyable' ).on( 'click', function ( event ) {
		if ( $( event.target ).closest( 'a' ).length ) {
			return;
		}

		copyBlockToClipboard( $( this ) );
	} );

	$( '.glotpress-stats-copyable' ).on( 'keydown', function ( event ) {
		if ( event.key !== 'Enter' && event.key !== ' ' ) {
			return;
		}

		event.preventDefault();
		copyBlockToClipboard( $( this ) );
	} );
} );
