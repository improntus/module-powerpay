<?php

namespace Improntus\PowerPay\Model\Rest;

use Magento\Framework\HTTP\Client\Curl;
use Improntus\PowerPay\Helper\Data;

class WebService
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var string
     */
    private $baseUrl;

    public function __construct(
        Curl $curl,
        Data $helper
    )
    {
        $this->helper = $helper;
        $this->curl = $curl;
        $this->baseUrl = 'https://api.powerpay.pe/api/';
    }

    public function doRequest($endpoint, $secret,  $data=null, $method=null, $storeId = null, $options=null)
    {
        if (null === $method) {
            $method = "POST";
        }

        if ($this->helper->getSandbox($storeId)) {
            $this->baseUrl = 'https://mo-services-bbva-bnpl-pe-beta.moprestamo.com/api/';
        }

        $url = $this->baseUrl . $endpoint;

        $basic = base64_encode($secret);

        try
        {
            if (!is_null($options)) {
                $this->curl->setOptions($options);
            }
            $this->curl->setHeaders(
                [
                    "Authorization" => $basic,
                    "Content-Type" => "application/json"
                ]
            );
            $data = json_encode($data);
            switch ($method)
            {
                case "POST":
                    $this->curl->post($url, $data);
                    break;
                case "GET":
                    $this->curl->get($url);
            }

            return json_decode($this->curl->getBody(), true);
        } catch (\Exception $e) {
            $this->helper->log($e->getMessage());
            return $e->getMessage();
        }
    }
}
