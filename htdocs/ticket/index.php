<?php
/**
 * Copyright (C) - ATM Consulting 2020
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

$res = 0;
if (file_exists("../main.inc.php")) {
	$res = include "../main.inc.php"; // From htdocs directory
} elseif (!$res && file_exists("../../main.inc.php")) {
	$res = include "../../main.inc.php"; // From "custom" directory
} else {
	die("Include of main fails");
}

require_once 'class/ticket.class.php';
require_once DOL_DOCUMENT_ROOT . '/ticket/class/actions_ticket.class.php';
require_once DOL_DOCUMENT_ROOT . '/ticket/class/ticketstats.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/dolgraph.class.php';

// Load traductions files requiredby by page
$langs->load("companies");
$langs->load("other");
$langs->load("ticket@ticket");

$WIDTH = DolGraph::getDefaultGraphSizeForStats('width');
$HEIGHT = DolGraph::getDefaultGraphSizeForStats('height');

// Year
$nowyear = strftime("%Y", dol_now());
$startyear = $year - 1;

// Get parameters
$id = GETPOST('id', 'int');
$msg_id = GETPOST('msg_id', 'int');
$action = GETPOST('action', 'aZ09');

if ($user->societe_id) {
	$socid = $user->societe_id;
}

// Security check
$result = restrictedArea($user, 'ticket', 0, '', '', '', '');

$object = new ActionsTicket($db);
$stats = new TicketStats($db, $socid, $userid);
$form = new Form($db);

/*******************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 ********************************************************************/



/***************************************************
 * PAGE
 *
 * Put here all code to build page
 ****************************************************/

llxHeader('', $langs->trans('Ticket'), '');

print '<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>';
print '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">';
print '<div class="fichecenter"><div class="fichethirdleft">';
print '<h3 id="NONASSIGNES">Tickets à prendre en charge</h3>';

/*
 * Derniers tickets non attribués
 */
$max = 100;

$sql ="SELECT ";
$sql.=" t.rowid,";
$sql.=" t.ref,";
$sql.=" t.track_id,";
$sql.=" t.datec,";
$sql.=" t.subject,";
$sql.=" t.type_code,";
$sql.=" t.category_code,";
$sql.=" t.severity_code, ";
$sql.=" type.label as type_label,";
$sql.=" category.label as category_label,";
$sql.=" severity.label as severity_label,";
$sql.=" extra.date_prev as deadline,";
$sql.=" IF(DATEDIFF(extra.date_prev,NOW()) <=1,'Alerte','') AS 'Alerte',";
$sql.=" DATEDIFF(extra.date_prev,NOW()) AS 'delais',";
$sql.=" extra.obj_mail AS 'obj_mail'";
$sql.=" FROM " . MAIN_DB_PREFIX . "ticket as t";
$sql.=" LEFT JOIN " . MAIN_DB_PREFIX . "c_ticket_type as type ON type.code=t.type_code";
$sql.=" LEFT JOIN " . MAIN_DB_PREFIX . "c_ticket_category as category ON category.code=t.category_code";
$sql.=" LEFT JOIN " . MAIN_DB_PREFIX . "c_ticket_severity as severity ON severity.code=t.severity_code";
$sql.=" LEFT JOIN " . MAIN_DB_PREFIX . "ticket_extrafields as extra ON extra.fk_object=t.rowid";

if (!$user->rights->societe->client->voir && !$socid) {
	$sql.= ", " . MAIN_DB_PREFIX."societe_commerciaux as sc";
}

$sql.=" WHERE t.entity IN (".getEntity('ticket', 1).")";
$sql.=" AND t.fk_statut IN(0,1,3)";
$sql.=" AND (t.fk_user_assign IS NULL OR t.fk_user_assign = 29)";
$sql.=" AND t.category_code IN ('INTERNE', 'CMD','EQUIPEMENT','INCIDENT','SUIVACT')";
$sql.=" AND t.type_code NOT IN ('PROPAL','PROJET','MEPPROJET' )";
$sql.=" AND (extra.date_prev <= DATE_ADD(NOW(), INTERVAL 30 DAY) OR extra.date_prev IS NULL) AND extra.pool_ttmt = 'pec'";

if (!$user->rights->societe->client->voir && !$socid) {
	$sql .= " AND t.fk_soc = sc.fk_soc AND sc.fk_user = ".$user->id;
}

if ($user->societe_id > 0) {
	$sql.= " AND t.fk_soc='" . $user->societe_id . "'";
} else {
	// Restricted to assigned user only
	if ($conf->global->TICKET_LIMIT_VIEW_ASSIGNED_ONLY && !$user->rights->ticket->manage) {
		$sql .= " AND t.fk_user_assign=" . $user->id;
	}
}
$sql .= $db->order("t.datec", "DESC");
$sql .= $db->plimit($max, 0);

