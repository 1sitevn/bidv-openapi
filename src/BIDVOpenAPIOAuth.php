<?php

namespace OneSite\BIDV;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

/**
 * BIDVOpenAPIOAuth
 */
class BIDVOpenAPIOAuth
{

    /**
     * @var Client
     */
    private $client;
        
    /**
     * apiUrl
     *
     * @var mixed
     */
    private $apiUrl;
    
    /**
     * clientId
     *
     * @var mixed
     */
    private $clientId;
    
    /**
     * secretId
     *
     * @var mixed
     */
    private $secretId;
        
    /**
     * apiAuthUrl
     *
     * @var mixed
     */
    private $apiAuthUrl;

    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client();

        $this->apiUrl = config('bidv.open_api.url');
        $this->clientId = config('bidv.open_api.client_id');
        $this->secretId = config('bidv.open_api.secret_id');

        $this->apiAuthUrl = $this->apiUrl . 'paygate-oauth/oauth2/token';        
    }
    
    /**
     * getAccessToken
     *
     * @return Response
     */
    public function getAccessToken(){
        $params = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->secretId,
            'scope' => 'ewallet',
        ];

        return $this->client->request('POST', $this->apiAuthUrl, [
            'http_errors' => false,
            'verify' => false,
            'headers' => [],
            'form_params' => $params
        ]);
    }
  
}