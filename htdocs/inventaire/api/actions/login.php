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

$rowid = GETPOST('id');
$uuid  = GETPOST('uuid');

$iUser = (new InventoryUser)->where(compact('rowid', 'uuid'))->first();

if (!$iUser) {
    header("HTTP/1.1 401 Unauthorized");
    return ['error' => 'Unauthorized', 'code' => $errorCodes['login']];
}

$iUser->intern = isset($iUser->fk_user) && $iUser->fk_user > 0;

$token = new Emarref\Jwt\Token();
$token->addClaim(new Emarref\Jwt\Claim\Expiration(new \DateTime('1440 minutes')));
$token->addClaim(new Emarref\Jwt\Claim\IssuedAt(new \DateTime('now')));
$token->addClaim(new Emarref\Jwt\Claim\Issuer(INVENTAIRE_URL_ROOT));
$token->addClaim(new Emarref\Jwt\Claim\JwtId($rowid));
$token->addClaim(new Emarref\Jwt\Claim\NotBefore(new \DateTime('now')));
$token->addClaim(new Emarref\Jwt\Claim\PrivateClaim('uuid', $uuid));
$algorithm  = new Emarref\Jwt\Algorithm\Hs256($dolibarr_main_cookie_cryptkey);
$jwt        = new Emarref\Jwt\Jwt();
$encryption = Emarref\Jwt\Encryption\Factory::create($algorithm);
$token      = $jwt->serialize($token, $encryption);
return ['user' => $iUser->toArray(), 'token' => $token];