$result = $db->query($sql);
if ($result) {
	$num = $db->num_rows($result);

	$i = 0;

	$transRecordedType = $langs->trans("LastNewTickets", $max);
	print '<table id ="nonassigne" class="display">';
	print '<thead>';
	print '<th>' . $langs->trans('Ref') . '</th>';
	print '<th>' . $langs->trans('Subject') . '</th>';
	print '<th>' . $langs->trans('Date prév') . '</th>';
	print '<th>' . $langs->trans('Type') . '</th>';
    //print '<th>' . $langs->trans('TeamUP') . '</th>';
	print '<th>' . $langs->trans('Severity') . '</th>';
	print '<th></th>';
	print '</tr></thead>';
	print '<tbody>';
	if ($num > 0) {
		$var = true;

		while ($i < $num) {
			$objp = $db->fetch_object($result);

			$var = !$var;
			print "<tr $bc[$var]>";

			// Ref
			print '<td>';
			print '<a href="card.php?track_id=' . $objp->track_id . '">' . $objp->ref . '</a>';
			print "</td>\n";

			// Subject
			print '<td>';
			print '<a href="card.php?track_id=' . $objp->track_id . '">' . $objp->subject . '</a>';
			if ($objp->Alerte != "") {
				print img_warning($objp->delais,$objp->delais);
			}
			print "</td>\n";

			// Deadline
			print '<td>';
			print $objp->deadline;
			print '</td>';
			
			// Type
            print '<td>';
            print $objp->type_label;
            print '</td>';
			

            // Severity
            print '<td>';
            print $objp->severity_label;
            print "</td>";

			// Prendre en charge
			print '<td>';
			print '<a href="card.php?track_id=' . $objp->track_id . '&action=view&set=assign_ticket"><img src="'.DOL_MAIN_URL_ROOT.'/theme/eldy/img/edit.png" border="0" alt="Prendre en charge" title=""></a>';
			print "</td>";
			print "</tr>\n";
			$i++;
		}

		$db->free();
	} else {
		print '<tr><td colspan="6"><div class="info">' . $langs->trans('NoTicketsFound') . '</div></td></tr>';
	}

	print "</tbody>
	</table>";
} else {
	dol_print_error($db);
}

print '
<script type="text/javascript">
	$(document).ready(function() {
		$(\'#nonassigne\').DataTable({
			"iDisplayLength": 20,
			"order": [[2,"asc"]]
			});
	} );
</script>
';

print '<h4 id="NONASSIGNESPJT">Pré-traitement</h4>';
/*
 * Derniers tickets non attribués (pretraitement)
 */
$max = 100;
$sql = "SELECT t.rowid, t.ref, t.track_id, t.datec, t.subject, t.type_code, t.category_code, t.severity_code";
$sql .= ", type.label as type_label, category.label as category_label, severity.label as severity_label, extra.deadline as deadline, IF(DATEDIFF(extra.deadline,NOW()) <=1,'Alerte','') AS 'Alerte', DATEDIFF(extra.deadline,NOW()) AS 'delais'";
$sql .= " FROM " . MAIN_DB_PREFIX . "ticket as t";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_ticket_type as type ON type.code=t.type_code";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_ticket_category as category ON category.code=t.category_code";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_ticket_severity as severity ON severity.code=t.severity_code";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ticket_extrafields as extra ON extra.fk_object=t.rowid";
if (!$user->rights->societe->client->voir && !$socid) {
    $sql .= ", " . MAIN_DB_PREFIX . "societe_commerciaux as sc";
}

$sql .= ' WHERE t.entity IN (' . getEntity('ticket', 1) . ')';
$sql .= " AND t.fk_statut IN(0,1,3) AND (t.fk_user_assign IS NULL OR t.fk_user_assign = 29) AND t.category_code IN ('INTERNE', 'CMD','EQUIPEMENT','INCIDENT','SUIVACT') AND t.type_code NOT IN ('PROPAL','PROJET','MEPPROJET' ) AND extra.deadline <= DATE_ADD(NOW(), INTERVAL 30 DAY) AND (extra.pool_ttmt = 'pretraitement' OR extra.pool_ttmt='' OR extra.pool_ttmt = '0')";
if (!$user->rights->societe->client->voir && !$socid) {
    $sql .= " AND t.fk_soc = sc.fk_soc AND sc.fk_user = " . $user->id;
}

