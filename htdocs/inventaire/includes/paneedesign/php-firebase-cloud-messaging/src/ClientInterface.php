<?php
namespace PaneeDesign\PhpFirebaseCloudMessaging;

use GuzzleHttp;

/**
 * @author Fabiano Roberto <fabiano@paneedesign.com>
 */
interface ClientInterface
{
    /**
     * Inject GuzzleHttp Client
     * @param GuzzleHttp\ClientInterface $client
     */
    public function injectGuzzleHttpClient(GuzzleHttp\ClientInterface $client);
    
    /**
     * Add your server api key here
     * read how to obtain an api key here: https://firebase.google.com/docs/server/setup#prerequisites
     *
     * @param string $apiKey
     *
     * @return Client
     */
    function setApiKey($apiKey);
    

    /**
     * People can overwrite the api url with a proxy server url of their own
     *
     * @param string $url
     *
     * @return Client
     */
    function setProxyApiUrl($url);

    /**
     * sends your notification to the google servers and returns a guzzle response object
     * containing their answer.
     *
     * @param Message $message
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\RequestException
     */
    function send(Message $message);

    /**
     * @param integer $topic_id
     * @param array|string $recipientsTokens
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function addTopicSubscription($topic_id, $recipientsTokens);


    /**
     * @param integer $topic_id
     * @param array|string $recipientsTokens
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function removeTopicSubscription($topic_id, $recipientsTokens);
}
   