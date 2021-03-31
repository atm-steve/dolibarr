<?php
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2014 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2010 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2011-2016 Alexandre Spangaro   <aspangaro@open-dsi.fr>
 * Copyright (C) 2011-2014 Juanjo Menent	    <jmenent@2byte.es>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 * Copyright (C) 2021		Gauthier VERDOL         <gauthier.verdol@atm-consulting.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *      \file       htdocs/compta/sociales/payments.php
 *      \ingroup    compta
 *		\brief      Page to list payments of special expenses
 */

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/salaries/class/salary.class.php';
require_once DOL_DOCUMENT_ROOT.'/salaries/class/paymentsalary.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';

// Load translation files required by the page
$langs->loadLangs(array('compta', 'bills', 'salaries'));

// Security check
if ($user->socid) $socid = $user->socid;
$result = restrictedArea($user, 'tax|salaries', '', '', 'charges|');

$mode = GETPOST("mode", 'alpha');
$year = GETPOST("year", 'int');
$filtre = GETPOST("filtre", 'alpha');
if (!$year && $mode != 'sconly') { $year = date("Y", time()); }
$search_user = GETPOST("search_user", 'int');

$limit = GETPOST('limit', 'int') ?GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page == -1) { $page = 0; }     // If $page is not defined, or '' or -1
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortfield) $sortfield = "pc.datep";
if (!$sortorder) $sortorder = "DESC";


/*
 * View
 */

$payment_salary_static = new PaymentSalary($db);
$sal_static = new Salary($db);
$userstatic = new User($db);
$accountstatic = new Account($db);
$accountlinestatic = new AccountLine($db);

llxHeader('', $langs->trans("SalariesArea"));

$title = $langs->trans("SalariesPayments");
if (!empty($search_user)) {
	$u = new user($db);
	$u->fetch($search_user);
	$title = $langs->trans("SalariesPaymentsOf", $u->getNomUrl());
}

$param = '';
if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param .= '&contextpage='.$contextpage;
if ($limit > 0 && $limit != $conf->liste_limit) $param .= '&limit='.$limit;
if ($mode == 'sconly') $param = '&mode=sconly';
if ($sortfield) $param .= '&sortfield='.$sortfield;
if ($sortorder) $param .= '&sortorder='.$sortorder;


print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="page" value="'.$page.'">';
print '<input type="hidden" name="mode" value="'.$mode.'">';

print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $center, $num, $totalnboflines, 'title_accountancy', 0, '', '', $limit);

if ($year) $param .= '&year='.$year;

// Localtax
if ($mysoc->localtax1_assuj == "1" && $mysoc->localtax2_assuj == "1")
{
	$j = 1;
	$numlt = 3;
}
elseif ($mysoc->localtax1_assuj == "1")
{
	$j = 1;
	$numlt = 2;
}
elseif ($mysoc->localtax2_assuj == "1")
{
	$j = 2;
	$numlt = 3;
}
else
{
	$j = 0;
	$numlt = 0;
}