if ($user->societe_id > 0) {
    $sql .= " AND t.fk_soc='" . $user->societe_id . "'";
} else {
    // Restricted to assigned user only
    if ($conf->global->TICKETS_LIMIT_VIEW_ASSIGNED_ONLY && !$user->rights->ticket->manage) {
        $sql .= " AND t.fk_user_assign=" . $user->id;
    }
}
$sql .= $db->order("t.datec", "DESC");
$sql .= $db->plimit($max, 0);

//print $sql;
$result = $db->query($sql);
if ($result) {
    $num = $db->num_rows($result);

    $i = 0;

    $transRecordedType = $langs->trans("LastNewTickets", $max);
    print '<table id ="nonassignepjt" class="display">';
    print '<thead>';
	//print '<tr><th>Tickets à prendre en charge</th>';
    print '<th>' . $langs->trans('Ref') . '</th>';
    print '<th>' . $langs->trans('Subject') . '</th>';
	print '<th>' . $langs->trans('Deadline PEC') . '</th>';
	print '<th>' . $langs->trans('Type') . '</th>';
   // print '<th>' . $langs->trans('Category') . '</th>';
    print '<th>' . $langs->trans('Severity') . '</th>';
	print '<th></th>';
    print '</tr></thead>';
	print '<tbody>';
    if ($num > 0) {
        $var = true;

        while ($i < $num) {
            $objp = $db->fetch_object($result);

            $var = !$var;
            print "<tr $bc[$var]>";
            // Creation date
           /* print '<td align="left">';
            print dol_print_date($db->jdate($objp->datec), 'dayhour');
            print "</td>";*/

            // Ref
            print '<td>';
            print '<a href="card.php?track_id=' . $objp->track_id . '">' . $objp->ref . '</a>';
            print "</td>\n";

            // Subject
            print '<td>';
            print '<a href="card.php?track_id=' . $objp->track_id . '">' . $objp->subject . '</a>';
						if ($objp->Alerte != "") {
				print img_warning($objp->delais,$objp->delais);
			}
            print "</td>\n";

            // Deadline
            print '<td>';
            print $objp->deadline;
            print '</td>';
			
			// Type
            print '<td>';
            print $objp->type_label;
            print '</td>';
			

            // Category
           /* print '<td>';
            print $objp->category_label;
            print "</td>";*/

            // Severity
            print '<td>';
            print $objp->severity_label;
            print "</td>";
			
			 // Prendre en charge
            print '<td>';
            print '<a href="card.php?track_id=' . $objp->track_id . '&action=view&set=assign_ticket"><img src="/htdocs/theme/eldy/img/edit.png" border="0" alt="Prendre en charge" title=""></a>';
            print "</td>";
            print "</tr>\n";
            $i++;
        }
				print '<script type="text/javascript">
$(document).ready(function() {
    $(\'#nonassignepjt\').DataTable({
		"iDisplayLength": 20,
		"order": [[2,"asc"]]
		});
} );
</script>';

        $db->free();
    } else {
        print '<tr><td colspan="6"><div class="info">' . $langs->trans('NoTicketsFound') . '</div></td></tr>';

    }

    print "</tbody>
	</table>";
} else {
    dol_print_error($db);
}


print '</div><div class="fichetwothirdright">';
print '<div class="ficheaddleft">';

/*
 * Repartition de la charge par utilisateurs
 */
print '<h3 id="CHARGE">Répartition de la charge</h3>';

$max = 25;
$sql = "SELECT concat('<a href=http://dolibarr.cell-and-co.local/htdocs/ticket/list.php?sortfield=t.fk_statut&sortorder=desc&begin=&search_fk_user_assign=',llx_user_0.rowid,'&search_fk_status=-1&search_fk_status=non_closed\>',llx_user_0.firstname,' ',llx_user_0.lastname,'</a>') AS 'login', Count(if(llx_ticket_0.fk_statut IN (0,1,3), llx_ticket_0.subject,NULL)) AS 'Nombre', Count(if(llx_ticket_0.fk_statut IN (2), llx_ticket_0.subject,NULL)) AS 'NombreAssigne', Count(if(llx_ticket_0.fk_statut IN(4,5,6), llx_ticket_0.subject,NULL)) AS 'NombreAttente',  round(sum(IF(llx_ticket_0.fk_statut NOT IN (0,6),llx_ticket_extrafields_0.heure_est,NULL)),1) AS 'Somme', round(sum(IF(llx_ticket_0.fk_statut = 6,llx_ticket_extrafields_0.heure_est,0)),1) AS 'SommeAttente'";
$sql .= " FROM llx_ticket as llx_ticket_0, llx_ticket_extrafields as llx_ticket_extrafields_0, llx_user as llx_user_0, llx_usergroup_user";
if (!$user->rights->societe->client->voir && !$socid) {
    $sql .= ", " . MAIN_DB_PREFIX . "societe_commerciaux as sc";
}

