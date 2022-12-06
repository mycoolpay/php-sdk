<?php

namespace MyCoolPay;

use MyCoolPay\Exception\BadSignatureException;
use MyCoolPay\Exception\KeyMismatchException;
use MyCoolPay\Exception\UnknownIpException;
use MyCoolPay\Http\Exception\HttpException;
use MyCoolPay\Http\Response;
use MyCoolPay\Http\RestClient;
use MyCoolPay\Logging\LoggerInterface;

class MyCoolPayClient extends RestClient
{
    const USER_AGENT = "MyCoolPay/PHP/MyCoolPayClient";

    /**
     * @var string $publicKey
     */
    protected $publicKey;
    /**
     * @var string $privateKey
     */
    protected $privateKey;

    /**
     * @param string $public_key
     * @param string $private_key
     * @param LoggerInterface|null $logger
     * @param bool $debug
     */
    public function __construct($public_key, $private_key, $logger = null, $debug = false)
    {
        parent::__construct("https://my-coolpay.com/api/$public_key", $logger, $debug);

        $this->publicKey = $public_key;
        $this->privateKey = $private_key;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param string $public_key
     * @return $this
     */
    public function setPublicKey($public_key)
    {
        $this->publicKey = $public_key;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param string $private_key
     * @return $this
     */
    public function setPrivateKey($private_key)
    {
        $this->privateKey = $private_key;
        return $this;
    }

    /**
     * @param array $data
     * @return Response
     * @throws HttpException
     */
    public function paylink($data)
    {
        return $this->post("/paylink", $data);
    }

    /**
     * @param array $data
     * @return Response
     * @throws HttpException
     */
    public function payin($data)
    {
        return $this->post("/payin", $data);
    }

    /**
     * @param array $data
     * @return Response
     * @throws HttpException
     */
    public function authorizePayin($data)
    {
        return $this->post("/payin/authorize", $data);
    }

    /**
     * @param array $data
     * @return Response
     * @throws HttpException
     */
    public function payout($data)
    {
        return $this->post("/payout", $data, ["X-PRIVATE-KEY" => $this->privateKey]);
    }

    /**
     * @param string $transaction_ref
     * @return Response
     * @throws HttpException
     */
    public function checkStatus($transaction_ref)
    {
        return $this->get("/checkStatus/$transaction_ref");
    }

    /**
     * @return Response
     * @throws HttpException
     */
    public function getBalance()
    {
        return $this->get("/balance", [], ["X-PRIVATE-KEY" => $this->privateKey]);
    }

    /**
     * @param array $callback_data
     * @return true
     * @throws KeyMismatchException
     * @throws BadSignatureException
     */
    public function checkCallbackIntegrity($callback_data)
    {
        if (!(is_array($callback_data)
            && isset($callback_data["application"])
            && $callback_data["application"] === $this->publicKey)
        )
            throw new KeyMismatchException();

        $ref = isset($callback_data["transaction_ref"]) ? $callback_data["transaction_ref"] : null;
        $type = isset($callback_data["transaction_type"]) ? $callback_data["transaction_type"] : null;
        $amount = isset($callback_data["transaction_amount"]) ? $callback_data["transaction_amount"] : null;
        $currency = isset($callback_data["transaction_currency"]) ? $callback_data["transaction_currency"] : null;
        $operator = isset($callback_data["transaction_operator"]) ? $callback_data["transaction_operator"] : null;

        $signature = md5($ref . $type . $amount . $currency . $operator . $this->privateKey);

        if (!(isset($callback_data["signature"]) && $callback_data["signature"] === $signature))
            throw new BadSignatureException();

        return true;
    }

    /**
     * @param string $ip
     * @return true
     * @throws UnknownIpException
     */
    public function isVerifiedIp($ip)
    {
        if (!(preg_match('/^15\./', $ip) && md5($ip) === "236b8184a174448a978e10a93480604a"))
            throw new UnknownIpException();

        return true;
    }
}