// Payment Salary
if (!empty($conf->salaries->enabled) && !empty($user->rights->salaries->read))
{
    if (!$mode || $mode != 'sconly')
    {
        $sal = new Salary($db);

        $sql = "SELECT ps.rowid as payment_id, ps.amount, s.rowid as salary_id, s.label, ps.datep as datep, s.datesp, s.dateep, s.amount as salary, u.salary as current_salary, pct.code as payment_code,";
        $sql .= " u.rowid as uid, u.lastname, u.firstname, u.login, u.email, u.admin, u.salary as current_salary, u.fk_soc as fk_soc, u.statut as status,";
		$sql .= " ba.rowid as bid, ba.ref as bref, ba.number as bnumber, ba.account_number, ba.fk_accountancy_journal, ba.label as blabel, ba.iban_prefix as iban, ba.bic, ba.currency_code, ba.clos,";
		$sql .= " pct.code as payment_code, ps.num_payment, ps.fk_bank";
        $sql .= " FROM ".MAIN_DB_PREFIX."payment_salary as ps";
		$sql .= " INNER JOIN ".MAIN_DB_PREFIX."salary as s ON (s.rowid = ps.fk_salary)";
		$sql .= " INNER JOIN ".MAIN_DB_PREFIX."user as u ON (u.rowid = s.fk_user)";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_paiement as pct ON ps.fk_typepayment = pct.id";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."bank as b ON ps.fk_bank = b.rowid";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."bank_account as ba ON b.fk_account = ba.rowid";
        $sql .= " WHERE s.entity IN (".getEntity('user').")";
	if(!empty($search_user)) $sql .= " AND u.rowid = ".$search_user;
       /* if ($year > 0)
        {
            $sql .= " AND (s.datesp between '".$db->idate(dol_get_first_day($year, 1, false))."' AND '".$db->idate(dol_get_last_day($year, 12, false))."'";
            $sql .= " OR s.dateep between '".$db->idate(dol_get_first_day($year, 1, false))."' AND '".$db->idate(dol_get_last_day($year, 12, false))."')";
        }*/
        if (preg_match('/^s\./', $sortfield)
			|| preg_match('/^pct\./', $sortfield)
			|| preg_match('/^ps\./', $sortfield)
			|| preg_match('/^ba\./', $sortfield)) $sql .= $db->order($sortfield, $sortorder);

        $result = $db->query($sql);
        if ($result)
        {
            $num = $db->num_rows($result);
            $i = 0;
            $total = 0;
            print '<table class="noborder centpercent">';
            print '<tr class="liste_titre">';
			print_liste_field_titre("RefPayment", $_SERVER["PHP_SELF"], "ps.rowid", "", $param, '', $sortfield, $sortorder);
			print_liste_field_titre("Salary", $_SERVER["PHP_SELF"], "s.rowid", "", $param, '', $sortfield, $sortorder);
			print_liste_field_titre("Label", $_SERVER["PHP_SELF"], "s.label", "", $param, '', $sortfield, $sortorder);
			print_liste_field_titre("DateEnd", $_SERVER["PHP_SELF"], "s.dateep", "", $param, 'width="140px"', $sortfield, $sortorder);
			print_liste_field_titre("DatePayment", $_SERVER["PHP_SELF"], "ps.datep", "", $param, 'align="center"', $sortfield, $sortorder);
			print_liste_field_titre("Employee", $_SERVER["PHP_SELF"], "s.fk_user", "", $param, "", $sortfield, $sortorder);
			print_liste_field_titre("PaymentMode", $_SERVER["PHP_SELF"], "pct.code", "", $param, '', $sortfield, $sortorder);
			print_liste_field_titre("Numero", $_SERVER["PHP_SELF"], "ps.num_payment", "", $param, '', $sortfield, $sortorder, '', 'ChequeOrTransferNumber');
			if (!empty($conf->banque->enabled)) {
				print_liste_field_titre("BankTransactionLine", $_SERVER["PHP_SELF"], "ps.fk_bank", "", $param, '', $sortfield, $sortorder);
				print_liste_field_titre("BankAccount", $_SERVER["PHP_SELF"], "ba.label", "", $param, "", $sortfield, $sortorder);
			}
            print_liste_field_titre("ExpectedToPay", $_SERVER["PHP_SELF"], "s.amount", "", $param, 'class="right"', $sortfield, $sortorder);
            print_liste_field_titre("PayedByThisPayment", $_SERVER["PHP_SELF"], "ps.amount", "", $param, 'class="right"', $sortfield, $sortorder);
            print "</tr>\n";

            while ($i < $num)
            {
                $obj = $db->fetch_object($result);

                $total = $total + $obj->amount;

                print '<tr class="oddeven">';

				// Ref payment
				$payment_salary_static->id = $obj->payment_id;
				$payment_salary_static->ref = $obj->payment_id;
				print '<td class="left">'.$payment_salary_static->getNomUrl(1)."</td>\n";

				// Salary
				print '<td>';
				$sal_static->id = $obj->salary_id;
				$sal_static->ref = $obj->salary_id;
				$sal_static->label = $obj->label;
				print $sal_static->getNomUrl(1, '20');
				print '</td>';

				// Salary label
				print "<td>".$obj->label."</td>\n";

				// Date fin salaire
				print '<td class="left">'.dol_print_date($db->jdate($obj->dateep), 'day').'</td>'."\n";

				// Date paiement
				print '<td class="center">'.dol_print_date($db->jdate($obj->datep), 'day')."</td>\n";

				// Employee
				$userstatic->id = $obj->uid;
				$userstatic->lastname = $obj->lastname;
				$userstatic->firstname = $obj->firstname;
				$userstatic->admin = $obj->admin;
				$userstatic->login = $obj->login;
				$userstatic->email = $obj->email;
				$userstatic->socid = $obj->fk_soc;
				$userstatic->statut = $obj->status;
				print "<td>".$userstatic->getNomUrl(1)."</td>\n";

				// Type payment
				print '<td>';
				if ($obj->payment_code) print $langs->trans("PaymentTypeShort".$obj->payment_code).' ';
				print '</td>';

				// Chq number
				print '<td>'.$obj->num_payment.'</td>';

				// Account
				if (!empty($conf->banque->enabled)) {
					// Bank transaction
					print '<td>';
					$accountlinestatic->rowid = $obj->fk_bank;
					print $accountlinestatic->getNomUrl(1);
					print '</td>';

					print '<td>';
					if ($obj->fk_bank > 0) {
						//$accountstatic->fetch($obj->fk_bank);
						$accountstatic->id = $obj->bid;
						$accountstatic->ref = $obj->bref;
						$accountstatic->number = $obj->bnumber;
						$accountstatic->iban = $obj->iban;
						$accountstatic->bic = $obj->bic;
						$accountstatic->currency_code = $langs->trans("Currency".$obj->currency_code);
						$accountstatic->clos = $obj->clos;

						if (!empty($conf->accounting->enabled)) {
							$accountstatic->account_number = $obj->account_number;

							$accountingjournal = new AccountingJournal($db);
							$accountingjournal->fetch($obj->fk_accountancy_journal);

							$accountstatic->accountancy_journal = $accountingjournal->getNomUrl(0, 1, 1, '', 1);
						}
						$accountstatic->label = $obj->blabel;
						if($accountstatic->id > 0) print $accountstatic->getNomUrl(1);
					} else print '&nbsp;';
					print '</td>';
				}

				// Date début salaire
				//print '<td class="left">'.dol_print_date($db->jdate($obj->datesp), 'day').'</td>'."\n";

                print '<td class="right">'.($obj->salary ?price($obj->salary) : '')."</td>";
                print '<td class="right">'.price($obj->amount)."</td>";
                print "</tr>\n";

                $i++;
            }
            print '<tr class="liste_total"><td colspan="2">'.$langs->trans("Total").'</td>';
            print '<td class="right"></td>'; // A total here has no sense
			print '<td align="center">&nbsp;</td>';
			print '<td align="center">&nbsp;</td>';
			print '<td align="center">&nbsp;</td>';
			print '<td align="center">&nbsp;</td>';
			print '<td align="center">&nbsp;</td>';
			if (!empty($conf->banque->enabled)) {
				print '<td align="center">&nbsp;</td>';
				print '<td align="center">&nbsp;</td>';
			}
            print '<td align="center">&nbsp;</td>';
            print '<td class="right">'.price($total)."</td>";
            print "</tr>";

            print "</table>";
            $db->free($result);

            print "<br>";
        }
        else
        {
            dol_print_error($db);
        }
    }
}

print '</form>';

// End of page
llxFooter();
$db->close();
