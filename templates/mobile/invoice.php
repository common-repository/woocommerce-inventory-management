<?php
/**
 * Manage invoice template
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit; ?>

<div class="pagewrap invoice-page">
	<div class="nav navbar navbar-fixed-top">
		<div class="col-xs-6 pull-left">
			<div class="header-title"><i class="fa fa-qrcode"></i> Scanventory</div>
		</div>
		<div class="col-xs-6 pull-right text-right">
			<?php
			$purl = remove_query_arg( 'invoice', $mainURL ); ?>

			<a class="btn" href="<?php echo $purl; ?>">Back to Product</a>
			<a class="btn" href="<?php echo $mainURL; ?>&logout=1">Logout</a> &nbsp; &nbsp;
		</div>
	</div>

	<div class="container" id="main">
		<br />
		<h4 class="text-center">This data generated randomly, please purchase <a target='_Blank' href='https://woocommerce.com/products/scanventory/'>Premium</a> plugin to get this feature.</h4>
		<hr />

		<h2 class="invoice-title" style="opacity: 0.5">Invoice
			<input type="text" class="sc-client-name" value="John Doe" disabled />
		</h2>

		<div class="invoice-product-list" style="opacity: 0.5">
			<table class="invoice-table" valign="middle">
				<thead>
					<tr>
						<th>Product</th>
						<th>Price</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$args = array(
						'posts_per_page'	=> 3,
						'orderby'			=> 'rand',
						'post_type'			=> 'product'
					); 
					$products = new WP_Query( $args );

					$total = 0;
					if( !empty($products->have_posts()) ) {
						while( $products->have_posts() ) : $products->the_post(); ?>
							<tr>
								<td class="product">
									<?php
									the_post_thumbnail( array('70', '70') ); ?>
									<span><?php the_title(); ?></span>
								</td>
								<td class="price">
									<?php
									$price = get_post_meta( get_the_ID(), '_regular_price', true);
									$price = !empty( $price ) ? $price : 0;

									$total = $total + $price;
									echo wc_price( $price ); ?>
								</td>
								<td class="action"><span class="remove-item">Remove</span></td>
							</tr>
						<?php
						endwhile;
						wp_reset_postdata();
					} else { ?>
						<tr><td colspan="3" class="no-products-row">
							No Product found in Invoice.
						</td></tr>
					<?php } ?>
				</tbody>
			</table>
		</div>

		<div class="invoice-total" style="opacity: 0.5">
			<table class="invoice-table">
				<tbody>
					<tr class="price-row">
						<td><h3>Invoice Total</h3></td>
						<td class="text-right">
							<h3><?php echo wc_price( $total ); ?></h3>
						</td>
					</tr>
					<tr class="btn-row no-border">
						<td>
							<a class="btn btn-rounded btn-danger clr-form-btn">Clear Form</a>
						</td>
						<td class="text-right">
							<form action='<?php echo $mainURL ?>' method="POST">
								<button type="submit" name="cmd" value="email_invoice" class="btn btn-rounded btn-info" disabled>Email Invoice</button>
							</form>

							<a class="btn btn-rounded btn-info" onclick='printContent("main");'>Print Invoice</a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<style type="text/css">
			input.sc-client-name{
				display: inline-block;
				border: 2px solid #dddddd;
				background-color: #ffffff;
				color: #000000;
				padding: 7px 10px;
				width: 75%;
				vertical-align: middle;
			}
			.invoice-table{ width: 100%; }
			.invoice-table thead { border-bottom: 2px solid #dddddd; }
			.invoice-table td, .invoice-table th{
				padding: 10px 7px;
				border-bottom: 1px solid #dddddd;
			}
			.invoice-table .no-border td,
			.invoice-table .no-border th{ border: 0; }

			.invoice-table td.product span{
				font-size: 16px;
				font-weight: 600;
				display: inline-block;
				margin-left: 10px;
			}
			.invoice-table td.price .amount{ font-size: 16px; }
			.invoice-table td.action{ width: 70px; }
			.no-products-row{ font-size: 16px; font-style: italic; }

			span.remove-item {
				width: 24px;
				height: 24px;
				cursor: pointer;
				color: #ad0f0d;
				display: inline-block;
				position: relative;
				text-indent: 999px;
				overflow: hidden;
			}
			span.remove-item:before{
				content: "\292B";
				width: 24px;
				height: 24px;
				text-indent: 0;
				display: block;
				font-size: 20px;
			}
			span.remove-item:hover{ color: #ff0500; }
			.invoice-total{
				width: 75%;
				float: right;
				border: 1px solid #dddddd;
				margin: 30px 0 15px;
				padding: 15px;
			}

			.invoice-total .invoice-table td{ vertical-align: top; }
			.price-row h3{ margin: 0; font-weight: 600; }
			.btn-row .btn{ margin: 10px 0 0; }
		</style>
	</div><!-- /#main -->
</div>

<script src="<?php echo site_url('/'); ?>wp-includes/js/jquery/jquery.js"></script>

<script>
	function printContent(el) {
		var restorepage = document.body.innerHTML;
		var printcontent = document.getElementById(el).innerHTML;
		document.body.innerHTML = printcontent;
		window.print();
		document.body.innerHTML = restorepage;
	}
</script>