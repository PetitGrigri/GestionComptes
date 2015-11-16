$(function () 
{
	$('#valider_suppression').click(function(e)
	{
		//stop de la propagation de l'évènement
		e.preventDefault();

		$('#delete_mfp').submit();	
	});
			
	$("button[data-action='delete']").click(function(e)
	{
		//stop de la propagation de l'évènement
		e.preventDefault();
		
		$this 			= $(this);
		$delete_form 	= $('#delete_mfp');
		
		//modification de la fenêtre de confirmation
		$('#nom_mfp').html($this.attr('data-libelle'));
		$('#nom_compte').html($this.attr('data-libelle-compte'));
		
		$delete_form.find('input[name="form[id]"]').val($this.attr('data-value'));
		
		//affichage de la fenêtre de confirmation
		$('#confirmation').modal('show');
	});
});