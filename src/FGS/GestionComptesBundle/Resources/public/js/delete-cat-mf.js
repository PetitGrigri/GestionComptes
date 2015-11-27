$(function () 
{
	$('#valider_suppression').click(function(e)
	{
		//stop de la propagation de l'évènement
		e.preventDefault();

		$('#delete_cat').submit();	
	});
			
	$("button[data-action='delete']").click(function(e)
	{
		//stop de la propagation de l'évènement
		e.preventDefault();
		
		$this 			= $(this);
		$delete_form 	= $('#delete_cat');
		
		//modification de la fenêtre de confirmation
		$('#ma_categorie').html($this.attr('data-categorie'));
		$('#son_parent').html($this.attr('data-parent'));
		$delete_form.find('input[name="form[id]"]').val($this.attr('data-value'));
		
		//affichage de la fenêtre de confirmation
		$('#confirmation').modal('show');
	});
});