$(function () 
		{
	$("button[data-action='delete']").click(function(e)
	{
		$this 			= $(this);
		$delete_form 	= $('#delete_mf');
		
		$delete_form.find('input[name="form[id]"]').val($this.attr('data-value'));
		$delete_form.submit();
	});
	$("button[data-action='check']").click(function(e)
	{
		$this 			= $(this);
		$check_form 	= $('#check_mf');
		
		$check_form.find('input[name="form[id]"]').val($this.attr('data-value'));
		$check_form.submit();
	});
});