<?php

namespace glotstats;

function parse( $locale = false, $directory = false, $view = 'top', $exclude = array() ) {

	if ( empty( $locale ) || empty( $directory ) ) {
		return false;
	}

	if ( ! defined( 'GLOTSTATS_DEBUG' ) ) {
		$url = 'https://translate.wordpress.org/locale/' . $locale . '/default/stats/' . $directory;

		$file = wp_remote_get( $url );
		if ( is_wp_error( $file ) ) {
			return __( 'An error occurred while fetching data from the WordPress directory', 'glotpress-stats' );
		}
		$file = wp_remote_retrieve_body( $file );
	} else {
		echo '<h2>[debugging]</h2>';
		$file = file_get_contents( plugin_dir_path( __FILE__ ) . '/demo_data/top_' . $directory . '_es.html' );
	}

	$file  = str_replace( '&', '&amp;', $file );
	$start = strpos( $file, '<tbody' );
	$end   = strpos( $file, '</tbody>' );

	$file  = substr( $file, $start, ( $end - $start ) + 8 );
	$xml   = new \SimpleXMLElement( $file );
	$input = array();

	foreach ( $xml as $item ) {

		$input[] = array(
			'title'                     => (string) $item->th->a,
			'installs'                  => (int) $item->th['data-sort-value'],
			'link'                      => (string) $item->th->a['href'],
			'slug'						=> (string) preg_replace('/^.*\/([^\/]+)\/?$/', '$1', $item->th->a['href']),
			'percent'                   => (int) $item->td[0]['data-sort-value'],
			'language_link'             => (string) $item->td[0]->a['href'],
			'translated'                => (int) $item->td[1]['data-sort-value'],
			//'translated_link'         => (string) $item->td[1]->a['href'],
			'untranslated'              => (int) $item->td[2]['data-sort-value'],
			'untranslated_link_themes'  => (string) $item->td[2]->a['href'],
			'untranslated_link_plugins' => (string) $item->th->a['href'] . 'stable/' . $locale . '/default/?filters[status]=untranslated',
			'fuzzy'                     => (int) $item->td[3]['data-sort-value'],
			//'fuzzy_link'              => (string) $item->td[3]->a['href'],
			'waiting'                   => (int) $item->td[4]['data-sort-value'],
			//'waiting_link'            => (string) $item->td[4]->a['href'],
		);

	}

	$base_url = 'https://translate.wordpress.org';
	$count    = count( $input );

	if ( 'top' === $view ) {
		?>
		<div class="stats-table">
			<table id="stats-table">
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
	} elseif ( 'tasks' === $view ) {
		echo '<pre>';
	}

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
	$finished_top = 0;
	$printed_tasks = 0;

	for ( $i = 0; $i < $count; $i++ ) {
		$row = $input[ $i ];

		if ( in_array( $row['slug'], $exclude ) ) {
			continue;
		}

		if ( 0 === $row['untranslated'] ) {
			if ( $clean ) {
				$finished_top = $i + 1;
			}
		} else {

			if ( $i <= 100 ) {
				$remaining['top100']['projects']++;
				$remaining['top100']['strings'] += $row['untranslated'];
			}
			if ( $i <= 200 ) {
				$remaining['top200']['projects']++;
				$remaining['top200']['strings'] += $row['untranslated'];
			}
			$remaining['total']['projects']++;
			$remaining['total']['strings'] += $row['untranslated'];

			$untranslated_link = $row[ 'untranslated_link_' . $directory ];

			if ( $clean ) {
				$clean = false;
			}

			if ( 'top' === $view ) {
				echo '<tr data-slug="' . $row['slug'] .'">' . "\n";
				echo '<th>' . esc_html( $i + 1 ) . '</th>';
				echo '<th><a href="' . esc_url( $base_url . $row['language_link'] ) . '">' . esc_html( $row['title'] ) . '</a></td>';
				echo '<td>' . number_format( $row['installs'], 0, '', '.' ) . '</td>';
				echo '<td><a href="' . esc_url( $base_url . $untranslated_link ) . '" rel="nofollow">' . esc_html( $row['untranslated'] ) . '</a></td>';
				echo '</tr>' . "\n";
			} elseif ( 'tasks' === $view ) {
				if ( $printed_tasks < 3 ) {
					echo esc_html( '*' . $row['title'] . '* (' . number_format( $row['installs'], 0, '', '.' ) . '+ instalaciones)' ) . "\n";
					echo esc_html( $row['untranslated'] . ' cadenas sin traducir' ) . "\n";
					echo esc_html( $base_url . $untranslated_link ) . "\n\n";
				}
			}
			$printed_tasks++;
		}
	}
	if ( 'top' === $view ) {
		echo '</tbody></table></div>';
	} elseif ( 'tasks' === $view ) {
		echo '</pre>';
	}

	echo '<div id="glotpress-stats-overview">';
	echo '<h2>' . esc_html( __( 'Overview', 'glotpress-stats' ) ) . '</h2>';
	echo '<p>Top' . esc_html( $finished_top ) . ' 💯<br>';

	if ( $remaining['top100'] > 0 ) {
		echo esc_html(
			sprintf(
				/* translators: 1. Remaining projects, 2. Remaining strings, 3. Top plugins/themes */
				__( '%1$s projects remaining ( %2$s strings) to complete Top %3$s', 'glotpress-stats' ),
				$remaining['top100']['projects'],
				$remaining['top100']['strings'],
				100
			)
		) . '<br>';
	}
	if ( $remaining['top200'] > 0 ) {
		echo esc_html(
			sprintf(
				/* translators: 1. Remaining projects, 2. Remaining strings, 3. Top plugins/themes */
				__( '%1$s projects remaining ( %2$s strings) to complete Top %3$s', 'glotpress-stats' ),
				$remaining['top200']['projects'],
				$remaining['top200']['strings'],
				200
			)
		) . '<br>';
	}

	if ( $printed_tasks > 0 ) {
		echo esc_html(
			sprintf(
				/* translators: 1. Remaining projects, 2. Remaining strings, 3. Top plugins/themes */
				__( '%1$s projects remaining ( %2$s strings) to complete Top %3$s', 'glotpress-stats' ),
				$remaining['total']['projects'],
				$remaining['total']['strings'],
				$count
			)
		);
	} else {
		echo esc_html(
			sprintf(
				/* translators: Top plugins/themes */
				__( 'All projects in Top %1$s have been translated!', 'glotpress-stats' ),
				$count
			)
		);
	}
	echo '</p></div>';
}
