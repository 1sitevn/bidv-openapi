<?php

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/



namespace OneSite\BIDV\Tests;

use PHPUnit\Framework\TestCase;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A128GCM;
use Jose\Component\Encryption\Algorithm\KeyEncryption\A256KW;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Serializer\JSONGeneralSerializer;
use Jose\Component\Signature\JWSVerifier;
use Base64Url\Base64Url;
use Carbon\Carbon;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

/**
 * ShopVATest
 */
class ShopVATest extends TestCase
{
    private $client;
    private $apiUrl;
    private $apiCreateShopVAUrl;
    private $serviceId;
    private $merchantId;
    private $merchantName;
    private $channelId;
    private $privateKey;
    private $certificateKey;
    private $symmatricKey;
    private $providerId;

    /**
     * testExample
     * vendor/bin/phpunit --filter testExample tests/ShopVATest.php
     * @return void
     */
    public function testExample()
    {
        $requestId = uniqid();

        $this->client = new Client();

        $this->serviceId = config('bidv.open_api.service_id');
        $this->merchantId = config('bidv.open_api.merchant_id');
        $this->channelId = config('bidv.open_api.channel_id');
        $this->merchantName = config('bidv.open_api.merchant_name');
        $this->privateKey = config('bidv.open_api.private_key'); // https://en.rakko.tools/tools/46/
        $this->certificateKey = config('bidv.open_api.certificate_key');
        $this->symmatricKey = config('bidv.open_api.symmatric_key'); // https://www.browserling.com/tools/random-hex
        $this->providerId = config('bidv.virtual_account.provider_id');

        $this->apiUrl = config('bidv.open_api.url');
        $this->apiCreateShopVAUrl = $this->apiUrl . 'open-banking/paygate/createVAQLBH/v1';     

        $params = [
            'serviceId' => $this->serviceId,
            'merchantType' => 1,
            'channelId' => $this->channelId,
            'merchantId' => $this->merchantId,
            'merchantName' => $this->merchantName,
            'accountNo' => '1207250341',
            'accountName' => 'QJEP VJO DJEY HOEPH',
            'identity' => '001198001552',
            'mobile' => '0902539889',
            'tranDate' => '240907'
        ];
        $dataEncrypt = $this->encryptTransferInfo($params);

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $this->getAccessToken(),
            'User-Agent' => $this->merchantName,
            'Channel' => $this->merchantName,
            'X-Client-Certificate' => $this->decryptClientCertificate(),
            'Timestamp' => Carbon::now()->format('Y-m-d\TH:i:s.v\Z'),
            'X-API-Interaction-ID' => $requestId,
            'X-JWS-Signature' => $dataEncrypt['signature'],
        ];

        var_dump($params, $dataEncrypt, $headers, $this->apiCreateShopVAUrl);

        $response = $this->client->request('POST', $this->apiCreateShopVAUrl, [
            'http_errors' => false,
            'verify' => false,
            'headers' => $headers,
            'body' => json_encode($dataEncrypt['rawData'])
        ]);

        var_dump($response);
    }

    private function getAccessToken(){
        return config('bidv.open_api.access_token');
    }
    private function getKeyContentFromFile($fileKeyPath)
    {
        return file_get_contents($fileKeyPath);
    }
    private function decryptClientCertificate()
    {
        return trim(str_replace(
            ["-----BEGIN CERTIFICATE-----", "-----END CERTIFICATE-----", "\n", "\r", " "],
            "",
            $this->getKeyContentFromFile($this->certificateKey)
        ));
    }

    private function encryptTransferInfo($payload)
    {
        $vAes = trim(str_replace(["\n", "\r", " "], "", $this->getKeyContentFromFile($this->symmatricKey)));
        $rawData = $this->enCryptData($payload, $vAes);
        return ['signature' => $this->sign($rawData), 'rawData' => $rawData];
    }

    private function enCryptData($payload, $vAes)
    {
        //try {
            $plaintext = json_encode($payload);
            $keyBytes = $this->hexToBytes($vAes);
            $k = Base64Url::encode($keyBytes);
            $jwk = new JWK([
                'kty' => 'oct',
                'k' => $k,
            ]);
            $keyEncryptionAlgorithmManager = new AlgorithmManager([
                new A256KW(),
            ]);
            $contentEncryptionAlgorithmManager = new AlgorithmManager([
                new A128GCM(),
            ]);
            $compressionMethodManager = new CompressionMethodManager([]);
            $jweBuilder = new JWEBuilder(
                $keyEncryptionAlgorithmManager,
                $contentEncryptionAlgorithmManager,
                $compressionMethodManager
            );
            $jwe = $jweBuilder
                ->create()
                ->withPayload($plaintext)
                ->withSharedProtectedHeader([
                    'enc' => 'A128GCM',
                    'alg' => 'A256KW',
                    'typ' => 'JWE',
                    'iat' => time(),
                ])
                ->addRecipient($jwk)
                ->build();
            $serializer = new JSONGeneralSerializer();
            $jsonJwe = $serializer->serialize($jwe);
            $generalJwe = json_decode($jsonJwe, true);
            return [
                "recipients" => [
                    [
                        "header" => [],
                        "encrypted_key" => $generalJwe['recipients'][0]['encrypted_key']
                    ]
                ],
                "protected" => $generalJwe['protected'],
                "ciphertext" => $generalJwe['ciphertext'],
                "iv" => $generalJwe['iv'],
                "tag" => $generalJwe['tag']
            ];
        //} catch (\Exception $exception) {
        //    
        //}
    }

    private function hexToBytes($hex)
    {
        $bytes = [];
        for ($i = 0; $i < strlen($hex); $i += 2) {
            $bytes[] = hexdec(substr($hex, $i, 2));
        }
        return implode(array_map("chr", $bytes));
    }

    private function sign($payload, $alg = 'RS256')
    {
        try {
            $algorithmManager = new AlgorithmManager([
                new RS256(),
            ]);
            $privateKeyPem = $this->getKeyContentFromFile($this->privateKey);
            $jwk = JWKFactory::createFromKey($privateKeyPem);
            $jwsBuilder = new JWSBuilder($algorithmManager);
            $jws = $jwsBuilder
                ->create()
                ->withPayload(json_encode($payload))
                ->addSignature($jwk, ['alg' => $alg])
                ->build();
            $serializer = new CompactSerializer();
            return $serializer->serialize($jws, 0);
        } catch (\Exception $exception) {
           
        }
    }

    private function verify($jws)
    {
        if (empty($jws)) {
            return false;
        }
        $certificateKey = $this->getKeyContentFromFile($this->certificateKey);
        $algorithmManager = new AlgorithmManager([
            new RS256(),
        ]);
        $jwsVerifier = new JWSVerifier($algorithmManager);
        $jwk = JWKFactory::createFromKey($certificateKey);
        $serializer = new CompactSerializer();
        $jwsObject = $serializer->unserialize($jws);
        $isVerified = $jwsVerifier->verifyWithKey($jwsObject, $jwk, 0);
        if ($isVerified) {
            return true;
        } else {
            return false;
        }
    }    
}
