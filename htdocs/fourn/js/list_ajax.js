function listAjaxCommandeFourn(context) {
	$.ajax({
		url: context.url
	}).done(function (data) {
		// On remplace les liens de la pagination pour rester sur la liste de commandes fournisseurs en cas de changement de page
		var form_commandes = $(data).find('div.fiche form[action*="list.php"]');
		form_commandes.find('table.table-fiche-title a').each(function () {
			$(this).attr('href', $(this).attr('href').replace(context.pathToList, context.pathToOrderCustomer));
			$(this).attr('href', $(this).attr('href') + '&id=' + context.id) + context.yesno;
		});

		// On remplace les liens de tri pour rester sur la liste de commandes fournisseurs en cas de tri sur une colonne
		form_commandes.find('table.liste tr.liste_titre a').each(function () {
			$(this).attr('href', $(this).attr('href').replace(context.pathToList, context.pathToOrderCustomer));
			$(this).attr('href', $(this).attr('href') + '&id=' + context.id + context.yesno);
		});

		// Formulaire
		form_commandes.attr('action', form_commandes.attr('action').replace(context.pathToList, context.pathToOrderCustomer));
		form_commandes.attr('action', form_commandes.attr('action') + '?id=' + context.id + context.yesno);

		// Fait disparaitre le bouton d'ajout de commande fournisseur
		form_commandes.find('li.paginationafterarrows').remove();

		// On affiche la liste des commandes fournisseurs
		console.log($(data).find('div.fiche form'));
		$("#id-right > .fiche").append(form_commandes);

		if(context.action == 'delete') {
			window.location.href = context.pathToOrderCustomer + '?id=' + context.id + context.yesno;
		}


	});
}
