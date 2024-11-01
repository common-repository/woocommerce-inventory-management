<?php
/**
 * Main - Plugin Settings
 * Scanventory page main tab
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

$settings = get_option( 'scanventory_options', array() );

$restoreStock	= (isset($this->options['restorestock']) ? $this->options['restorestock'] : false);
$invoice		= (isset($this->options['invoice']) ? $this->options['invoice'] : false);

$lkey = (isset($this->options['lkey']) ? $this->options['lkey'] : '');
$lkey = (isset($_POST['lkey']) ? $_POST['lkey'] : $lkey ); ?>

<form method="post" action="?post_type=product&page=scanventory&tab=main">
	<h2>General Settings</h2>

	<?php
	wp_nonce_field( 'scanventory_plugin_settings', 'scanventory_settings' ); ?>

	<table class="form-table">
		<tr valign="top">
			<th scope="row">Restore Stock</th>
			<td>
				<input type="hidden" name="restorestock" value="0" type="checkbox" />
				<label><input name="restorestock" value="1" type="checkbox" <?php checked( $restoreStock, '1' ); ?> /> Restore Stock on cancelled orders?</label>
			</td>
		</tr>
	</table>

	<hr />
	<h2>Invoice Settings</h2>
	<table class="form-table">
		<tr>
			<th>Enable/Disable</th>
			<td>
				<input type="hidden" name="invoice" value="0" type="checkbox" />
				<label><input name="invoice" value="1" type="checkbox" disabled /> Check this box to enable invoice system.</label>
				<p class='description'>This feature is available in <a target='_Blank' href='https://woocommerce.com/products/scanventory/'>Premium</a> plugin</p>
			</td>
		</tr>
	</table>

	<hr />
	<?php submit_button(); ?>
</form>