<?php

namespace Artme\Paysera;

use App\Order;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Artme\Paysera\Paysera;
use Illuminate\Support\Facades\Config;

class PayseraController extends Controller
{
    /**
     * Take care Paysera callback request
     *
     * @return string
     */
    public function callback(Request $request)
    {
        return Paysera::updateOrderStatus($request)?'OK':'ERROR';
    }
}
