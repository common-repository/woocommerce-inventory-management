<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Create report
 */

if( !is_user_logged_in() || !current_user_can('manage_options') ) wp_die('This page is private.');

global $woocommerce; ?>

<section>
	<br />
	<h2>Simple</h2>
	<table class="wpscl-table">
		<thead><tr>
			<th><?php _e( 'SKU', 'woothemes' ); ?></th>
			<th><?php _e( 'Stock', 'woothemes' ); ?></th>
			<th><?php _e( 'Product', 'woothemes' ); ?></th>
			<th><?php _e( 'Price / Regular', 'woothemes' ); ?></th>
		</tr></thead>

		<tbody>
		<?php
		$args = array(
			'post_type'	=> 'product',
			'post_status' => 'publish',
			'posts_per_page' => 5,
			'orderby'	=> 'title',
			'order'	=> 'ASC',
			'meta_query' => array(
				array(
					'key' => '_manage_stock',
					'value' => 'yes'
				)
			),
			'tax_query' => array(
				array(
					'taxonomy' => 'product_type',
					'field' => 'slug',
					'terms' => array('simple'),
					'operator' => 'IN'
				)
			)
		);

		$loop = new WP_Query( $args );

		while( $loop->have_posts() ) : $loop->the_post();

			$_product = new WC_Product( get_the_ID() );

			$price = $_product->get_price();
			$price = ( $price != "" ) ? money_format( '%i', $price ) : '0.00';

			$regular = $_product->get_regular_price();
			$regular = ( $regular != "" ) ? money_format( '%i', $regular ) : '0.00';
			
			$sku	= ( $_product->get_sku() != "" ) ? $_product->get_sku() : '<i>None</i>'; ?>

			<tr>
				<td><?php echo $sku; ?></td>
				<td><?php echo wc_get_stock_html( $_product ); ?></td>
				<td><?php echo $_product->get_title(); ?></td>
				<td><?php echo "{$price} / {$regular}"; ?></td>
			</tr>

		<?php endwhile;
		wp_reset_query(); ?>

			<tr>
				<td colspan="4">
					<h4 style="text-align: center; color: #555555;">It will generate report up to 5 products only. To generate all product report, please get <a target='_Blank' href='https://woocommerce.com/products/scanventory/'>Premium</a> plugin now.</h4>
				</td>
			</tr>
		</tbody>
	</table>

	<hr />
	<h2>Variations</h2>
	<table class="wpscl-table">
		<thead><tr>
			<th><?php _e( 'SKU'); ?></th>
			<th><?php _e( 'Stock' ); ?></th>
			<th><?php _e( 'Variation' ); ?></th>
			<th><?php _e( 'Parent' ); ?></th>
			<th><?php _e( 'Attributes' ); ?></th>
			<th><?php _e( 'Price / Regular' ); ?></th>
		</tr></thead>
		<tbody>
			<tr>
				<td colspan="6">
					<br />
						<h3 style="text-align: center; color: #555555;">This feature is available in <a target='_Blank' href='https://woocommerce.com/products/scanventory/'>Premium</a> plugin.</h3>
					<br />
				</td>
			</tr>
		</tbody>
	</table>
</section>

<style type="text/css">
	.wpscl-table{
		width: 100%;
		max-width: 900px;
		margin-bottom: 20px;
	}
	.wpscl-table thead th {
		background-color:#c3c3c3;
		padding: 5px 10px; 
	}
	.wpscl-table tbody td{
		padding: 5px 10px;
		background-color: #f9f9f9;
	}
	.wpscl-table tbody tr:nth-child(even) td{background-color: #ffffff;}
	.wpscl-table p{ margin: 0; }

	@media only screen and ( max-width: 480px ) {
		.wpscl-table thead th,
		.wpscl-table tbody td {
			padding: 2px; 
		}		
	}
</style>