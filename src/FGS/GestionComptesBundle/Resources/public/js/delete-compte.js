$(function () 
{
	$('#valider_suppression').click(function(e)
	{
		//stop de la propagation de l'évènement
		e.preventDefault();

		$('#delete_compte').submit();	
	});
			
	$("button[data-action='delete']").click(function(e)
	{
		//stop de la propagation de l'évènement
		e.preventDefault();
		
		$this 			= $(this);
		$delete_form 	= $('#delete_compte');
		
		//modification de la fenêtre de confirmation
		$('#nom_compte').html($this.attr('data-compte'));
		$delete_form.find('input[name="form[id]"]').val($this.attr('data-value'));
		
		//affichage de la fenêtre de confirmation
		$('#confirmation').modal('show');
	});
});