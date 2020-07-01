<?php

/*
 * Copyright (C) 2017-2018  <dev2a> contact@dev2a.pro
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

$res = 0;
if (!$res && file_exists("../main.inc.php")) {
    $res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = @include "../../../main.inc.php";
}
if (!$res && file_exists("../../../../main.inc.php")) {
    $res = @include "../../../../main.inc.php";
}
if (!$res) {
    die("Include of main fails");
}
if (!defined('INVENTAIRE_URL_ROOT')) {
    define('INVENTAIRE_URL_ROOT', dol_buildpath('inventaire', 2));
}
if (!defined('INVENTAIRE_PATH_ROOT')) {
    define('INVENTAIRE_PATH_ROOT', dol_buildpath('inventaire', 0));
}
$conf->global->MAIN_USE_JQUERY_JEDITABLE = 1;

require INVENTAIRE_PATH_ROOT . '/lib/inventaire.lib.php';
require INVENTAIRE_PATH_ROOT . '/includes/autoload.php';
if (!defined('INVENTAIRE_API')) {
    $cacheFolder = $conf->inventaire->dir_output . '/cache';
    if (!file_exists($cacheFolder)) {
        @mkdir($cacheFolder);
    }

    $loader = new Twig_Loader_Filesystem(INVENTAIRE_PATH_ROOT . '/views');
    $view   = new Twig_Environment(
        $loader,
        array(
            'cache'            => $cacheFolder,
            'strict_variables' => true,
            'debug'            => true,
        )
    );

    $view->addGlobal('form', new Form($db));
    $view->addGlobal('bc', $bc);
    $view->addGlobal('conf', $conf);
    $view->addFunction(new Twig_SimpleFunction('displayErrors', 'twigDisplayErrors', array('is_safe' => array('html'))));
    $view->addFunction(new Twig_SimpleFunction('trans', 'trans', array('is_safe' => array('html'))));
    $view->addFunction(new Twig_SimpleFunction('getConstant', 'twigConstant'));
    $view->registerUndefinedFunctionCallback(
        function ($name) {
            return function_exists($name) ? new Twig_SimpleFunction($name, $name) : false;
        }
    );
}
$capsule = new Illuminate\Database\Capsule\Manager;
$driver  = $dolibarr_main_db_type === 'mysqli' ? 'mysql' : $dolibarr_main_db_type;
$capsule->addConnection(
    [
        'driver'    => $driver,
        'host'      => $dolibarr_main_db_host,
        'port'      => $dolibarr_main_db_port,
        'database'  => $dolibarr_main_db_name,
        'username'  => $dolibarr_main_db_user,
        'password'  => $dolibarr_main_db_pass,
        'prefix'    => $dolibarr_main_db_prefix,
        'charset'   => $dolibarr_main_db_character_set,
        'collation' => $dolibarr_main_db_collation,
    ]
);
$capsule->bootEloquent();
$capsule->setAsGlobal();
$validator = new Validation\Validator($capsule->getDatabaseManager(), $langs);
$entrepotLabel = 'entrepot.label';
// Compat 7.0
if (version_compare(DOL_VERSION, '7.0.0', '>=')) {
    $entrepotLabel= 'entrepot.ref';
}
