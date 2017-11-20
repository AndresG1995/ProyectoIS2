<?php

use Illuminate\Support\Facades\Input as input;
use App\User;
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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');
Route::get('login', 'Auth\AuthController@getLogin');
Route::post('login', ['as' =>'login', 'uses' => 'Auth\AuthController@postLogin']);
Route::post('logout', ['as' => 'logout', 'uses' => 'Auth\AuthController@getLogout']);





Route::get('/changepassword', function(){
	return view('auth/change_password');
});

Route::post('change/password',function(){
	$User = User::find(Auth::user()->id);
	if(Hash::check(Input::get('passwordold'), $User['password']) && Input::get('password') == Input::get('password_confirmation')){
		$User->password = bcrypt(Input::get('password'));
		$User->save();
		return back()->with('success','Password Changed');
	}
	else{
		return back()->with('error','Password NOt  Changed!!');
	}

});

//Route::get('password/email', 'Auth\PasswordController@getEmail');
//Route::post('password/email', 'Auth\PasswordController@postEmail');
