<?php

namespace App\Http\Controllers\Auth;
use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Auth;
use DB;



class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use ThrottlesLogins;


    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
        $this->middleware('guest', ['except' => 'getLogout']);
      
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
   


//login

       protected function getLogin()
    {
        return view("auth/login");
    }


       

public function postLogin(Request $request)
   {
//usuario=User::findOrFail(Auth::user()->id);
  // $usuario->estado; 
    $this->validate($request, [
        'email' => 'required',
        'password' => 'required',
    ]);

$credentials = $request->only('email', 'password');

if ($this->auth->attempt($credentials, $request->has('remember')))
    {
        $usuarios=User::findOrFail(Auth::user()->id);

      if($usuarios->estado!=1)
      {

        //Se define las variables para el mensaje de Telegram
        define('BOT_TOKEN',Auth::user()->token);
        define('CHAT_ID',Auth::user()->char_id);
        define('API_URL','https://api.telegram.org/bot'.Auth::user()->token.'/');

        //Envio de Mensaje con la frase "Inicio de Session"
        $this->enviar_telegram('Inicio de session');

        DB::select('CALL Estado(?)',array(Auth::user()->id));
        return redirect('home');
      }
      else
      {
        $this->auth->logout();
        Session::flush();
         return view('error');
      }
    // session::flash('msg', 'Thanks for voting');
        
    }
         return view('auth/login');

   // return view('login')->with("msjerror","credenciales incorrectas");


    }


//login

 //registro   


        protected function getRegister()
    {
        return view("welcome");
    }

public function enviar_telegram($msj)
{
    $queryArray=[ 
    'chat_id'=> CHAT_ID,
    'text'=>$msj, ];
    $url='https://api.telegram.org/bot'.BOT_TOKEN.'/sendMessage?'.http_build_query($queryArray);
    $result=file_get_contents($url);
}
        

    protected function postRegister(Request $request)
   {
    $this->validate($request, [
        'name' => 'required',
        //'last_name' => 'required',
        'email' => 'required',
        'password' => 'required, num',
    ]);


    $data = $request;


    $user=new User;
    $user->name=$data['name'];
    //$user->last_name=$data['last_name'];
    $user->email=$data['email'];
    $user->password=bcrypt($data['password']);
    $user->estado=false;

    if($user->save()){

         return view("/login");
               
    }
   

   

}

//registro

protected function getLogout()
    {
       DB::select('CALL Estado(?)',array(Auth::user()->id));
        $this->auth->logout();
        Session::flush();

        return redirect('/');
    }

public function contacto(){
        return view('scandir(directory)contacto');
   }





}
