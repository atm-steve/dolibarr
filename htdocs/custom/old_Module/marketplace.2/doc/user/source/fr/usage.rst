Usage
========================================

.. toctree::
   :maxdepth: 2
   :caption: Contents:

Configuration
####################################

Un écran de configuration permet de paramétrer le module :

 Catégorie fournisseur principale pour les taux de prélèvement
   Cette catégorie fournisseur sera utilisée pour trouver les taux de prélèvement/commission.

 Service utilisé pour les commissions
   Indiquer un service qui sera utilisé pour les factures « pour le compte de tier »

Gestion des taux de prélèvement
####################################

Des taux de prélèvement différents peuvent être paramétrés. Avant la création de taux de prélèvement il est nécessaire de crééer les catégories fournisseurs auxquelles on associera ensuite le taux de prélèvement.

.. image:: img/screenshot-fr-liste-taux.png
        :alt: Vue de la liste des taux de prélèvement
        :align: center

Liaison produit / tiers (fournisseur)
####################################

Pour savoir qui vend quoi, il faut lier le ou les produits à un tiers qui doit être marqué comme fournisseur.

Le module ajoute la possibilité de saisir le revendeur (champ Vendeur rétrocession) lors de la création ou l'édition de chaque produit.

.. image:: img/screenshot-fr-produit-fournisseur.png
        :alt: Liaison produit / fournisseur
        :align: center

Pour que le calcul de commission ait lieu lors des ventes **il faut que le vendeur (fournisseur dans Dolibarr) soit classé dans une catégorie qui correspond à un taux de prélèvement**.

Les commissions / prélèvements
#####################################

Lors de chaque vente faite dans Dolibarr ERP/CRM, le module MarketPlace va procéder au calcul du montant de la commission pour chaque ligne de facture, en fonction du tiers lié au produit (le vendeur). 
Le taux de commission est celui associé à la catégorie fournisseur à laquelle est lié ce tiers.

Plus de détails dans la section :ref:`rst_sales`.