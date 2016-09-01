<?php

namespace Artme\Paysera;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use WebToPay;

class Paysera {
    public static function getRequiredFields(){
        return [];
    }

    /**
     * Return available payment methods by country and payment group
     * Method parameters can be set via config
     *
     * @param string [Optional] $country
     * @param array [Optional] $payment_groups_names
     * @return array
     */
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

    /**
     * Generates full request and redirects with parameters to Paysera
     * Parameter $options can override $order_id and $amount
     *
     * TODO: Handle exceptions. At the moment imagine you're doing everything perfectly
     *
     * @param integer $order_id
     * @param float $amount
     * @param array $options
     */
    public static function makePayment($order_id, $amount, $options = []){
        try {
            $payment_data = [
                'projectid'     => config('paysera.projectid'),
                'sign_password' => config('paysera.sign_password'),
                'currency'      => config('paysera.currency'),
                'country'       => config('paysera.country'),
                'test'          => config('paysera.test'),
                'version'       => '1.6',

                'callbackurl'   => route('artme.paysera.callback'),

                'orderid'       => $order_id,
                'amount'        => intval($amount*100)
            ];

            $payment_data = array_merge($payment_data, $options);
            $payment_data['cancelurl'] = self::getCancelUrl($payment_data['cancelurl'], $order_id);


            $request = WebToPay::redirectToPayment($payment_data, true);
        } catch (WebToPayException $e) {
            echo get_class($e) . ': ' . $e->getMessage();
        }
    }

    /**
     * Check if callback response is from Paysera and parse data to array
     *
     * @param Request $request
     * @return array
     */
    public static function verifyPayment(Request $request){
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


    public static function updateOrderStatus(Request $request){
        $request_data = self::verifyPayment($request);
        $namespace = config('paysera.order_model_namespace');

        if(!is_null($namespace)){
            $order = $namespace::findOrFail($request_data['orderid']);
            if(method_exists($order, 'setStatus')){
                $order->setStatus($request_data['status']);
                return true;
            }
        }

        return false;
    }

    public static function getCancelUrl($url, $order_id){
        $parsed_url = parse_url($url);
        if(isset($parsed_url['query'])){
            $query = parse_str($parsed_url['query']);
        } else {
            $query = [];
        }
        $query['order_id'] = Crypt::encrypt($order_id);
        $parsed_url['query'] = http_build_query($query);

        return self::unparseUrl($parsed_url);
    }

    private static function unparseUrl($parsed_url) {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}
