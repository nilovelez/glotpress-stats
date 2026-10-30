<?php
/**
 * Builds the DataTables files the plugin ships from the Composer packages.
 *
 * Composer installs DataTables in vendor/, which is not part of the plugin.
 * This script combines the core library and its default styling integration
 * into resources/vendor/datatables/, the same files the DataTables download
 * builder produces. Run it with `composer build-datatables`; Composer also
 * runs it after `composer install` and `composer update`.
 *
 * @package GlotPress_Stats
 */

$root   = dirname( __DIR__ );
$core   = $root . '/vendor/datatables.net/datatables.net';
$style  = $root . '/vendor/datatables.net/datatables.net-dt';
$target = $root . '/resources/vendor/datatables';

if ( ! is_dir( $core ) || ! is_dir( $style ) ) {
	fwrite( STDERR, "DataTables is not installed. Run `composer install` first.\n" );
	exit( 1 );
}

$builds = array(
	'datatables.js'      => array( $core . '/js/dataTables.js', $style . '/js/dataTables.dataTables.js' ),
	'datatables.min.js'  => array( $core . '/js/dataTables.min.js', $style . '/js/dataTables.dataTables.min.js' ),
	'datatables.css'     => array( $style . '/css/dataTables.dataTables.css' ),
	'datatables.min.css' => array( $style . '/css/dataTables.dataTables.min.css' ),
);

if ( ! is_dir( $target ) ) {
	mkdir( $target, 0755, true );
}

foreach ( $builds as $file => $sources ) {
	$parts = array();

	foreach ( $sources as $source ) {
		$content = file_get_contents( $source );
		$content = str_replace( "\r\n", "\n", $content );
		$parts[] = trim( $content );
	}

	file_put_contents( $target . '/' . $file, implode( "\n\n\n", $parts ) . "\n" );
	echo 'Built resources/vendor/datatables/' . $file . "\n";
}
