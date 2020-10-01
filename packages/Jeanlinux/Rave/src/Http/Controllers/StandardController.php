<?php

namespace Jeanlinux\Rave\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Prettus\Validator\Exceptions\ValidatorException;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Jeanlinux\Rave\Payment\Rave;
use Jeanlinux\Rave\Helpers\Helper;

class StandardController extends Controller
{
    /**
     * OrderRepository object
     *
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * Helper object
     *
     * @var Helper
     */
    protected $helper;

    /**
     * Rave
     *
     * @var Rave
     */
    protected $rave;

    /**
     * Create a new controller instance.
     *
     * @param OrderRepository $orderRepository
     * @param Rave $rave
     * @param Helper $helper
     */
    public function __construct(
        OrderRepository $orderRepository,
        Rave $rave,
        Helper $helper
    )
    {
        $this->orderRepository = $orderRepository;
        $this->rave = $rave;
        $this->helper = $helper;
    }

    /**
     * Redirects to the rave.
     *
     * @return RedirectResponse
     */
    public function redirect()
    {
        try {
            $redirect = $this->rave->paymentRequest();
//            Log::debug('redirect url is: ' . $redirect);
            return redirect()->to($redirect);
        } catch (Exception $e) {
            Log::debug('Exception: here is ->>>>>' . $e->getMessage());
            session()->flash('error', 'There was a problem making the payment, please try again later.');
            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Cancel payment from rave.
     *
     * @return RedirectResponse
     */
    public function cancel()
    {
        session()->flash('error', 'Rave payment has been canceled.');

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Redirect callback page from rave
     * @throws ValidatorException
     */
    public function callback()
    {
        //check for cancelled payment
        if (isset($_GET['cancelled']) && $_GET['cancelled'] == true) {
            Log::debug("Session Cancelled here");
            //payment was cancelled
            session()->flash('error', 'Rave payment has been canceled.');
            return redirect()->route('shop.checkout.cart.index');
        }

        if (isset($_GET['txref']) && isset($_GET['flwref'])) {
            $ref = $_GET['txref'];
            $amount = $this->rave->amount; //Correct Amount from Server
//            $amount = 400; //Correct Amount from Server
            $currency = $this->rave->currency; //Correct Currency from Server
//            $currency = "GHS"; //Correct Currency from Server

            $query = array(
                "SECKEY" => $this->rave->secretKey,
                "txref" => $ref
            );

            $data_string = json_encode($query);

            $ch = curl_init($this->rave->baseUrl . '/flwv3-pug/getpaidx/api/v2/verify');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

            $response = curl_exec($ch);

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);

            curl_close($ch);

            $resp = json_decode($response, true);

            $paymentStatus = $resp['data']['status'];
            $chargeResponsecode = $resp['data']['chargecode'];
            $chargeAmount = $resp['data']['amount'];
            $chargeCurrency = $resp['data']['currency'];

            if (($chargeResponsecode == "00" || $chargeResponsecode == "0") && ($chargeAmount == $amount) && ($chargeCurrency == $currency)) {
                // transaction was successful...
                // please check other things like whether you already gave value for this ref
                // if the email matches the customer who owns the product etc
                //Give Value and return to Success page
                $order = $this->orderRepository->create(Cart::prepareDataForOrder());
                try {
                    $this->helper->processOrder($order);
                } catch (Exception $e) {
                    Log::debug('Exception Occurred:', ["Message" => $e->getMessage()]);
                    throw $e;
                }
                return redirect()->route('rave.standard.success', ['flwref' => $_GET['flwref'], 'txref' => $ref, 'id' => $order->id]);
            } else {
                Log::debug('Failed to crosscheck charge amount: => Amount: ' . $amount . ' Currency =>' . $currency);
                //Dont Give Value and return to Failure page
                return redirect()->route('rave.standard.cancel');
            }
        } else {
            die('No reference supplied');
        }
    }

    /**
     * Success payment
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function success()
    {
        if (isset($_GET['flwref']) && isset($_GET['txref']) && isset($_GET['id'])) {

            //check for rave token in $_GET variable
            $flwref = $_GET['flwref'];
            $order_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
//        $txref = $_GET['txref'];


            if (is_int($order_id) && strlen($flwref) > 20) {
                Log::debug("Payment successfully made");
                $order = $this->orderRepository->find($order_id);
                Cart::deActivateCart();

                session()->flash('order', $order);

                return redirect()->route('shop.checkout.success');
            } else {
                return redirect()->route('shop.home.index');
            }
        }
    }

}