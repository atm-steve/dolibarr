<?php

namespace PaneeDesign\PhpFirebaseCloudMessaging\Tests;

use PaneeDesign\PhpFirebaseCloudMessaging\Client;
use PaneeDesign\PhpFirebaseCloudMessaging\Recipient\Topic;
use PaneeDesign\PhpFirebaseCloudMessaging\Message;

use GuzzleHttp\Psr7\Response;

/**
 * @author Fabiano Roberto <fabiano@paneedesign.com>
 */
class ClientTest extends PhpFirebaseCloudMessagingTestCase
{
    /**
     * @var Client
     */
    private $fixture;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new Client();
    }

    public function testSendConstruesValidJsonForNotificationWithTopic()
    {
        $apiKey = 'key';
        $headers = array(
            'Authorization' => sprintf('key=%s', $apiKey),
            'Content-Type' => 'application/json',
        );

        /* @var \Mockery\Mock|\GuzzleHttp\Client $guzzle */
        $guzzle = \Mockery::mock(\GuzzleHttp\Client::class);
        $guzzle->shouldReceive('post')
            ->once()
            ->with(Client::DEFAULT_API_URL, array('headers' => $headers, 'body' => '{"to":"\\/topics\\/test"}'))
            ->andReturn(\Mockery::mock(Response::class));

        $this->fixture->injectGuzzleHttpClient($guzzle);
        $this->fixture->setApiKey($apiKey);

        $message = new Message();
        $message->addRecipient(new Topic('test'));

        $this->fixture->send($message);
    }
}