<?php
/**
 * Generate products labels
 * Scanventory page labels tab
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit; ?>

<h2>Labels</h2>

<form method="post" action="<?php echo site_url() ?>/?scanventory-labels" target="_blank">
	<table class="form-table">
		<tr valign="top">
			<th scope="row">Label Print Options</th>
			<td>
				<label for="allOption"><input name="labeloption" id="allOption" type="radio" checked> All Products</label>
			</td>
		</tr>

		<tr valign="top">
			<th></th>
			<td>
				<label for="singleOption">
					<input disabled name="labeloption" id="singleOption" type="radio"> Specific Products :
				</label>
				<I> No Products Specified </I>

				<p class='description'>This feature is available in <a target='_Blank' href='https://woocommerce.com/products/scanventory/'>Premium</a> plugin, please check <a target='_Blank' href='http://scanventory.net/documentation/#!/specific_product_labels'>Documentation Here.</a></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">Additional Label Info </th>
			<td>
				<select name="variable">
					<?php
					$vars = self::variables();
					foreach( $vars as $k => $v ) {
						echo "<option value=\"{$k}\"> {$v["name"]}</option>";
					} ?>
				</select>
				<p class="description">You can check more attributes options in <a target='_Blank' href='https://woocommerce.com/products/scanventory/'>Premium</a> plugin.</p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">Label For Varitions </th>
			<td>
				<select name="vlabel" disabled>
					<option value="0">No</option>
					<option value="1">Yes</option>
				</select>
				<p class="description">Do you want to generate seperate labels for product varitions also? Get now <a target='_Blank' href='https://woocommerce.com/products/scanventory/'>Premium</a> plugin to enable it.</p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">Label Format</th>
			<td>
				<select name="label">
					<?php
					foreach (self::labelfac() as $css => $label) {
						echo "<option value=\"{$css}\">{$label}</option>";
					} ?>
				</select>
				<p class="description">Get more format option in <a target='_Blank' href='https://woocommerce.com/products/scanventory/'>Premium</a> plugin.</p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">Labels Per Sheet</th>
			<td>
				<select name="per_page" disabled>
					<option value="">Default</option>
					<option value="2">2</option>
					<option value="3">3</option>
				</select>
				<p class="description">Get now <a target='_Blank' href='https://woocommerce.com/products/scanventory/'>Premium</a> plugin to enable it.</p>
			</td>
		</tr>

	</table>
	<button class="button">Build Labels</button>
</form>
<br />