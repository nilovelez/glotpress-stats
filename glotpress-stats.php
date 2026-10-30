<?php
/**
 * Plugin Name: GlotPress Stats
 * Description: Shortcode for displaying a polyglot-oriented digest of a locale
 * Version: 0.8
 * Author: Nilo Velez
 * Author URI: https://www.nilovelez.com
 * Text Domain: glotpress-stats
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
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
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		// Register Styles and Scripts.
		wp_register_style(
			'datatables',
			plugin_dir_url( __FILE__ ) . 'resources/vendor/datatables/datatables.min.css',
			'',
			'2.3.8'
		);
		wp_register_script(
			'datatables',
			plugin_dir_url( __FILE__ ) . 'resources/vendor/datatables/datatables.min.js',
			array( 'jquery' ),
			'2.3.8',
			true
		);
		wp_register_style(
			'glotpress-stats',
			plugin_dir_url( __FILE__ ) . 'resources/css/glotpress-stats.css',
			'',
			'0.8'
		);
		wp_register_script(
			'glotpress-stats',
			plugin_dir_url( __FILE__ ) . 'resources/js/glotpress-stats.js',
			array( 'jquery' ),
			'0.8',
			true
		);

		/**
		 * Outputs the stats table of the given locale and project
		 * [glotstats locale='es' directory='plugins' view='tasks']
		 * locale: locale code as used in the >>URLs<<< os translate.wordpress.org
		 * directory: choose themes or plugins
		 * view:
		 *  - top: shows the unstranslatede projects from the top 200 of the selected directory
		 *  - tasks: shows ready to copy Slack code with the info of the next 3 projects to do
		 *
		 * @param array $atts Params passed to the shortcode.
		 * @return string|false The shortcode output, or false on invalid params.
		 */
		function shortcode_callback( $atts ) {
			static $copy_strings_added = false;

			$a = shortcode_atts(
				array(
					'locale'    => 'es',
					'directory' => 'plugins',
					'view'      => 'top',
					'exclude'   => '',
				),
				$atts
			);
			if ( ! in_array( $a['directory'], array( 'themes', 'plugins' ), true ) ) {
				return false;
			}
			$trans = array(
				'themes'  => __( 'themes', 'glotpress-stats' ),
				'plugins' => __( 'plugins', 'glotpress-stats' ),
			);
			if ( ! in_array( $a['view'], array( 'top', 'tasks' ), true ) ) {
				return false;
			}

			if ( ! empty( $a['exclude'] ) ) {
				$a['exclude'] = str_replace( ' ', '', $a['exclude'] );
				$a['exclude'] = explode( ',', $a['exclude'] );
			} else {
				$a['exclude'] = array();
			}

			wp_enqueue_style( 'glotpress-stats' );
			wp_enqueue_script( 'glotpress-stats' );

			// The copy strings are the same for every shortcode on the page: add them once.
			if ( ! $copy_strings_added ) {
				wp_add_inline_script(
					'glotpress-stats',
					'const GLOTSTATS_COPY = ' . wp_json_encode(
						array(
							'clickToCopy' => __( 'Click to copy', 'glotpress-stats' ),
							'copied'      => __( 'Copied to clipboard', 'glotpress-stats' ),
						)
					),
					'before'
				);
				$copy_strings_added = true;
			}

			// The table strings depend on the directory, so each table carries its own.
			$table_language = array();

			if ( 'top' === $a['view'] ) {
				wp_enqueue_style( 'datatables' );
				wp_enqueue_script( 'datatables' );
				$table_language = array(
					'decimal'        => _x( '.', 'decimal separator', 'glotpress-stats' ),
					'emptyTable'     => __( 'No data available in table', 'glotpress-stats' ),
					// Translators: %1$s "themes" or "plugins".
					'info'           => sprintf( __( 'Showing _START_ to _END_ of _TOTAL_ %1$s', 'glotpress-stats' ), $trans[ $a['directory'] ] ),
					// Translators: %1$s "themes" or "plugins".
					'infoEmpty'      => sprintf( __( 'Showing 0 to 0 of 0 %1$s', 'glotpress-stats' ), $trans[ $a['directory'] ] ),
					// Translators: %1$s "themes" or "plugins".
					'infoFiltered'   => sprintf( __( '(filtered from _MAX_ total %1$s )', 'glotpress-stats' ), $trans[ $a['directory'] ] ),
					'thousands'      => _x( ',', 'thousands separator', 'glotpress-stats' ),
					// Translators: %1$s "themes" or "plugins".
					'lengthMenu'     => sprintf( __( 'Show _MENU_ %1$s per page', 'glotpress-stats' ), $trans[ $a['directory'] ] ),
					'loadingRecords' => __( 'Loading...', 'glotpress-stats' ),
					'processing'     => __( 'Processing...', 'glotpress-stats' ),
					'search'         => __( 'Search ', 'glotpress-stats' ),
					'zeroRecords'    => __( 'No matching records found', 'glotpress-stats' ),
					'paginate'       => array(
						'first'    => __( 'First', 'glotpress-stats' ),
						'last'     => __( 'Last', 'glotpress-stats' ),
						'next'     => __( 'Next', 'glotpress-stats' ),
						'previous' => __( 'Previous', 'glotpress-stats' ),
					),
					'aria'           => array(
						'sortAscending'  => __( ': activate to sort column ascending', 'glotpress-stats' ),
						'sortDescending' => __( ': activate to sort column descending', 'glotpress-stats' ),
					),
				);
			}

			ob_start();

			require_once plugin_dir_path( __FILE__ ) . 'parser.php';
			$error = parse(
				$a['locale'],
				$a['directory'],
				$a['view'],
				$a['exclude'],
				$table_language
			);

			if ( is_string( $error ) ) {
				echo '<p class="glotpress-stats-error">' . esc_html( $error ) . '</p>';
			}

			return ob_get_clean();
		}
		add_shortcode( 'glotstats', 'glotstats\shortcode_callback' );
	}
);
