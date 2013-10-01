<?php
/**
 * Tools
 *
 * These are functions used for displaying EDD tools such as the import/export system.
 *
 * @package     EDD
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tools
 *
 * Shows the tools panel which contains EDD-specific tools including the
 * built-in import/export system.
 *
 * @since       1.8
 * @author      Daniel J Griffiths
 * @return      void
 */
function edd_tools_page() {

	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( edd_get_tools_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'tab'  => $tab_id,
					'view' => false
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>
		<div class="metabox-holder">
			<?php
			do_action( 'edd_tools_before' );
			do_action( 'edd_tools_tab_' . $active_tab );
			do_action( 'edd_tools_after' );
			?>
		</div>
	</div><!-- .wrap -->
<?php
}

/**
 * Retrieve tools tabs
 *
 * @since 1.9
 * @param array $input The field value
 * @return array
 */
function edd_get_tools_tabs() {

	$tabs             = array();
	$tabs['general']  = __( 'General', 'edd' );
	$tabs['webhooks'] = __( 'Webhooks', 'edd' );

	return apply_filters( 'edd_tools_tabs', $tabs );
}

/**
 * Display the general Tools tab
 *
 * @since 1.9
 */
function edd_tools_tab_general() {
?>
<div class="postbox">
	<h3><span><?php _e( 'Export Settings', 'edd' ); ?></span></h3>
	<div class="inside">
		<p><?php _e( 'Export the Easy Digital Downloads settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'edd' ); ?></p>
		<p><?php printf( __( 'To export shop data (purchases, customers, etc), visit the <a href="%s">Reports</a> page.', 'edd' ), admin_url( 'edit.php?post_type=download&page=edd-reports&tab=export' ) ); ?>
		<form method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools' ); ?>">
			<p><input type="hidden" name="edd_action" value="export_settings" /></p>
			<p>
				<?php wp_nonce_field( 'edd_export_nonce', 'edd_export_nonce' ); ?>
				<?php submit_button( __( 'Export', 'edd' ), 'secondary', 'submit', false ); ?>
			</p>
		</form>
	</div><!-- .inside -->
</div><!-- .postbox -->
<div class="postbox">
	<h3><span><?php _e( 'Import Settings', 'edd' ); ?></span></h3>
	<div class="inside">
		<p><?php _e( 'Import the Easy Digital Downloads settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'edd' ); ?></p>
		<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools' ); ?>">
			<p>
				<input type="file" name="import_file"/>
			</p>
			<p>
				<input type="hidden" name="edd_action" value="import_settings" />
				<?php wp_nonce_field( 'edd_import_nonce', 'edd_import_nonce' ); ?>
				<?php submit_button( __( 'Import', 'edd' ), 'secondary', 'submit', false ); ?>
			</p>
		</form>
	</div><!-- .inside -->
</div><!-- .postbox -->
<?php
}
add_action( 'edd_tools_tab_general', 'edd_tools_tab_general' );

/**
 * Display the webhooks Tools tab
 *
 * @since 1.9
 */
function edd_tools_tab_webhooks() {

	require_once EDD_PLUGIN_DIR . 'includes/admin/tools/class-webhooks-list-table.php';
	$webhooks_table = new EDD_Webhooks_Table();
	$webhooks_table->prepare_items();
?>
	<?php
	if( isset( $_GET['view'] ) && 'edit_webhook' == $_GET['view'] ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools/edit-webhook.php';
	} elseif( isset( $_GET['view'] ) && 'add_webhook' == $_GET['view'] ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools/add-webhook.php';
	} else { ?>
		<a href="<?php echo add_query_arg( array( 'view' => 'add_webhook' ) ); ?>" class="button-primary"><?php _e( 'Add New', 'edd' ); ?></a>
		<form id="edd-webhooks-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tag=webhooks' ); ?>">

			<input type="hidden" name="post_type" value="download" />
			<input type="hidden" name="page" value="edd-tools" />
			<input type="hidden" name="tab" value="webhooks" />

			<?php $webhooks_table->views() ?>
			<?php $webhooks_table->display() ?>
		</form>
	<?php } ?>
<?php
}
add_action( 'edd_tools_tab_webhooks', 'edd_tools_tab_webhooks' );

/**
 * Process a settings export that generates a .json file of the shop settings
 *
 * @since       1.7
 * @return      void
 */
function edd_process_settings_export() {

	if( empty( $_POST['edd_export_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['edd_export_nonce'], 'edd_export_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

	$settings = array();
	$settings = get_option( 'edd_settings' );

	ignore_user_abort( true );

	if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
		set_time_limit( 0 );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=edd-settings-export-' . date( 'm-d-Y' ) . '.json' );
	header( "Expires: 0" );

	echo json_encode( $settings );
	exit;
}
add_action( 'edd_export_settings', 'edd_process_settings_export' );

/**
 * Process a settings import from a json file
 *
 * @since 1.7
 * @return void
 */
function edd_process_settings_import() {

	if( empty( $_POST['edd_import_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['edd_import_nonce'], 'edd_import_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

	$import_file = $_FILES['import_file']['tmp_name'];

	if( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'edd' ) );
	}

	// Retrieve the settings from the file and convert the json object to an array
	$settings = edd_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	update_option( 'edd_settings', $settings );

	wp_safe_redirect( admin_url( 'edit.php?post_type=download&page=edd-tools&edd-message=settings-imported' ) ); exit;

}
add_action( 'edd_import_settings', 'edd_process_settings_import' );
