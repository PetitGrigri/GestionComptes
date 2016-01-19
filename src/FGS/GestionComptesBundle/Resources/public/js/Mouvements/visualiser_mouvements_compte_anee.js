$(function () {
  $('[data-toggle="tooltip"]').tooltip();
})

//sélection du mois à afficher
$('#annee_choix').datepicker({
    format: "yyyy",
    minViewMode: 2,
    language: "fr",
    calendarWeeks: true,
    autoclose: true,
    orientation: "bottom"
}).on('changeDate',function(e){
	var date_selectionnee = $('#annee_choix').datepicker('getDate');
	var page_lien = $('#annee_choix a').attr('href');
	window.location = page_lien+'/'+date_selectionnee.getFullYear();
});

$('#annee_choix').click(function(event){
	event.preventDefault();
});