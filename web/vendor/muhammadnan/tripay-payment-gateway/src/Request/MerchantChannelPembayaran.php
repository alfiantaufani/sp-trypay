<?php namespace Tripay\Request;

use GuzzleHttp\Client;
use Tripay\Methods\RequestInterface;

/**
 * Class MerchantChannelPembayaran
 * @package Tripay\Request
 *
 * @author Muhammad Adnan <naravaya04@gmail.com>
 */
class MerchantChannelPembayaran implements RequestInterface
{
    /**
     * @var
     */
    private $mode;
    private $apiKey;
    public $code;
    public const URL_SANDBOX = 'https://tripay.co.id/api-sandbox/merchant/payment-channel?';
    public const URL_PRODUCTION = 'https://tripay.co.id/api/merchant/payment-channel?';

    /**
     * @var array
     */
    public $response;
    public $statusCode;
    public $json;

    /**
     * MerchantChannelPembayaran constructor.
     * @param null $code
     * @param $apiKey
     * @param $mode
     */
    public function __construct($code = null, $apiKey, $mode) {
        $this->mode = $mode;
        $this->apiKey = $apiKey;
        $this->code = $code;
    }

    /**
     * @return object
     */
    public function getMerchantChannelPembayaran()
    {
        if ($this->mode === 'live') {

            return $this->getRequest(self::URL_PRODUCTION);

        } else if ($this->mode === 'sandbox') {

            return $this->getRequest(self::URL_SANDBOX);

        }
    }

    /**
     * @param $url
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getRequest(string $url) : object {
        $client = new Client();
        $res = $client->request('GET', $url.http_build_query(['code' => $this->code]), [
            'headers' => [
                "Authorization" => 'Bearer '.$this->apiKey
            ]
        ]);
        
        return $res;
    }

    /**
     * @return object
     */
    public function getResponse()
    {
        return $this->getMerchantChannelPembayaran()->getBody()->getContents();
    }

    /**
     * @return int
     */
    public function getStatusCode() : int
    {
        return $this->getMerchantChannelPembayaran()->getStatusCode();
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getJson()
    {
        return json_decode($this->getMerchantChannelPembayaran()->getBody()->getContents());
    }

    /**
     * @return bool
     */
    public function getSuccess() : bool
    {
        return $this->getJson()->success;
    }

    /**
     * @return object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getData()
    {
        if ( isset($this->getJson()->data) ) {
            return $this->getJson()->data;
        }

        return $this->getResponse();
    }

}