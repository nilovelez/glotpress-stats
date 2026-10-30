<?php
/**
 * Fetches and parses the translate.wordpress.org stats page of a locale and renders the views.
 *
 * @package GlotPress_Stats
 */

namespace glotstats;

/**
 * Parses the stats of a locale and outputs the requested view.
 *
 * @param string $locale         Locale slug, as used in translate.wordpress.org URLs.
 * @param string $directory      Directory to read the stats from: 'plugins' or 'themes'.
 * @param string $view           View to output: 'top' or 'tasks'.
 * @param array  $exclude        Slugs of the projects to leave out.
 * @param array  $table_language DataTables language strings for the 'top' view.
 * @return string|false|null An error message when the stats can't be read, false on invalid arguments, null on success.
 */
function parse( $locale = false, $directory = false, $view = 'top', $exclude = array(), $table_language = array() ) {
	static $instance = 0;

	if ( empty( $locale ) || empty( $directory ) ) {
		return false;
	}

	$error_message = __( 'An error occurred while fetching data from the WordPress directory', 'glotpress-stats' );

	if ( ! defined( 'GLOTSTATS_DEBUG' ) ) {
		// Random number to break the cache.
		$rand = wp_rand( 1000, 9999 );
		$url  = 'https://translate.wordpress.org/locale/' . $locale . '/default/stats/' . $directory . '/?rand=' . $rand;

		$file = wp_remote_get( $url );
		if ( is_wp_error( $file ) || 200 !== wp_remote_retrieve_response_code( $file ) ) {
			return $error_message;
		}
		$file = wp_remote_retrieve_body( $file );
	} else {
		echo '<h2>[debugging]</h2>';
		$file = file_get_contents( plugin_dir_path( __FILE__ ) . '/demo_data/top_' . $directory . '_es.html' );
	}

	$file  = str_replace( '&', '&amp;', $file );
	$start = strpos( $file, '<tbody' );
	$end   = strpos( $file, '</tbody>' );

	if ( false === $start || false === $end ) {
		return $error_message;
	}

	$file = substr( $file, $start, ( $end - $start ) + 8 );

	try {
		$xml = new \SimpleXMLElement( $file );
	} catch ( \Exception $e ) {
		return $error_message;
	}

	$input = array();

	foreach ( $xml as $item ) {

		$input[] = array(
			'title'                     => (string) $item->th->a,
			'installs'                  => (int) $item->th['data-sort-value'],
			'link'                      => (string) $item->th->a['href'],
			'slug'                      => (string) preg_replace( '/^.*\/([^\/]+)\/?$/', '$1', $item->th->a['href'] ),
			'percent'                   => (int) $item->td[0]['data-sort-value'],
			'language_link'             => (string) $item->td[0]->a['href'],
			'translated'                => (int) $item->td[1]['data-sort-value'],
			'untranslated'              => (int) $item->td[2]['data-sort-value'],
			'untranslated_link_themes'  => (string) $item->td[2]->a['href'],
			'untranslated_link_plugins' => (string) $item->th->a['href'] . 'stable/' . $locale . '/default/?filters[status]=untranslated',
			'fuzzy'                     => (int) $item->td[3]['data-sort-value'],
			'waiting'                   => (int) $item->td[4]['data-sort-value'],
		);

	}

	// The first instance on a page keeps the original ids, so existing custom CSS still applies.
	++$instance;
	$id_suffix = ( 1 === $instance ) ? '' : '-' . $instance;

	$base_url = 'https://translate.wordpress.org';
	$count    = count( $input );

	if ( 'top' === $view ) {
		?>
		<div class="stats-table">
			<table id="stats-table<?php echo esc_attr( $id_suffix ); ?>" class="glotpress-stats-table" data-language="<?php echo esc_attr( wp_json_encode( $table_language ) ); ?>">
				<thead>
					<tr>
						<th style="width: 60px;">#</th>
						<th><?php esc_html_e( 'Name', 'glotpress-stats' ); ?></th>
						<th><?php esc_html_e( 'Installs', 'glotpress-stats' ); ?></th>
						<th><?php esc_html_e( 'Untranslated', 'glotpress-stats' ); ?></th>
				</tr>
			</thead>
			<tbody>
		<?php
	}

	$tasks_blocks  = array();
	$clean         = true;
	$remaining     = array(
		'top100' => array(
			'projects' => 0,
			'strings'  => 0,
		),
		'top200' => array(
			'projects' => 0,
			'strings'  => 0,
		),
		'total'  => array(
			'projects' => 0,
			'strings'  => 0,
		),
	);
	$finished_top  = 0;
	$printed_tasks = 0;

	for ( $i = 0; $i < $count; $i++ ) {
		$row = $input[ $i ];

		if ( in_array( $row['slug'], $exclude, true ) ) {
			continue;
		}

		if ( 0 === $row['untranslated'] ) {
			if ( $clean ) {
				$finished_top = $i + 1;
			}
		} else {

			// $i is zero-based: the top 100 are positions 0 to 99.
			if ( $i < 100 ) {
				++$remaining['top100']['projects'];
				$remaining['top100']['strings'] += $row['untranslated'];
			}
			if ( $i < 200 ) {
				++$remaining['top200']['projects'];
				$remaining['top200']['strings'] += $row['untranslated'];
			}
			++$remaining['total']['projects'];
			$remaining['total']['strings'] += $row['untranslated'];

			$untranslated_link = $row[ 'untranslated_link_' . $directory ];

			if ( $clean ) {
				$clean = false;
			}

			if ( 'top' === $view ) {
				echo '<tr data-slug="' . esc_attr( $row['slug'] ) . '">' . "\n";
				echo '<th>' . esc_html( $i + 1 ) . '</th>';
				echo '<th><a href="' . esc_url( $base_url . $row['language_link'] ) . '">' . esc_html( $row['title'] ) . '</a></td>';
				echo '<td>' . esc_html( number_format( $row['installs'], 0, '', '.' ) ) . '+</td>';
				echo '<td><a href="' . esc_url( $base_url . $untranslated_link ) . '" rel="nofollow">' . esc_html( $row['untranslated'] ) . '</a></td>';
				echo '</tr>' . "\n";
			} elseif ( 'tasks' === $view && $printed_tasks < 3 ) {
				$tasks_blocks[] = array(
					'title'        => $row['title'],
					'installs'     => number_format( $row['installs'], 0, '', '.' ),
					'untranslated' => $row['untranslated'],
					'url'          => $base_url . $untranslated_link,
				);
			}
			++$printed_tasks;
		}
	}
	if ( 'top' === $view ) {
		echo '</tbody></table></div>';
	} elseif ( 'tasks' === $view && ! empty( $tasks_blocks ) ) {
		$clipboard_blocks = array();

		foreach ( $tasks_blocks as $task ) {
			$installs_line = sprintf(
				'(%s+ %s)',
				$task['installs'],
				__( 'installs', 'glotpress-stats' )
			);
			$strings_line  = sprintf(
				/* translators: %d: number of untranslated strings */
				__( '%d untranslated strings', 'glotpress-stats' ),
				$task['untranslated']
			);

			$clipboard_blocks[] = sprintf(
				'<p><strong>%1$s</strong> %2$s<br>%3$s<br><a href="%4$s">%5$s</a></p>',
				esc_html( $task['title'] ),
				esc_html( $installs_line ),
				esc_html( $strings_line ),
				esc_url( $task['url'] ),
				esc_html( $task['url'] )
			);
		}

		$clipboard_html = implode( "\n", $clipboard_blocks );
		$allowed_html   = array(
			'p'      => array(),
			'strong' => array(),
			'br'     => array(),
			'a'      => array(
				'href' => array(),
			),
		);
		render_copyable_block( $clipboard_html, $allowed_html );
	}

	$overview_parts   = array();
	$overview_parts[] = 'Top' . $finished_top . ' 💯';

	if ( $remaining['top100']['projects'] > 0 ) {
		$overview_parts[] = sprintf(
			/* translators: 1. Remaining projects, 2. Remaining strings, 3. Top plugins/themes */
			__( '%1$s projects remaining ( %2$s strings) to complete Top %3$s', 'glotpress-stats' ),
			$remaining['top100']['projects'],
			$remaining['top100']['strings'],
			100
		);
	}
	if ( $remaining['top200']['projects'] > 0 ) {
		$overview_parts[] = sprintf(
			/* translators: 1. Remaining projects, 2. Remaining strings, 3. Top plugins/themes */
			__( '%1$s projects remaining ( %2$s strings) to complete Top %3$s', 'glotpress-stats' ),
			$remaining['top200']['projects'],
			$remaining['top200']['strings'],
			200
		);
	}

	if ( $printed_tasks > 0 ) {
		$overview_parts[] = sprintf(
			/* translators: 1. Remaining projects, 2. Remaining strings, 3. Top plugins/themes */
			__( '%1$s projects remaining ( %2$s strings) to complete Top %3$s', 'glotpress-stats' ),
			$remaining['total']['projects'],
			$remaining['total']['strings'],
			$count
		);
	} else {
		$overview_parts[] = sprintf(
			/* translators: Top plugins/themes */
			__( 'All projects in Top %1$s have been translated!', 'glotpress-stats' ),
			$count
		);
	}

	$overview_lines = array();

	foreach ( $overview_parts as $overview_part ) {
		$overview_lines[] = esc_html( $overview_part );
	}

	$overview_title = sprintf(
		/* translators: %1$s: the directory, plugins or themes */
		__( 'Top %1$s progress', 'glotpress-stats' ),
		$directory
	);

	$overview_html = sprintf(
		'<strong>%1$s</strong><p>%2$s</p>',
		esc_html( $overview_title ),
		implode( '<br>', $overview_lines )
	);

	$overview_allowed_html = array(
		'strong' => array(),
		'p'      => array(),
		'br'     => array(),
	);

	echo '<div id="glotpress-stats-overview' . esc_attr( $id_suffix ) . '" class="glotpress-stats-overview">';
	render_copyable_block( $overview_html, $overview_allowed_html );
	echo '</div>';

	return null;
}

/**
 * Renders a copyable HTML block with click-to-copy behavior.
 *
 * @param string $content_html  HTML content to display and copy.
 * @param array  $allowed_html  Allowed HTML tags for wp_kses.
 */
function render_copyable_block( $content_html, $allowed_html ) {
	?>
	<div
		class="glotpress-stats-copyable"
		role="button"
		tabindex="0"
	>
		<p class="glotpress-stats-copy-hint"><?php esc_html_e( 'Click to copy', 'glotpress-stats' ); ?></p>
		<div class="glotpress-stats-copy-content"><?php echo wp_kses( $content_html, $allowed_html ); ?></div>
	</div>
	<?php
}
