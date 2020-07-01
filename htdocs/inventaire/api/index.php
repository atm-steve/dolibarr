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

if (!defined("NOLOGIN")) {
    define("NOLOGIN", '1');
}

if (!defined('NOREQUIREUSER')) {
    define('NOREQUIREUSER', '1');
}

if (!defined('NOTOKENRENEWAL')) {
    define('NOTOKENRENEWAL', '1');
}

if (!defined('NOREQUIREMENU')) {
    define('NOREQUIREMENU', '1');
}

if (!defined('NOREQUIREHTML')) {
    define('NOREQUIREHTML', '1');
}

if (!defined('NOREQUIREAJAX')) {
    define('NOREQUIREAJAX', '1');
}

if (!defined('NOREQUIRESOC')) {
    define('NOREQUIRESOC', '1');
}

if (!defined('NOCSRFCHECK')) {
    define('NOCSRFCHECK', '1');
}

if (!defined('INVENTAIRE_API')) {
    define('INVENTAIRE_API', '1');
}

$errorCodes = [
    'noToken'          => 100,
    'noUser'           => 200,
    'login'            => 300,
    'missingArguments' => 400,
];

require '../config.php';
$langs->setDefaultLang('fr');
$langs->load('inventaire@inventaire');
require INVENTAIRE_PATH_ROOT . '/class/InventoryUser.php';

$action = GETPOST('action');

if ($action == 'login') {
    return sendOutput(include './actions/login.php');
}

$header = getallheaders();
if (isset($header['Authorization'])) {
    $userToken = $header['Authorization'];
}
if (isset($header['authorization'])) {
    $userToken = $header['authorization'];
}
$userToken = str_replace('Bearer ', '', $userToken);
if (empty($userToken)) {
    header("HTTP/1.1 401 Unauthorized");
    sendOutput(['error' => 'Unauthorized', 'code' => $errorCodes['noToken']]);
    return;
}
$jwt        = new Emarref\Jwt\Jwt();
$algorithm  = new Emarref\Jwt\Algorithm\Hs256($dolibarr_main_cookie_cryptkey);
$encryption = Emarref\Jwt\Encryption\Factory::create($algorithm);

$token   = $jwt->deserialize($userToken);
$context = new Emarref\Jwt\Verification\Context($encryption);
$context->setIssuer(INVENTAIRE_URL_ROOT);
try {
    $jwt->verify($token, $context);
} catch (Emarref\Jwt\Exception\VerificationException $e) {
    header("HTTP/1.1 401 Unauthorized");
    sendOutput(['error' => $e->getMessage()]);
    return;
}

$payload = $token->getPayload();
$uuid    = $payload->findClaimByName('uuid')->getValue();
$rowid   = $payload->findClaimByName('jti')->getValue();
$iUser   = (new InventoryUser)->where(compact('rowid', 'uuid'))->first();
if (!$iUser) {
    header("HTTP/1.1 401 Unauthorized");
    sendOutput(['error' => 'Unauthorized']);
    return;
}

switch ($action) {
    case 'userToken':
        $token        = GETPOST('token');
        $iUser->regid = $token;
        $iUser->save();
        return;
        break;
    case 'barcode':
        return sendOutput(include './actions/barcode.php');
        break;
    case 'getProducts':
        return sendOutput(include './actions/getProducts.php');
        break;
    case 'getZones':
        return sendOutput(include './actions/getZones.php');
        break;
    case 'insertInZone':
        return sendOutput(include './actions/insertInZone.php');
        break;
    case 'valideZone':
        return sendOutput(include './actions/valideZone.php');
        break;
    case 'getZoneInfo':
        include_once INVENTAIRE_PATH_ROOT . '/class/Zone.php';
        $zone              = (new Zone)->with('products.product')->find(GETPOST('zone'));
        if (!$zone) {
           return sendOutput(['error' => 'not found']);
        }
        $zone->statut_code = $zone->statut;
        $zone->statut      = html_entity_decode(Zone::getStatutLabel($zone->statut, 1, false));
        sendOutput(compact('zone'));
        break;
    default:
        # code...
        break;
}

function sendOutput($res)
{
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    header('Content-Type: application/json;charset=UTF-8');
    $json = json_encode($res);
    if ($json) {
        print $json;
        return;
    }
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = ' - Aucune erreur';
            break;
        case JSON_ERROR_DEPTH:
            $error = ' - Profondeur maximale atteinte';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = ' - Inadéquation des modes ou underflow';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = ' - Erreur lors du contrôle des caractères';
            break;
        case JSON_ERROR_SYNTAX:
            $error = ' - Erreur de syntaxe ; JSON malformé';
            break;
        case JSON_ERROR_UTF8:
            $error = " - Caractères UTF-8 malformés, probablement une erreur d'encodage";
            break;
        default:
            $error = ' - Erreur inconnue';
            break;
    }
    echo json_encode(array("error" => $error));

}
