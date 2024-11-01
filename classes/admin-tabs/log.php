<?php
/**
 * Log - Show Activity Logs
 * Scanventory page log tab
 */

 // Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit; ?>

<br />
<table class='wpscl-table wpscl-log'>
	<thead>
		<tr>
			<th>When</th>
			<th>Who</th>
			<th>What</th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td colspan="3">
				<br />
					<h3 style="text-align: center; color: #555555;">This feature is available in <a target='_Blank' href='https://woocommerce.com/products/scanventory/'>Premium</a> plugin.</h3>
				<br />
			</td>
		</tr>
	</tbody>
</table>

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