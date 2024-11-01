<?php
/**
 * Mobile login template
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit; ?>

<form class="form-inline" role="form" method="POST" action="<?php echo $mainURL; ?>">
	<div class="panel panel-primary">

		<div class="panel-heading">Scanventory &mdash; Login</div>
		<div class="panel-body">
			<label class="sr-only" for="username">Username</label>
			<input type="username" name="username" class="form-control" placeholder="Username">
		</div>

		<div class="panel-body">
			<label class="sr-only" for="pass">Password</label>
			<input type="password" name="password" class="form-control" placeholder="Password">
		</div>
		<div class="panel-body">
			<button type="submit" class="btn btn-primary pull-right">Sign in</button>
		</div>
	</div>

	<?php if( !empty($message) ) { echo "<div class=\"panel-body\">"; echo "<div class=\"alert alert-warning\"> {$message} </div>"; echo "</div>"; } ?>
</form>