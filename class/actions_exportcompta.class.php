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
 * \file    class/actions_workstation.class.php
 * \ingroup workstation
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class ActionsWorkstation
 */
class ActionsExportCompta
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

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager) {
	    
        if (in_array('invoicecard', explode(':', $parameters['context'])) || in_array('invoicesuppliercard', explode(':', $parameters['context'])))
        {
          global $conf;
		  $object->fetch_optionals($object->id);
		  if(!empty($conf->global->EXPORT_COMPTA_HIDE_GENERATE_FACTURE) && !empty($object->array_options['options_date_compta'])  ) {
          ?>
          <script language="javascript">
            $(document).ready(function() {
                $('table[summary=listofdocumentstable] tr.liste_titre').remove();
                $('table[summary=listofdocumentstable] tr td:last').remove();
            }  );
          </script>
          <?php
          }

 		  if(!empty($conf->global->EXPORT_COMPTA_HIDE_REOPEN_INVOICE) && !empty($object->array_options['options_date_compta'])) {
          		?>
          		<script language="javascript">
		            $(document).ready(function() {
		                $('.butAction').each(function(){
		                	href = $(this).attr('href');
		                	if(href.indexOf('action=reopen') > 0 || href.indexOf('action=modif') > 0 || href.indexOf('action=canceled') > 0){
		                		$(this).hide();
		                	}
		                })
		            }  );
		          </script>
          		<?php
		  }
          return 0;
        }
        
	} 
	 
	function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $langs,$conf;
		if($object->element == 'facture' && !empty($conf->global->EXPORT_COMPTA_HIDE_REOPEN_INVOICE) && !empty($object->array_options['options_date_compta'])) {
			if($action == 'reopen' || $action == 'modif' || $action == 'canceled'){
				$this->error = 1;
				$this->errors = $langs->trans('ErrorForbidden');
				$action = '';
				return -1;
			}
		}
	}
	
	function formObjectOptions($parameters, &$object, &$action, $hookmanager) {
		
		global $conf;
		
		if (in_array('invoicesuppliercard', explode(':', $parameters['context'])) && !empty($conf->global->EXPORTCOMPTA_DISABLE_UPDATE_DATE_FACFOURN_IF_COMPTABILISE) && !empty($object->array_options['options_date_compta'])) {
		
			?>
			
			<script language="javascript">
				$(document).ready(function() {
					var btn_edit_datef = $('[href*="action=editdatef"]');
					var link_edit_datef = btn_edit_datef.attr('href');
					
					if(typeof link_edit_datef != 'undefined') {
						$(btn_edit_datef).hide();
					}
				})
			</script>
			
			<?php
			
		}
			
	}
	
}