$sql .= " WHERE  llx_ticket_0.fk_user_assign = llx_user_0.rowid AND llx_ticket_extrafields_0.fk_object = llx_ticket_0.rowid AND llx_usergroup_user.fk_user = llx_user_0.rowid AND llx_usergroup_user.fk_usergroup = 16";
$sql .= " AND  llx_ticket_0.fk_statut NOT IN(8) ";
$sql .= " GROUP BY llx_user_0.lastname";
//$sql .= $db->order("Somme", "DESC");

//print $sql;
$result = $db->query($sql);
if ($result) {
    $num = $db->num_rows($result);

    $i = 0;

    $transRecordedType = $langs->trans("Repartition", $max);
    print '<table id ="repartition" class="display">';
	print '<thead>';
    print '<tr>';
    print '<th>' . $langs->trans('Responsable') . '</th>';
	print '<th>' . $langs->trans('Nombre de tickets Assignés') . '</th>';
    print '<th>' . $langs->trans('Nombre de tickets En cours') . '</th>';
	print '<th>' . $langs->trans('Nombre de tickets En attente') . '</th>';
    print '</tr>';
	print '</thead>
	<tbody>';
    if ($num > 0) {
        $var = true;

        while ($i < $num) {
            $objp = $db->fetch_object($result);

            $var = !$var;
            print "<tr $bc[$var]>";
            // Login
            print '<td class="nowrap">';
            print $objp->login;
            print "</td>\n";
			
			// Login
            print '<td class="nowrap">';
            print $objp->NombreAssigne;
            print "</td>\n";
			
            // Nombre
            print '<td class="nowrap">';
            print $objp->Nombre;
			print "</td>\n";
			
			//Nombre attente
            print '<td class="nowrap">';
			print $objp->NombreAttente;
			print "</td>\n";			
		
            print "</tr>\n";
			
            $i++;
        }

        $db->free();
    } else {
        print '<tr><td colspan="6"><div class="info">' . $langs->trans('NoTicketsFound') . '</div></td></tr>';
    }

    print "</tbody>
	</table>";
} else {
    dol_print_error($db);
}
print '</div></div></div>';
print '<div class="inline-block divButAction"><a class="butAction" href="#NONASSIGNES"> Non assignés </a></div> <div class="inline-block divButAction"><a class="butAction" href="#MODELES"> Modèles </a></div>';
print '<div class="inline-block divButAction"><a class="butAction" href="#LAST"> Tickets en cours</a></div>';// <div class="inline-block divButAction"><a class="butAction" href="#SI"> Tickets SI </a></div>';
//print '<div class="inline-block divButAction"><a class="butAction" href="#STAT">Statistiques</a></div>';
print '<div class="inline-block divButAction"><a class="butAction" href="#planning">Planning</a></div>';
print '<div class="inline-block divButAction"><a class="butAction" href="#TRANSPORT">Transports</a></div>';
print '<div class="inline-block divButAction"><a class="butAction" href="#LONGTERME">Long terme</a></div>';
print '<div style="clear:both"></div>';
print '<br/>';

print '<script type="text/javascript">
$(document).ready(function() {
    $(\'#repartition\').DataTable({
		"iDisplayLength": 20,
		"order": [[0,"asc"]]
		});
} );
</script>';


/** Calendrier **/

print '<h3 id="planning">Planning<div class="inline-block divButAction"><a class="butAction" href="#repartition">Top</a></h3>';
print '<iframe src="https://teamup.com/ksnxk439d2wmgjisax?lang=fr&showLogo=0&showProfileAndInfo=0&showSidepanel=0&disableSidepanel=0&showTitle=0&showViewSelector=1&showMenu=0&showAgendaHeader=1&showAgendaDetails=1&showYearViewHeader=1" width="100%" height="800px" style="border: 1px solid #cccccc" frameborder="0"></iframe>';


print '<h3 id="MODELES">Modèles de tickets<div class="inline-block divButAction"><a class="butAction" href="#repartition">Top</a></h3><div>';

$max = 25;
$sql = "SELECT llx_cust_modelesTickets_extrafields.typeTicket Type, llx_cust_modelesTickets_extrafields.titreTicket Titre, llx_projet.title Projet, llx_projet.ref RefProjet, llx_projet.fk_soc CID, llx_projet.rowid PID, llx_cust_modelesTickets_extrafields.urlCreationTicket URL, CONCAT('http://dolibarr.cell-and-co.local/htdocs/ticket/card.php?track_id=',subString(llx_cust_modelesTickets_extrafields.urlCreationTicket,71,16)) AS LienModele, llx_ticket.rowid, CONCAT('http://dolibarr.cell-and-co.local/htdocs/ticket/card.php?clone_id=',llx_ticket.rowid,'&action=create&projectid=',llx_projet.rowid) AS new_ticket
FROM llx_projet
LEFT JOIN llx_cust_modelesTickets_extrafields ON llx_cust_modelesTickets_extrafields.fk_object = llx_projet.rowid
LEFT JOIN llx_ticket ON llx_ticket.track_id = subString(llx_cust_modelesTickets_extrafields.urlCreationTicket,71,16)";
//$sql .= $db->order("Somme", "DESC");

