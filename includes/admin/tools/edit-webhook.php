<?php
/**
 * Edit Webhook Page
 *
 * @package     EDD
 * @subpackage  Admin/Tools/Webhooks
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! isset( $_GET['webhook'] ) || ! is_numeric( $_GET['webhook'] ) ) {
	wp_die( __( 'Something went wrong.', 'edd' ), __( 'Error', 'edd' ) );
}

$webhook_id  = absint( $_GET['webhook'] );

?>
<h2><?php _e( 'Edit Webhook', 'edd' ); ?> - <a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-tools&tab=webhooks' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'edd' ); ?></a></h2>
<form id="edd-edit-webhook" action="" method="POST">
	<?php do_action( 'edd_edit_webhook_form_top' ); ?>
	<table class="form-table">
		<tbody>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-name"><?php _e( 'Name', 'edd' ); ?></label>
				</th>
				<td>
					<input name="name" id="edd-name" type="text" value="<?php echo esc_attr( get_post_field( 'post_title', $webhook_id ) ); ?>" style="width: 300px;"/>
					<p class="description"><?php _e( 'The name of this webhook', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-url"><?php _e( 'Webhook URL', 'edd' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-url" name="url" value="<?php echo esc_attr( get_post_field( 'guid', $webhook_id ) ); ?>" style="width: 300px;"/>
					<p class="description"><?php _e( 'Enter a url to send the remote request to', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-status"><?php _e( 'Status', 'edd' ); ?></label>
				</th>
				<td>
					<select name="status" id="edd-status">
						<option value="active"<?php selected( 'active', get_post_status( $webhook_id ) );?>><?php _e( 'Active', 'edd' ); ?></option>
						<option value="inactive"<?php selected( 'inactive', get_post_status( $webhook_id ) );?>><?php _e( 'Inactive', 'edd' ); ?></option>
					</select>
					<p class="description"><?php _e( 'Set the webhook to active or inactive?', 'edd' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="edd-action"><?php _e( 'Action', 'edd' ); ?></label>
				</th>
				<td>
					<select name="action" id="edd-action">
						<?php foreach( EDD()->webhooks->get_actions() as $action_id => $action ) { ?>
							<option value="<?php echo esc_attr( $action_id ); ?>"<?php selected( $action_id, EDD()->webhooks->get_action( $webhook_id ) ); ?>><?php echo esc_html( $action ); ?></option>
						<?php } ?>
					</select>
					<p class="description"><?php _e( 'Set the webhook to active or inactive?', 'edd' ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
	<?php do_action( 'edd_edit_webhook_form_bottom' ); ?>
	<p class="submit">
		<input type="hidden" name="ID" value="<?php echo $webhook_id; ?>"/>
		<input type="hidden" name="edd-action" value="edit_webhook"/>
		<input type="hidden" name="edd-redirect" value="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-tools&tab=webhooks' ) ); ?>"/>
		<input type="hidden" name="edd-webhooks-nonce" value="<?php echo wp_create_nonce( 'edd_webhooks_nonce' ); ?>"/>
		<input type="submit" value="<?php _e( 'Update Webhook', 'edd' ); ?>" class="button-primary"/>
	</p>
</form>