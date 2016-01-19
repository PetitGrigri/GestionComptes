//sélection du mois à afficher
$('#mois_annee_en_cours').datepicker({
    format: "MM yyyy",
    minViewMode: 1,
    language: "fr",
    calendarWeeks: true,
    autoclose: true,
    orientation: "bottom"
}).on('changeDate',function(e){
	var date_selectionnee = $('#mois_annee_en_cours').datepicker('getDate');
	var page_lien = $('#mois_annee_en_cours a').attr('href');
	
	window.location = page_lien+'/'+date_selectionnee.getFullYear()+'/'+(date_selectionnee.getMonth()+1);
});

$('#mois_annee_en_cours').click(function(event){
	event.preventDefault();
});