//print $sql;
$result = $db->query($sql);
if ($result) {
    $num = $db->num_rows($result);

    $i = 0;

    $transRecordedType = $langs->trans("modèles", $max);
    print '<table id ="modeles" class="display">';
	print '<thead>';
    print '<tr>';
    print '<th>Ticket projet</th>';
    //print '<th>Type</th>';
	print '<th>Création du ticket (old)</th>';
	print '<th>Création du ticket (new)</th>';
	print '<th>Ticket du projet</th>';
	print '<th>Modèles</th>';
    print '</tr>';
	print '</thead>
	<tbody>';
    if ($num > 0) {
        $var = true;

        while ($i < $num) {
            $objp = $db->fetch_object($result);

            $var = !$var;
            print "<tr $bc[$var]>";
            // Projet
            print '<td class="nowrap">';
            print '<a href="card.php?action=create&origin=projet_project&originid='.$objp->PID.'&socid='.$objp->CID.'">'.$objp->Projet.'</a>';
            print "</td>\n";
			
            // Type
           /* print '<td class="nowrap">';
            print $objp->Type;
			print "</td>\n";*/
			
			//Création du ticket (old)
            print '<td class="nowrap">';
			print '<a href="'.$objp->URL.'">'.$objp->Titre.'</a>';
			print "</td>\n";	

			//Création du ticket (new)
            print '<td class="nowrap">';
			print '<a href="'.$objp->new_ticket.'">'.$objp->Titre.'</a>';
			print "</td>\n";

			//Ticket du projet
            print '<td class="nowrap">';
			print '<a href="http://dolibarr.cell-and-co.local/htdocs/ticket/list.php?projectid='.$objp->PID.'">Tickets du projet</a>';
			print "</td>\n";

			//Modèle
            print '<td class="nowrap">';
			print '<a href="'.$objp->LienModele.'">Modèle</a>';
			print "</td>\n";			
		
            print "</tr>\n";
			
            $i++;
        }

        $db->free();
    } else {
        print '<tr><td colspan="6"><div class="info">' . $langs->trans('NoTicketsFound') . '</div></td></tr>';
    }

    print "</tbody>
	</table>";
} else {
    dol_print_error($db);
}

print '<script type="text/javascript">
$(document).ready(function() {
    $(\'#modeles\').DataTable({
		"iDisplayLength": 20
		});
} );
</script>';
        /*set_include_path('/home/dolibarr/httpdocs/reportico/');
		require_once 'reportico.php';        // Include Reportico
        $q = new reportico();                         // Create instance
		$q->initial_project = "CellAndCo";            // Name of report project folder    
        $q->initial_report = "CreationTicket";           // Name of report to run
		$q->initial_execute_mode = "EXECUTE";         // Just executes specified report
		$q->access_mode = "REPORTOUTPUT";
		$q->output_template_parameters["show_hide_report_output_title"] = "hide";
		$q->bootstrap_styles = "3";                   // Set to "3" for bootstrap v3, "2" for V2 or false for no bootstrap
        $q->force_reportico_mini_maintains = true;    // Often required
        $q->bootstrap_preloaded = false;               // true if you dont need Reportico to load its own bootstrap
		$q->embedded_report = true;
        $q->clear_reportico_session = true;
		$q->reportico_ajax_mode = true;
		$q->session_namespace = "MODELES";
		$q->execute();
		print '</div>';*/

/* 
 * Transport
 */
 print '<h3 id="TRANSPORT">Suivi des transports<div class="inline-block divButAction"><a class="butAction" href="#repartition">Top</a></h3>';
 
 /*SELECT llx_commande_extrafields.cmd_titre Description, SuiviTransport(llx_commande_extrafields.demandetpt,llx_commande_extrafields.cmd_transporteur) Tracking, llx_commande.rowid, CONCAT(llx_projet.ref," - ",llx_projet.title) Projet, llx_commande_extrafields.cmd_transporteur, llx_commande_extrafields.cmd_type_tpt, llx_commande_extrafields.datedenlevement, llx_commande_extrafields.datedelivraison, llx_commande.ref, llx_commande_extrafields.dateenlevementprev, llx_commande_extrafields.datelivraisonprev, llx_user.lastname, llx_commande.fk_statut
FROM llx_societe, llx_projet, llx_commande, llx_commande_extrafields, llx_user
WHERE llx_projet.rowid = llx_commande.fk_projet 
AND llx_societe.rowid = llx_commande.fk_soc
AND llx_commande.rowid = llx_commande_extrafields.fk_object
AND llx_commande_extrafields.cmd_transporteur IS NOT NULL
AND llx_commande.fk_statut < 3 AND llx_commande.fk_statut > 0
AND llx_user.rowid = llx_commande.fk_user_valid
ORDER BY llx_commande_extrafields.dateenlevementprev ASC*/

