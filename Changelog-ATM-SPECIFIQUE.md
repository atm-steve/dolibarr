dolibarr/htdocs/compta/facture/card.php
l 4937 => partage de paiement entre entités

dolibarr/htdocs/compta/facture/card.php
    l 1522 => le commit 84f5e3677cf900ff9d268389f2cae84ca0801592 de la 8.0_arcoop
    n'avait pas été réintroduit lors de la MDV : TVA à 0 sur les factures d'acompte

dolibarr/htdocs/expensereport/card.php  
    Ne PAS afficher le bouton de validation pour les utilisateurs du groupe entrepreneur ID 1  
    l 2557 => utilisation conf ARCOOP_HIDE_VALIDATION_BTN_EXPENSEREPORT_FOR_GROUP du module cli

dolibarr/htdocs/conf/conf.php  
    ajout $multicompany_transverse_mode='1';
dolibarr/htdocs/compta/facture.php  
    l 3141	=> Permet l'envoi par e-mail même au statut brouillon

dolibarr/htdocs/core/lib/functions2.lib.php  
    l 713		=> Numérotation globale à toutes les entités

dolibarr/htdocs/core/lib/functions2.lib.php  
    l 1254	=> Permet l'affichage correct des modèles PDF eb transverse mode

dolibarr/htdocs/theme/bureau2crea/style.css.php  
    l 2114	=> float: left; enlevé pour bug affichage cloture devis

dolibarr/htdocs/custom/ndfp/class/ndfp.class.php  
    => Permet l'envoi par e-mail même au statut brouillon

dolibarr/htdocs/compta/facture.php  
    l 3059	=> Ne pas autoriser l'ajout de ligne libre (mantis 32)

dolibarr/htdocs/comm/propal.php  
    l 1648	=> Ne pas autoriser l'ajout de ligne libre (mantis 278)

dolibarr/htdocs/core/modules/facture/mod_facture_mercure.php  
    l 133		=> Préfixe devant la numérotation des acomptes (pour gestion prpre des règements)


//Suivi depuis le 04/07/2022 après la montée de version 14 ://

dolibarr/htdocs/expensereport/class/expensereport.class.php
    l 1259 - 1268 - 1269 - 1276 =>  Fix la validation d'une NDF créée depuis une entité
                                    avec l'entité maître.

dolibarr/htdocs/compta/class/facture.class.php
    l 2873 - 2885 - 2886 =>     Fix la validation d'une facture créée depuis une entité
                                avec l'entité maître.

dolibarr/htdocs/expensereport/card.php
    l 2632 - 2637 =>    Fix la modification d'une NDF par une entity si la NDF est au status
                        brouillon
