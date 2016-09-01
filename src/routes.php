<?php

Route::get(config('paysera.callback_uri'), [
    'as' => 'artme.paysera.callback', 'uses' => 'artme\paysera\PayseraController@callback'
]);