$max = 50;
$sql = "SELECT llx_commande_extrafields.cmd_titre as Description, SuiviTransport(llx_commande_extrafields.demandetpt,llx_commande_extrafields.cmd_transporteur) as Tracking, llx_commande.rowid as bc, llx_projet.title as Projet, llx_commande_extrafields.cmd_transporteur as transporteur, llx_commande_extrafields.cmd_type_tpt, llx_commande_extrafields.datedenlevement, llx_commande_extrafields.datedelivraison, llx_commande.ref refBC, llx_commande_extrafields.dateenlevementprev, llx_commande_extrafields.datelivraisonprev, llx_user.lastname, llx_commande.fk_statut";
$sql .= " FROM llx_societe, llx_projet, llx_commande, llx_commande_extrafields, llx_user";
$sql .= " WHERE llx_projet.rowid = llx_commande.fk_projet ";
$sql .= " AND llx_societe.rowid = llx_commande.fk_soc AND llx_commande.rowid = llx_commande_extrafields.fk_object AND llx_commande_extrafields.cmd_transporteur IS NOT NULL
AND llx_commande.fk_statut < 3 AND llx_commande.fk_statut > 0 AND llx_user.rowid = llx_commande.fk_user_valid AND llx_commande_extrafields.dateenlevementprev IS NOT NULL ";
$sql .= $db->order("llx_commande_extrafields.dateenlevementprev", "ASC");
$sql .= $db->plimit($max, 0);

//print $sql;
$result = $db->query($sql);
if ($result) {
    $num = $db->num_rows($result);

    $i = 0;

    $transRecordedType = $langs->trans("Suivi transport", $max);
    print '<table id ="transport" class="display">';
    print '<thead>';
    print '<tr><th>' . $langs->trans('Description') . '</th>';
    print '<th>' . $langs->trans('Tracking') . '</th>';
	print '<th>' . $langs->trans('Commande') . '</th>';
	print '<th>' . $langs->trans('Enlèvement') . '</th>';
    print '<th>' . $langs->trans('Livraison') . '</th>';
    print '<th>' . $langs->trans('Projet') . '</th>';
    print '</tr></thead>';
	print '<tbody>';
    if ($num > 0) {
        $var = true;

        while ($i < $num) {
            $objp = $db->fetch_object($result);

            $var = !$var;
            print "<tr $bc[$var]>";
            // Description
            print '<td align="left">';
            print $objp->Description ;
            print "</td>";

            // Tracking
            print '<td>';
            print $objp->transporteur . ': '. $objp->Tracking ;
            print "</td>\n";

            // BC
            print '<td>';
            print '<a href="http://dolibarr.cell-and-co.local/htdocs/commande/card.php?id=' . $objp->bc . '">' . $objp->refBC . '</a>';
						/*if ($objp->Alerte != "") {
				print img_warning($objp->delais,$objp->delais);
			}*/
            print "</td>\n";

            // Enlèvement
            print '<td>';
			print 'Planifié le :' . $objp->dateenlevementprev;
			print '<br/>Réel :' . $objp->datedenlevement;
            print '</td>';
			
			// Livraison
            print '<td>';
            print 'Planifié le :' . $objp->datelivraisonprev;
			print '<br/>Réel :' . $objp->datedelivraison;
            print '</td>';
			

            // Projet
            print '<td>';
            print $objp->Projet;
            print "</td>";

            print "</tr>\n";
            $i++;
        }

        $db->free();
    } else {
        print '<tr><td colspan="6"><div class="info">' . $langs->trans('Pas de transport') . '</div></td></tr>';
    }

    print "</tbody>
	</table>";
} else {
    dol_print_error($db);
}

print '<script type="text/javascript">
$(document).ready(function() {
    $(\'#transport\').DataTable({
		"iDisplayLength": 20
		});
} );
</script>';


/*
 * Planification à plus d'un mois
 */
print '<h3 id="LONGTERME">Planification à plus d\'un mois<div class="inline-block divButAction"><a class="butAction" href="#repartition">Top</a></h3>';

