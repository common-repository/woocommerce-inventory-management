<?php
/**
 * Mobile main view template
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

$options = get_option( 'scanventory_options', array() ); ?>

<form method="POST" action="<?php echo $mainURL; ?>">
	<div class="nav navbar navbar-fixed-top">
		<div class="col-xs-6 pull-left">
			<div class="header-title"><i class="fas fa-qrcode"></i> Scanventory</div>
		</div>
		<div class="col-xs-6 pull-right text-right">
			<a class="btn" href="<?php echo $mainURL; ?>&invoice=1">View Invoice</a>
			<a class="btn" href="<?php echo $mainURL; ?>&logout=1">Logout</a> &nbsp; &nbsp;
		</div>
	</div>

	<div class="container" id="main" style="text-align:center">
		<div class="row">
			<div class="col-xs-6">
				<?php echo $image; ?>
				<p class="sku">SKU: <?php echo $sku;?></p>
			</div>

			<div class="col-xs-6">
				<p class="name"><?php echo $name;?></p>
				<?php
				if( $stock === FALSE ) { ?>
					Stock not managed <br />
					<button type='submit' class='btn btn-primary' name='cmd' value='Activate'><i class='icon-off'></i> Activate</button>

					<?php
					if( !empty($_GET['p']) ) { 
						$bkURL = get_site_url() . '/?' . $_GET['p']; ?>
						<a class="btn btn-default" href="<?php echo $bkURL; ?>">Back to Main Product</a>
					<?php }

				} else { ?>
					<p class="name"><div id="stock"><?php echo $stock;?></div></p>
					
					<?php
					if( $instock == "instock" ) {
						echo "<button type='submit' name='cmd' value='instock' class='btn btn-success btn-xs'>In stock</button>";
					} else {
						echo "<button type='submit' name='cmd' value='outstock' class='btn btn-warning btn-xs'>Out of stock</button>";
					}
				} ?>
			</div>
		</div>

		<div class="col text-center">
			<?php echo $message; ?>
			<p class="name" id="message"></p>
		</div>
		
		<?php
		if( $stock !== FALSE ) { ?>
			
			<div class='row main'>
				<div class="col-md-12">
					<p><strong><big>Main Inventory</big></strong></p>
				</div>

				<div class='col-xs-6'>
					<div class="form-group">
						<label>Quantity</label><br />
						<input type="number" class="form-control stock-input" name="main_stock" value="<?=$stock?>">
						<div class='btn-group'>
							<button type="submit" name="main_byone" value="&#45;" class="btn btn-xs btn-primary" 
							<?php echo ($stock > 0 ? '' : 'disabled'); ?>><i class="fas fa-minus"></i></button>
							<button class="btn btn-default btn-large btn-xs" type="submit" name="main_set">Set</button>
							<button type="submit" name="main_byone" value="&#43;" class="btn btn-xs btn-primary" ><i class="fas fa-plus"></i></button>
						</div>
					</div>

					<?php
					// Invoice section
					if( isset($options['invoice']) && $options['invoice'] == '1' ) {

						$invoices = isset( $_COOKIE['scanventory_invoices'] ) ? stripslashes( $_COOKIE['scanventory_invoices'] ) : '';
						$invoices = json_decode( $invoices, true );

						$invIds = array();
						if( !empty($invoices) ) {
							$invIds = array_column( $invoices, 'id' );
						}

						$invIdx = array_search( $p->get_id(), $invIds );

						$invPrice = $p->get_price();
						if( $invIdx !== false ) {
							$invPrice = $invoices[$invIdx]['price'];
						} ?>

						<div class="invoice-wrap">
							<label>Price</label>
							<div class="form-group">
								<input type="number" name="invoice_price" class="form-control price-field" value="<?php echo $invPrice; ?>" />

								<button class="btn btn-info" type="button" disabled>Add to Invoice</button>

								<p class="text-muted">This feature available in <a target='_Blank' href='https://woocommerce.com/products/scanventory/'>Premium</a> plugin.</p>
							</div>
						</div>
					<?php
					} // is active invoice ?>
				</div>

				<div class='col-xs-6'>
					<div class='form-group'>
						<label>Allow backorders <br />
							<select name="backorders" class="form-control" disabled>
								<option value="yes" <?php echo ($backorders == "yes"?"selected":"")?>>Yes</option>
								<option value="notify"<?php echo ($backorders == "notify"?"selected":"")?>>Yes, notify customers</option>
								<option value="no" <?php echo ($backorders == "no"?"selected":"")?>>No</option>
							</select>
						</label>
					</div>

					<?php
					if( !is_a($p, 'WC_Product_Variation') ) { ?>
						<div class='form-group'>
							<label>Sell individually <br />
								<select name="individually" class="form-control" disabled>
									<option value="yes" <?php if($individually) echo "selected"; ?>>Yes</option>
									<option value="no" <?php if(!$individually) echo "selected"; ?>>No</option>
								</select>
							</label>
						</div>
					<?php } ?>
					<div class='btn-group'>
						<input type="submit" name="cmd" value="Update" class="btn btn-primary" disabled />
					</div>
					<p class="text-muted">This feature available in <a target='_Blank' href='https://woocommerce.com/products/scanventory/'>Premium</a> plugin.</p>
				</div>
			</div>

			<?php
			$scanventory_secret = get_option( 'scanventory_secret' );
			if( (count($vars) > 0) and ($stock !== FALSE) ) {
				foreach( $vars as $var ) { ?>
					<div class='row variant'>
						<div class='col-xs-6'>
							<p><strong><?php echo $var["label"]; ?></strong></p>
							<label>Quantity 
								<?php
								if( $var['manage_stock'] != '1' ) { ?>
									<small>( Inherits from main product )</small>
								<?php } ?>
							</label><br />
							<input type="number" class="form-control test stock-input" name="stock[<?php echo $var['vid']?>]" value="<?php echo str_replace(' ', '', $var['stock']); ?>" readonly disabled="disabled" />

							<?php
							$ven = sha1( $var['vid'] + $scanventory_secret );
							$vch = ( substr($ven, (strlen($ven) - 4), 4) );
							$vcode = dechex( $var['vid'] ) . "g" . $vch;

							$varUrl = get_bloginfo( 'url' );
							$varUrl .= '/?scanventory-S' . $vcode;

							if( !empty($current["query"]) ) {
								$varUrl .= '&p='.$current["query"];
							} ?>
							<a href="<?php echo $varUrl; ?>" class="btn btn-success">Manage</a>
						</div>
						<div class="col-xs-6 text-left">
							<p><strong>Attributes : </strong></p>
							<ul class='attributes'>
								<?php
								if( count($var["attributes"]) > 0 ) {
									foreach( $var["attributes"] as $attributeKey=>$value ) {
										list( $akey, $avalue ) = $value;
										echo "<li><strong>".$akey."</strong> : ".$avalue."</li>";
									}
								} ?>
							</ul>
						</div>
					</div>
				<?php
				}
			} ?>

			<div class="col-xs-12 text-right">
				<?php
				if( !empty($_GET['p']) ) { 
					$bkURL = get_site_url() . '/?' . $_GET['p']; ?>

					<a class="btn btn-default" href="<?php echo $bkURL; ?>">Back to Main Product</a>
				<?php } ?>

				<button type='submit' name='cmd' value='Deactivate' id='deactButton' class='btn btn-danger'> <i class='icon-off'></i> Deactivate </button>
			</div>
		<?php } ?>
	</div>
	<br />
</form>