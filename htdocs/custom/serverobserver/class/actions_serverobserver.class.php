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
 * \file    class/actions_serverobserver.class.php
 * \ingroup serverobserver
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class ActionsserverObserver
 */
class ActionsserverObserver
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
	function formObjectOptions($parameters, &$object, &$action, $hookmanager)
	{
		
		if ($action==='view' && in_array('thirdpartycard', explode(':', $parameters['context'])) && !empty($object->array_options['options_serverobserverchecker']))
		{
			
			global $langs;
			
			$langs->load('serverobserver@serverobserver');
		  
		  	echo '<tr><td colspan="'.($parameters['colspanvalue']+1).'">
		  		<a href="'.dol_buildpath('/serverobserver/show.php?id='.$object->id, 1).'" target="_blank">'.$langs->trans('ServerObserverShowData').'</a>
				-		  			
		  		<a href="'.dirname($object->array_options['options_serverobserverchecker']).'/switch-user.php" target="_blank">'.$langs->trans('SwitchUserOnInstance').'</a>
		  	</td></td>';
		  
		}

		return 0;
	}
}