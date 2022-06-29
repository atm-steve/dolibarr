# Change Log
All notable changes to this project will be documented in this file.

### TODO R&D


## Unreleased

## Version 1.3
- FIX : SPE ARCOOP NDF : l'arrondi sur total tva à été corrigé et est correctement appliqué - 1.3.3_arcoop
- FIX : SPÉ ARCOOP écritures bancaires: utiliser entité de l'écriture plutôt 
  que du compte - *09/06/2022* - 1.3.2_arcoop
- FIX : Wrong calc for situation *07/04/2022* - 1.3.1 
- NEW : Colonne "compte collectif client" pour l'export InExtenso. - *17/02/2022* - 1.3.0  
  Note: pour les instances où le format a été personnalisé, il faudra ajouter la colonne
  "compte_collectif_client" dans le paramétrage du module (onglet "Formats" > afficher
  In Extenso), avec longueur 6 (minimum) et type "text".


## Version 1.2
- FIX : Conf not working: prevent regeneration of PDFs of invoices already sent to accounting - *21/03/2021* - 1.2.5
- FIX : Situation: improve calculation of total tax to avoid precision errors - *10/03/2021* - 1.2.4
- FIX : Situation empty progress *17/01/2021* - 1.2.3
- FIX : Add invoicesupplier context to avoid supplier invoice reopen *08/12/2021** - 1.2.2
