<?php

namespace Artme\Paysera;

use App\Order;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use WebToPay;

class Paysera {
    public static function getRequiredFields(){
        return [];
    }

    public static function getPaymentMethods($country = null, $payment_groups_names = null){
        $payment_methods_info = WebToPay::getPaymentMethodList(intval(config('paysera.projectid')), config('paysera.currency'));
        $country_code = $country !== null?$country:strtolower(config('paysera.country'));
        $payment_methods_info->setDefaultLanguage(App::getLocale());

        $result = [];

        $country_payment_methods_info = $payment_methods_info->getCountry($country_code);
        $result['country_code'] = $country_payment_methods_info->getCode();
        $result['country_title'] = $country_payment_methods_info->getTitle();
        $payment_methods_groups_all = $country_payment_methods_info->getGroups();
        if($payment_groups_names == null){
            $payment_groups_names = config('paysera.payment_groups');
        }
        foreach ($payment_groups_names as $payment_groups_name){
            $payment_methods_groups[$payment_groups_name] = $payment_methods_groups_all[$payment_groups_name];
            $result['payment_groups'][$payment_groups_name]['title'] = $payment_methods_groups_all[$payment_groups_name]->getTitle(App::getLocale());
            foreach($payment_methods_groups_all[$payment_groups_name]->getPaymentMethods() as $key => $method){
                $tmp = [];
                $tmp['title'] = $method->getTitle(App::getLocale());
                $tmp['key'] = $key;
                $tmp['currency'] = $method->getBaseCurrency();
                $tmp['logo_url'] = $method->getLogoUrl();
                $tmp['object'] = $method;

                $result['payment_groups'][$payment_groups_name]['methods'][$key] = $tmp;
            }
        }
        return $result;
    }

    public static function makePayment($order_id, $amount, $options = []){
        try {
            $payment_data = [
                'projectid'     => config('paysera.projectid'),
                'sign_password' => config('paysera.sign_password'),
                'currency'      => config('paysera.currency'),
                'country'       => config('paysera.country'),
                'test'          => config('paysera.test'),
                'version'       => '1.6',

                'orderid'       => $order_id,
                'amount'        => intval($amount*100)
            ];

            $request = WebToPay::redirectToPayment(array_merge($payment_data, $options), true);
        } catch (WebToPayException $e) {
            // handle exception
        }
    }

    public static function verifyPayment($request){
        try {
            $response = WebToPay::validateAndParseData(
                $request->all(),
                intval(config('paysera.projectid')),
                config('paysera.sign_password')
            );

            return $response;
        } catch (Exception $e) {
            echo get_class($e) . ': ' . $e->getMessage();
        }
    }
}