$max = 50;
$sql = "SELECT t.rowid, t.ref, t.track_id, t.datec, t.subject, t.type_code, t.category_code, t.severity_code";
$sql .= ", type.label as type_label, category.label as category_label, severity.label as severity_label, extra.deadline as deadline, IF(DATEDIFF(extra.deadline,NOW()) <=1,'Alerte','') AS 'Alerte', DATEDIFF(extra.deadline,NOW()) AS 'delais'";
$sql .= " FROM " . MAIN_DB_PREFIX . "ticket as t";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_ticket_type as type ON type.code=t.type_code";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_ticket_category as category ON category.code=t.category_code";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_ticket_severity as severity ON severity.code=t.severity_code";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ticket_extrafields as extra ON extra.fk_object=t.rowid";
if (!$user->rights->societe->client->voir && !$socid) {
    $sql .= ", " . MAIN_DB_PREFIX . "societe_commerciaux as sc";
}

$sql .= ' WHERE t.entity IN (' . getEntity('ticket', 1) . ')';
$sql .= " AND t.fk_statut<>8 AND t.fk_user_assign IS NULL AND t.category_code IN ('INTERNE', 'CMD','EQUIPEMENT','INCIDENT','SUIVACT') AND extra.deadline >= DATE_ADD(NOW(), INTERVAL 30 DAY)";
if (!$user->rights->societe->client->voir && !$socid) {
    $sql .= " AND t.fk_soc = sc.fk_soc AND sc.fk_user = " . $user->id;
}

if ($user->societe_id > 0) {
    $sql .= " AND t.fk_soc='" . $user->societe_id . "'";
} else {
    // Restricted to assigned user only
    if ($conf->global->TICKETS_LIMIT_VIEW_ASSIGNED_ONLY && !$user->rights->ticket->manage) {
        $sql .= " AND t.fk_user_assign=" . $user->id;
    }
}
$sql .= $db->order("t.datec", "DESC");
$sql .= $db->plimit($max, 0);

//print $sql;
$result = $db->query($sql);
if ($result) {
    $num = $db->num_rows($result);

    $i = 0;

    $transRecordedType = $langs->trans("Planification long-terme", $max);
    print '<table id ="longterme" class="display">';
    print '<thead>';
	print '<tr><th>Tickets à prendre en charge</th>';
    print '<th>' . $langs->trans('Ref') . '</th>';
    print '<th>' . $langs->trans('Subject') . '</th>';
	print '<th>' . $langs->trans('Deadline PEC') . '</th>';
	print '<th>' . $langs->trans('Type') . '</th>';
    print '<th>' . $langs->trans('Category') . '</th>';
    print '<th>' . $langs->trans('Severity') . '</th>';
	print '<th></th>';
    print '</tr></thead>';
	print '<tbody>';
    if ($num > 0) {
        $var = true;

        while ($i < $num) {
            $objp = $db->fetch_object($result);

            $var = !$var;
            print "<tr $bc[$var]>";
            // Creation date
            print '<td align="left">';
            print dol_print_date($db->jdate($objp->datec), 'dayhour');
            print "</td>";

            // Ref
            print '<td>';
            print '<a href="card.php?track_id=' . $objp->track_id . '">' . $objp->ref . '</a>';
            print "</td>\n";

            // Subject
            print '<td>';
            print '<a href="card.php?track_id=' . $objp->track_id . '">' . $objp->subject . '</a>';
						if ($objp->Alerte != "") {
				print img_warning($objp->delais,$objp->delais);
			}
            print "</td>\n";

            // Deadline
            print '<td>';
            print $objp->deadline;
            print '</td>';
			
			// Type
            print '<td>';
            print $objp->type_label;
            print '</td>';
			

            // Category
            print '<td>';
            print $objp->category_label;
            print "</td>";

            // Severity
            print '<td>';
            print $objp->severity_label;
            print "</td>";
			
			 // Prendre en charge
            print '<td>';
            print '<a href="card.php?track_id=' . $objp->track_id . '&action=view&set=assign_ticket"><img src="/htdocs/theme/eldy/img/edit.png" border="0" alt="Prendre en charge" title=""></a>';
            print "</td>";
            print "</tr>\n";
            $i++;
        }

        $db->free();
    } else {
        print '<tr><td colspan="5"><div class="info">' . $langs->trans('NoTicketsFound') . '</div></td></tr>';
    }

    print "</tbody>
	</table>";
} else {
    dol_print_error($db);
}

print '<script type="text/javascript">
$(document).ready(function() {
    $(\'#longterme\').DataTable({
		"iDisplayLength": 20,
		"order": [[3,"asc"]]
		});
} );
</script>';


