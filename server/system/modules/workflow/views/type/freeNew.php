<table class="table table-striped table-head-condensed table-head-inverse mbz" style="width: 360px;">
	<tbody>
		<tr>
			<td><input type="text" name="newuser" value="<?php echo $users;?>" id="new_user"></td>
		</tr>
	</tbody>
</table>
<script>
	(function() {
		var $userNew = $("#new_user");
		$userNew.userSelect({
			data: Ibos.data.get('user', 'position', 'department'),
			type: "all",
			box: $("<div></div>").appendTo(document.body)
		});
	})();
</script>