$('#fgs_gestioncomptesbundle_mouvement_financier_type_date').datepicker({
		orientation: "auto right",
	    autoclose: true,
	    todayHighlight: true,
	    language: "fr",
}).on('show',function(e){

	if ($(document).width() < 480)
	{	
		$(this).datepicker('hide');
		$('#date .modal-content').datepicker({
			orientation: "auto right",
		    autoclose: true,
		    todayHighlight: true,
		    language: "fr",
		}).on('changeDate',function(e){
			$('#fgs_gestioncomptesbundle_mouvement_financier_type_date').datepicker('setDate', $(this).datepicker('getDate'));
			$('#date').modal('hide');
		});
		$('#date').modal('show');
	}
});