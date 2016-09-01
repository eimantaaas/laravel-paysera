<?php

namespace Artme\Paysera;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

class PayseraMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $route_name = $request->route()->getName();


        switch ($route_name){
            case config('paysera.routes_names.cancel'):
                if($request->get('order_id') && $request->get('order_id')){
                $namespace = config('paysera.order_model_namespace');
                    if(!is_null($namespace)){
                        $order = $namespace::find(Crypt::decrypt($request->get('order_id')));
                        if($order && method_exists($order, 'setStatus')){
                            $order->setStatus(0);
                        }
                    }
                }
                break;
            case config('paysera.routes_names.accept'):
                Paysera::updateOrderStatus($request);
                break;
        }

        return $next($request);
    }
}
