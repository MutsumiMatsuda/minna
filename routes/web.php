<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
  //return view('check');
  //return redirect(route('domain.add'));
//});

Route::get('phpinfo', function () {
  return phpinfo();
});

Route::get('.well-known/acme-challenge/{fname}', function () {
  $ary = explode('/', url()->current());

  $path = '/home/mu/webroot/minna/storage/acme/' . $ary[2] . '/' . $ary[count($ary) - 1];
  //dd($path);
  $f = File::get($path);
  //dd($path, $f);
  return $f;
});

use App\Http\Controllers\AcmeController;
Route::controller(AcmeController::class)->group(function() {
  Route::get('/', 'add')->name("domain.top");
  Route::get('domain/add', 'add')->name("domain.add");
  Route::post('domain/add', 'create')->name("domain.create");
  Route::post('domain/remove', 'remove')->name("domain.remove");
  //Route::get('.well-known/acme-challenge/{fname}', 'challenge')->name("domain.challenge");

  Route::get('test', 'test');
  Route::get('api', 'api');
});
