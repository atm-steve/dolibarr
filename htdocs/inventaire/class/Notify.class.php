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

use Illuminate\Support\Collection;
use PaneeDesign\PhpFirebaseCloudMessaging\Client;
use PaneeDesign\PhpFirebaseCloudMessaging\Message;
use PaneeDesign\PhpFirebaseCloudMessaging\Notification;
use PaneeDesign\PhpFirebaseCloudMessaging\Recipient\Device;

/**
 * Class to notify user
 */
class FCMNotify
{

    /**
     * FCM message
     * @var Message
     */

    private $message;

    /**
     * http client
     * @var Client
     */
    private $client;
/**
 * noticfication recipiant
 * @var array
 */
    private $recipients = [];

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApiKey('AAAA2v7hl4g:APA91bGhsVf2SxPF6g74Xwm-dszTDl25uz_JXrnxbSkxKlwZtZhP4a-KNXek7Laybex8yuCUpyiT2D_dTrRT4jguBJqiKr2hJMj98oSjVxGjefNsYC7SvKKG0NylK8n_zqSv2XsSrx_k');
        $this->client->injectGuzzleHttpClient(new \GuzzleHttp\Client());
        $this->message = new Message();
        $this->message->setPriority('high');
        $this->message->setContentAvailable(true);
        $this->data = new Collection([]);
    }
/**
 * destinataire
 * @param  InventoryUser|Collection  $sendTo users
 * @return FCMNotify
 */
    public function sendTo($sendTo)
    {
        if ($sendTo instanceof InventoryUser) {
            if (isset($sendTo->regid)) {
                $this->recipients[] = $sendTo->regid;

            }
            return $this;
        }
        if ($sendTo instanceof Collection) {
            foreach ($sendTo as $user) {
                if (isset($user->regid)) {
                    $this->recipients[] = $user->regid;
                }
            }
            return $this;
        }

        return $this;
    }

    /**
     * notification data
     * @param string|array $key
     * @param string $value null if $key is array
     * @return FCMNotify
     */
    public function withData($key, $value = null)
    {
        if (!is_array($key)) {
            $this->data->put($key, $value);
        }

        foreach ($key as $arrayKey => $arrayValue) {
            $this->data->put($arrayKey, $arrayValue);
        }
        return $this;
    }

    public function send()
    {
        if (count($this->recipients) == 0) {
            return;
        }
        foreach ($this->recipients as $key) {
            $this->message->addRecipient(new Device($key));
        }
        $this->message->setData($this->data->toArray());
        return $this->client->send($this->message);
    }
}
