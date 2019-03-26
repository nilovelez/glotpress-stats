<?php
/**
 * Plugin Name: GlotPress Stats
 * Description: Shortcode for displaying a polyglot-oriented digest of a locale
 * Version: 0.1
 * Author: Nilo Velez
 * Author URI: https://www.nilovelez.com
 * Text Domain: glotstats
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html

 * @package    WordPress
 * @subpackage glotstats
 */

namespace glotstats;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action(
	'init',
	function () {
		/**
		 * Outputs the stats table of the give locale and project
		 * [glotstats locale="es" directory="plugins"]
		 */
		function shortcode_callback( $atts ) {
			$a = shortcode_atts(
				array(
					'locale'    => 'es',
					'directory' => 'plugins',
				),
				$atts
			);
			if ( ! in_array( $a['directory'], array( 'themes', 'plugins' ), true ) ) {
				return false;
			}
			ob_start();
			require plugin_dir_path( __FILE__ ) . './parser.php';
			parse( $a['locale'], $a['directory'] );
			return ob_get_clean();
		}
		add_shortcode( 'glotstats', 'glotstats\shortcode_callback' );
	}
);
