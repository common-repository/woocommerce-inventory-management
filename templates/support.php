<form method="post" action="?post_type=product&page=scanventory&tab=support">
	<table class="form-table">
		<tr valign="top">
			<th scope="row">Support Category</th>
			<td>
				<label for="category">
					<select name="category">
						<option>General Question</option>
						<option>Bug / Error</option>
						<option>Suggestion</option>
						<option>Feature Request</option>
					</select>
				</label>
			</td>
		</tr>
		 <tr valign="top">
			 <th scope="row">Message</th>
			 <td>
				<textarea name="message" cols="50" rows="10"></textarea>
			 </td>
		 </tr>
	</table>
	<?php
		if (isset($form_message)) echo "<B>{$form_message}</B>";
		submit_button('Send');
	?>
</form>