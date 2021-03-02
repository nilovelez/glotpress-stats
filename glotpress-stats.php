<?php
/**
 * Plugin Name: GlotPress Stats
 * Description: Shortcode for displaying a polyglot-oriented digest of a locale
 * Version: 0.5
 * Author: Nilo Velez
 * Author URI: https://www.nilovelez.com
 * Text Domain: glotpress-stats
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
 * @package    WordPress
 * @subpackage Glopress-stats
 */

namespace glotstats;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action(
	'init',
	function () {

		if ( in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) ) {
			define( 'GLOTSTATS_DEBUG', true );
		}

		// Load plugin text domain.
		load_plugin_textdomain(
			'glotpress-stats',
			'',
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		// Register Styles and Scripts.
		wp_register_style(
			'datatable',
			plugin_dir_url( __FILE__ ) . 'vendor/DataTables/datatables.min.css',
			'',
			'1.10.23'
		);
		wp_register_script(
			'datatables',
			plugin_dir_url( __FILE__ ) . 'vendor/DataTables/datatables.min.js',
			array( 'jquery' ),
			'1.10.23',
			true
		);
		wp_register_script(
			'glotpress-stats',
			plugin_dir_url( __FILE__ ) . 'assets/js/glotpress-stats.js',
			array( 'datatables', 'jquery' ),
			'0.3',
			true
		);

		/**
		 * Outputs the stats table of the given locale and project
		 * [glotstats locale='es' directory='plugins' view='tasks']
		 * locale: locale code as used in the >>URLs<<< os translate.wordpress.org
		 * directory: choose themes or plugins
		 * view:
		 *  - top: shows the unstranslatede projects from the top 200 of the selected directory
		 *  - stats: shows ready to copy Slack code with the info of the next 3 projects to do

		$atts array params passed to the function.
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
			if ( 'top' === $a['view'] ) {
				// Enqueue Styles and Scripts.
				wp_enqueue_style( 'datatable' );
				wp_enqueue_script( 'datatables' );
				wp_enqueue_script( 'glotpress-stats' );
				wp_add_inline_script(
					'glotpress-stats',
					'const GLOTSTATS_TRANSLATION = ' . wp_json_encode(
						array(
							'decimal'             => _x( '.', 'decimal separator', 'glotpress-stats' ),
							'emptyTable'          => __( 'No data available in table', 'glotpress-stats' ),
							// Translators: %1$s "themes" or "plugins".
							'info'                => sprintf( __( 'Showing _START_ to _END_ of _TOTAL_ %1$s', 'glotpress-stats' ), $a['directory'] ),
							// Translators: %1$s "themes" or "plugins".
							'infoEmpty'           => sprintf( __( 'Showing 0 to 0 of 0 %1$s', 'glotpress-stats' ), $a['directory'] ),
							// Translators: %1$s "themes" or "plugins".
							'infoFiltered'        => sprintf( __( '(filtered from _MAX_ total %1$s )', 'glotpress-stats' ), $a['directory'] ),
							'thousands'           => _x( ',', 'thousands separator', 'glotpress-stats' ),
							// Translators: %1$s "themes" or "plugins".
							'lengthMenu'          => sprintf( __( 'Show _MENU_ %1$s per page', 'glotpress-stats' ), $a['directory'] ),
							'loadingRecords'      => __( 'Loading...', 'glotpress-stats' ),
							'processing'          => __( 'Processing...', 'glotpress-stats' ),
							'search'              => __( 'Search ', 'glotpress-stats' ),
							'zeroRecords'         => __( 'No matching records found', 'glotpress-stats' ),

							'paginate_first'      => __( 'First', 'glotpress-stats' ),
							'paginate_last'       => __( 'Last', 'glotpress-stats' ),
							'paginate_next'       => __( 'Next', 'glotpress-stats' ),
							'paginate_previous'   => __( 'Previous', 'glotpress-stats' ),

							'aria_sortAscending'  => __( ': activate to sort column ascending', 'glotpress-stats' ),
							'aria_sortDescending' => __( ': activate to sort column descending', 'glotpress-stats' ),
						)
					),
					'before'
				);
			}

			ob_start();

			require_once plugin_dir_path( __FILE__ ) . 'parser.php';
			parse(
				$a['locale'],
				$a['directory'],
				$a['view']
			);
			?>

			<?php
			return ob_get_clean();
		}
		add_shortcode( 'glotstats', 'glotstats\shortcode_callback' );
	}
);
