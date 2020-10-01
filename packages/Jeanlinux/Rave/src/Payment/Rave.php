<?php

namespace Jeanlinux\Rave\Payment;

use Illuminate\Support\Facades\Log;
use Webkul\Payment\Payment\Payment;
use function core;

class Rave extends Payment
{

    /**
     *
     */
    const CONFIG_PUBLIC_KEY = 'sales.paymentmethods.rave.public_key';
    /**
     *
     */
    const CONFIG_SECRET_KEY = 'sales.paymentmethods.rave.secret_key';
    /**
     *
     */
    const CONFIG_ENVIRONMENT = 'sales.paymentmethods.rave.environment';
    /**
     *
     */
    const CONFIG_RAVE_TITLE = 'sales.paymentmethods.rave.store_title';
    /**
     *
     */
    const CONFIG_RAVE_LOGO = 'sales.paymentmethods.rave.logo';
    /**
     *
     */
    const CONFIG_PREFIX = 'sales.paymentmethods.rave.prefix';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $code  = 'rave';


    protected $publicKey;
    public $secretKey;
    protected $paymentMethod = 'both';
    protected $customLogo;
    protected $customTitle;
    protected $txref;
    protected $env;
    protected $transactionPrefix;
    protected $urls = [
        "live" => "https://api.ravepay.co",
        "test" => "https://ravesandboxapi.flutterwave.com",
    ];
    public $baseUrl;
    public $amount;
    public $currency;

    protected $overrideTransactionReference;




    /**
     * Rave constructor.
     */
    public function __construct()
    {
//        Log::debug("Public key is: " . core()->getConfigData(self::CONFIG_PUBLIC_KEY));
        $this->publicKey = core()->getConfigData(self::CONFIG_PUBLIC_KEY);
        $this->secretKey = core()->getConfigData(self::CONFIG_SECRET_KEY);

        /** @var Cart $cart */
        $cart = $this->getCart();
        if ($cart){
            $this->amount = $cart->grand_total;
//        $this->amount = 400;
            $this->currency = $cart->cart_currency_code;
//        $this->currency = "GHS";
//        $this->currency = $cart->customer_email;
        }


        $prefix = core()->getConfigData(self::CONFIG_PREFIX);
        $overrideRefWithPrefix = false;
        $this->customLogo = core()->getConfigData(self::CONFIG_RAVE_LOGO);
        $this->customTitle = core()->getConfigData(self::CONFIG_RAVE_TITLE);
        $this->env = core()->getConfigData(self::CONFIG_ENVIRONMENT);
        $this->transactionPrefix = $prefix.'_';
        $this->overrideTransactionReference = $overrideRefWithPrefix;


//        Log::notice('Generating Reference Number....');
        if ($this->overrideTransactionReference) {
            $this->txref = $this->transactionPrefix;
        } else {
            $this->txref = uniqid($this->transactionPrefix);
        }
//        Log::notice('Generated Reference Number....' . $this->txref);

        $this->baseUrl = $this->urls[($this->env === "live" ? "$this->env" : "test")];

//        Log::notice('Rave Class Initializes....');

    }


    /**
     * @throws Exception
     */
    public function paymentRequest()
    {
        if (!$this->publicKey || !$this->secretKey) {
            throw new Exception('Rave pay: rave public key and secret key is required to initialize rave payment');
        }


        /** @var Cart $cart */
        $cart = $this->getCart();

        $curl = curl_init();

//        $customer_email = "jeanlinux5@gmail.com";
        $customer_email = $cart->customer_email;
        $amount = $cart->grand_total;
//        $amount = 400;
        $currency = $cart->cart_currency_code;
//        $currency = 'GHS';
        $txref = $this->txref; // ensure you generate unique references per transaction.
        $PBFPubKey = $this->publicKey; // get your public key from the dashboard.
        $redirect_url = route('rave.standard.callback');


        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . "/flwv3-pug/getpaidx/api/v2/hosted/pay",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'amount' => $amount,
                'customer_email' => $customer_email,
                'currency' => $currency,
                'txref' => $txref,
                'PBFPubKey' => $PBFPubKey,
                'redirect_url' => $redirect_url,
                'customer_firstname' => $cart->customer_first_name,
//                'customer_firstname' => "John",
                "payment_method" => $this->paymentMethod,
                "customer_lastname" => $cart->customer_last_name,
//                "customer_lastname" => "Doe",
//                "country" => $cart->country,
                "country" => "GH",
//                "custom_description" => "By a new Jersey",
//                "custom_description" => core()->getConfigData(self::CONFIG_DESCRIPTION),
                "custom_logo" => core()->getConfigData(self::CONFIG_RAVE_LOGO),
                "custom_title" => $this->customTitle,
//                "custom_title" => core()->getConfigData(self::CONFIG_RAVE_TITLE),
                "customer_phone" => $cart->getBillingAddressAttribute()->phone,
//                "customer_phone" => '233260805346',
                "hosted_payment" => 1
            ]),
            CURLOPT_HTTPHEADER => [
                "content-type: application/json",
                "cache-control: no-cache"
            ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ($err) {
            // there was an error contacting the rave API
            die('Curl returned error: ' . $err);
        }

        $transaction = json_decode($response);

        if (!$transaction->data && !$transaction->data->link) {
            // there was an error from the API
            print_r('API returned error: ' . $transaction->message);
        }

// uncomment out this line if you want to redirect the user to the payment page
//        Log::debug($transaction->data->link);


// redirect to page so User can pay
// uncomment this line to allow the user redirect to the payment page
//        header('Location: ' . $transaction->data->link);

        return $transaction->data->link;
    }



    /**
     * Return rave redirect url
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return route('rave.standard.redirect');
    }
}