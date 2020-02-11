<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_supportatm.class.php
 * \ingroup supportatm
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class Actionssupportatm
 */
class Actionssupportatm
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	function printLeftBlock($parameters, &$object, &$action, $hookmanager) {
	    global $conf, $user, $db;

	    $cssprint=GETPOST('optioncss');

        if($user->rights->supportatm->read && empty($cssprint)) {
            // Infos d'ouverture
            $form = new Form($db);
            $infobulle = "du lundi au vendredi de 9h à 12h et de 14h à 17h, hors jours fériés";
            $supportopen = false;
            if(date('N') <= 5) {
                if (date('H') >= 9 && date('H') < 12 || date('H') >= 14 && date('H') < 17) {
                    $supportopen = true;
                }
            }
            $infopicto = ($supportopen ? 'info' : 'warning');

            $cur_url = $_SERVER['REQUEST_URI'];
            $ref_url = $_SERVER['HTTP_REFERER'];
            $info = "URL : " . $cur_url;
            $info.= "\nREF : " . $ref_url;
            $info .= "\nUtilisateur : " . $user->login;

            // Lien vers mantis
            $url_support = "http://support.atm-consulting.fr/bug_report_page.php";
            $url_support.= "?summary=" . urlencode("Saisissez une résumé simple de votre problème");
            $url_support.= "&description=" . urlencode("Donnez nous autant d'informations que possible, exemples, captures d'écran...");
            $url_support.= "&project_id=" . $conf->global->SUPPORTATM_PROJECTID;
            $url_support.= "&additional_info=" . urlencode($info);

            // Lien envoi e-mail
            $title = "[" . $conf->global->MAIN_INFO_SOCIETE_NOM . "] Demande d'assistance";
            $url_email = "mailto:support@atm-consulting.fr?subject=" . urlencode($title) . "&body=" . urlencode($info);

            ?>
            <div class="vmenu">
                <div id="blockvmenuatm" class="blockvmenubookmarks">
                    <div class="menu_titre">
                        <table class="nobordernopadding" summary="bookmarkstable" width="100%">
                            <tr>
                                <td><a class="vmenu" href="<?php echo $url_support; ?>" target="_blank">Assistance ATM</a></td>
                                <td align="right"><?php echo $form->textwithpicto("",$infobulle,1,$infopicto); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="menu_top"></div>
                    <div class="menu_contenu"><a class="vsmenu" href="<?php echo $url_support; ?>" target="_blank">Accès portail</a></div>
<!--
                    <div class="menu_contenu"><a class="vsmenu" href="<?php echo $url_email; ?>">E-mail</a></div>
                    <div class="menu_contenu"><a class="vsmenu" href="tel:+33977195069">Tel : 09 77 19 50 69</a></div>
-->
                    <div class="menu_end"></div>
                </div>
            </div>
            <?php
        }

        return 0;
    }
}
