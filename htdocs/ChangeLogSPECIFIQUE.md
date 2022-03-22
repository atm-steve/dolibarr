*08/02/2021* 
Le tâche planifiée issue de la fonction createRecurringInvoices() dans le fichier facture-rec.class.php a été modifié pour pouvoir générer les factures en anticipé en fonction de la valeur
entrée dans la conf suivante : $conf->global->GENERATE_INVOICE_AHEAD_OF_TIME. Si par exemple, cette conf caché contient 6, les factures seront généré 6 jours avant la date à laquelle elles
devaient l'être. Ce code spécifique bypass la conf suivante : $conf->global->FAC_FORCE_DATE_VALIDATION qui consiste à forcer la date de facturation à la date de validation