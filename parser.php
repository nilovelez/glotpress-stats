<?php

namespace glotstats;

function parse( $locale = false, $directory = false ) {
	

	if ( empty( $locale ) || empty( $directory ) ) {
		return false;
	}

	echo "hola";


	$url = 'https://translate.wordpress.org/locale/' . $locale . '/default/stats/' . $directory;

	$file  = file_get_contents( $url );
	$file  = str_replace( '&', '&amp;', $file );
	$start = strpos( $file, '<tbody' );
	$end   = strpos( $file, '</tbody>' );

	$file  = substr( $file, $start, ( $end - $start ) + 8 );
	$xml   = new \SimpleXMLElement( $file );
	$input = array();

	foreach ( $xml as $item ) {

		//$item = $xml->tr[0];

		$base_url = 'https://translate.wordpress.org';

		//var_dump( $item );

		$input[] = array(
			'title'             => (string) $item->th->a,
			'installs'          => (int)    $item->th['data-sort-value'],
			'link'              => (string) $item->th->a['href'],
			'percent'           => (int)    $item->td[0]['data-sort-value'],
			'language_link'     => (string) $item->td[0]->a['href'],
			'translated'        => (int)    $item->td[1]['data-sort-value'],
			'translated_link'   => (string) $item->td[1]->a['href'],
			'untranslated'      => (int)    $item->td[2]['data-sort-value'],
			'untranslated_link' => (string) $item->td[2]->a['href'],
			'fuzzy'             => (int)    $item->td[3]['data-sort-value'],
			'fuzzy_link'        => (string) $item->td[3]->a['href'],
			'waiting'           => (int)    $item->td[4]['data-sort-value'],
			'waiting_link'      => (string) $item->td[4]->a['href'],
		);
	}

	$count = count( $input );

	//echo '<pre>';
	//echo $count . "\n";
	//print_r( $input );
	?>
	<div class="stats-table">
		<table id="stats-table">
			<thead>

	<tr>
	<th style="width: 60px;">#</th>
	<th>Name</th>
	<th>Installs</th>
	<th>Untranslated</th>
	</tr>
	</thead>
	<tbody>

	<?php

	$clean        = true;
	$top          = $count;
	$untranslated = 0;

	for ( $i = 0; $i < $count; $i++ ) {
		$row = $input[ $i ];

		//print_r( $row );

		if ( 0 !== $row['untranslated'] ) {

			$untranslated += $row['untranslated'];

			if ( $clean ) {
				$clean = false;
				$top   = $i + 1;
			}

			echo '<tr>'. "\n";
			echo '<th>' . ( $i + 1)	 . '</th>';
			echo '<th>' . $row['title'] . '</td>';
			echo '<td>' . $row['installs'] . '</td>';
			echo '<td><a href="' . $base_url . $row['untranslated_link'] . '">' . $row['untranslated'] . '</a></td>';
			echo '</tr>'. "\n";
		}
	}

	echo '</tbody></table><div>';
	//echo '</pre>';

	echo 'Top'.$top .' :100:<br>';
	echo $untranslated .' unstranlated<br>';

}
