<script>
	$(function (){
		$.ajax({
        	url: '/posts/saveTitle?id=<?php echo $model->id; ?>',
        	type: 'POST',
        	success: function (data) {
			$('#link_thumb_pic').attr('src', data[1]);
		},
        	error: function (jqXHR, textStatus, errorThrown) {
		},
 		dataType: 'json',
    		});
	});
</script>