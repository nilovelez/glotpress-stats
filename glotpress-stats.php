<?php
/**
 * Plugin Name: GlotPress Stats
 * Description: Shortcode for displaying a polyglot-oriented digest of a locale
 * Version: 0.2
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
		 * Outputs the stats table of the given locale and project
		 * [glotstats locale="es" directory="plugins" view="tasks"]
		 * locale: locale code as used in the >>URLs<<< os translate.wordpress.org
		 * directory: choose themes or plugins
		 * view:
		 *  - top: shows the unstranslatede projects from the top 200 of the selected directory
		 *  - stats: shows ready to copy Slack code with the info of the next 3 projects to do
		 */
		function shortcode_callback( $atts ) {
			$a = shortcode_atts(
				array(
					'locale'    => 'es',
					'directory' => 'plugins',
					'view'      => 'top',
				),
				$atts
			);
			if ( ! in_array( $a['directory'], array( 'themes', 'plugins' ), true ) ) {
				return false;
			}
			if ( ! in_array( $a['view'], array( 'top', 'tasks' ), true ) ) {
				return false;
			}
			ob_start();
			require plugin_dir_path( __FILE__ ) . './parser.php';
			parse( $a['locale'], $a['directory'], $a['view'] );
			return ob_get_clean();
		}
		add_shortcode( 'glotstats', 'glotstats\shortcode_callback' );
	}
);
