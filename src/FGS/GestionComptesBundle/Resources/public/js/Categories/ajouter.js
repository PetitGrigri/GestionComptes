$('#fgs_gestioncomptesbundle_compte_type_effacer').click(function(){
	$('form input[type="text"], form select').each(function(){$(this).val('')});
})
var icone = $('#fgs_gestioncomptesbundle_categorie_mouvement_financier_type_icone').val();

$('#fgs_gestioncomptesbundle_categorie_mouvement_financier_type_icone').iconpicker()
	.iconpicker('setPlacement', 'left')
	.iconpicker('setPlacement', 'bottom')
	.iconpicker('setCols', 6)
	.iconpicker('setSearch', false)
	.iconpicker('setLabelHeader', 'Page {0} de {1}')
	.iconpicker('setIconset', $.iconset_daily_life )
	.iconpicker('setIcon', icone ? icone : '')
	.on('change', function(e){
		$('#fgs_gestioncomptesbundle_categorie_mouvement_financier_type_icone').val(e.icon)
	});
