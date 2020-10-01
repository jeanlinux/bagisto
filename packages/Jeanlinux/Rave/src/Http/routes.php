<?php
if ( ! defined( 'RAVE_CONTROLER')) {
    define('RAVE_CONTROLER', 'Jeanlinux\Rave\Http\Controllers\StandardController@');
}

Route::group(['middleware' => ['web']], function () {
    Route::prefix('rave/standard')->group(function () {

        Route::get('/redirect', RAVE_CONTROLER. 'redirect')->name('rave.standard.redirect');
        Route::post('/callback', RAVE_CONTROLER. 'callback')->name('rave.standard.callback');

        Route::get('/success', RAVE_CONTROLER. 'success')->name('rave.standard.success');

        Route::get('/cancel', RAVE_CONTROLER. 'cancel')->name('rave.standard.cancel');
    });
});