/*
 * Last tickets
 */

print '<h3 id="LAST">Tickets en cours <div class="inline-block divButAction"><a class="butAction" href="#repartition">Top</a></h3>';

//$max = 30;
$sql = "SELECT t.rowid, t.ref, t.track_id, t.datec, t.subject, t.type_code, t.category_code, t.severity_code";
$sql .= ", type.label as type_label, category.label as category_label, severity.label as severity_label, extra.deadline as deadline";
$sql .= " FROM " . MAIN_DB_PREFIX . "ticket as t";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_ticket_type as type ON type.code=t.type_code";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_ticket_category as category ON category.code=t.category_code";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_ticket_severity as severity ON severity.code=t.severity_code";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "ticket_extrafields as extra ON extra.fk_object=t.rowid";
if (!$user->rights->societe->client->voir && !$socid) {
    $sql .= ", " . MAIN_DB_PREFIX . "societe_commerciaux as sc";
}

$sql .= ' WHERE t.entity IN (' . getEntity('ticket', 1) . ')';
$sql .= " AND t.fk_statut<8";
if (!$user->rights->societe->client->voir && !$socid) {
    $sql .= " AND t.fk_soc = sc.fk_soc AND sc.fk_user = " . $user->id;
}

if ($user->societe_id > 0) {
    $sql .= " AND t.fk_soc='" . $user->societe_id . "'";
} else {
    // Restricted to assigned user only
    if ($conf->global->TICKETS_LIMIT_VIEW_ASSIGNED_ONLY && !$user->rights->ticket->manage) {
        $sql .= " AND t.fk_user_assign=" . $user->id;
    }
}
$sql .= $db->order("t.tms", "DESC");
//$sql .= $db->plimit($max, 0);

//print $sql;
$result = $db->query($sql);
if ($result) {
    $num = $db->num_rows($result);

    $i = 0;

    //$transRecordedType = $langs->trans("LastNewTickets", $max);
    print '<table id ="derniers" class="display">';
    print '<thead>';
	print '<tr><th>Tickets en cours</th>';
	print '<th>' . $langs->trans('ID') . '</th>';
    print '<th>' . $langs->trans('Ref') . '</th>';
    print '<th>' . $langs->trans('Subject') . '</th>';
	print '<th>' . $langs->trans('Deadline PEC') . '</th>';
	print '<th>' . $langs->trans('Type') . '</th>';
    print '<th>' . $langs->trans('Category') . '</th>';
    print '<th>' . $langs->trans('Severity') . '</th>';
    print '</tr>';
	print '</thead>
	<tbody>';
    if ($num > 0) {
        $var = true;

        while ($i < $num) {
            $objp = $db->fetch_object($result);

            $var = !$var;
            print "<tr $bc[$var]>";
            // Creation date
            print '<td align="left">';
            print dol_print_date($db->jdate($objp->datec), 'dayhour');
            print "</td>";

            // ID
            print '<td>';
            print $objp->rowid ;
            print "</td>";

            // Ref
            print '<td>';
            print '<a href="card.php?track_id=' . $objp->track_id . '">' . $objp->ref . '</a>';
            print "</td>";

            // Subject
            print '<td>';
            print '<a href="card.php?track_id=' . $objp->track_id . '">' . $objp->subject . '</a>';
            print "</td>";

            // Deadline
            print '<td>';
            print $objp->deadline;
            print '</td>';
			
			// Type
            print '<td>';
            print $objp->type_label;
            print '</td>';
			

            // Category
            print '<td>';
            print $objp->category_label;
            print "</td>";

            // Severity
            print '<td>';
            print $objp->severity_label;
            print "</td>";
            print "</tr>";
            $i++;
        }

        $db->free();
    } else {
        print '<tr><td colspan="5"><div class="info">' . $langs->trans('NoTicketsFound') . '</div></td></tr>';
    }

    print "</tbody>
	</table>";
} else {
    dol_print_error($db);
}

print '<script type="text/javascript">
$(document).ready(function() {
$(\'#derniers\').DataTable({
	"columnDefs": [
            {
                "targets": [ 1 ],
                "visible": false,
                "searchable": false
            }
        ],
	"iDisplayLength": 30,
	"order": [[1,"desc"]]
	});
} );
</script>';



print '</div></div>';
print '<div style="clear:both"></div>';

print '<div class="tabsAction">';
//print '<div class="inline-block divButAction"><a class="butAction" href="new.php?action=create_ticket">' . $langs->trans('CreateTicket') . '</a></div>';
//print '<div class="inline-block divButAction"><a class="butAction" href="list.php">' . $langs->trans('TicketList') . '</a></div>';
print '</div>';



// End of page
llxFooter('');
$db->close();
