<?php

namespace Artme\Paysera;

use App\Order;
use Illuminate\Support\Facades\Config;
use WebToPay;

class Paysera {
    public static function getRequiredFields(){
        return [];
    }

    public static function makePayment($order_id, $amount, $options = []){
        try {
            Order::findOrFail($order_id)->setStatus(Config::get('paysera.statuses.2'));

            $payment_data = [
                'projectid'     => config('paysera.projectid'),
                'sign_password' => config('paysera.sign_password'),
                'currency'      => config('paysera.currency'),
                'country'       => config('paysera.country'),
                'test'          => config('paysera.test'),

                'orderid'       => $order_id,
                'amount'        => intval($amount*100),
                'accepturl'     => route('front.order.show', $order_id),
                'cancelurl'     => route('front.order.show', $order_id),
                'callbackurl'   => route('artme.paysera.callback', [])
            ];

            $request = WebToPay::redirectToPayment(array_merge($payment_data, $options), true);
        } catch (WebToPayException $e) {
            // handle exception
        }
    }
}
