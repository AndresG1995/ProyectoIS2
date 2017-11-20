<?php

namespace App\Notifications;


use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Auth;
use Session;
//use resource\views\auth\passwords\reset;
//use Auth;
use DB;

//define('BOT_TOKEN','418313703:AAFNbJi6Bktm_hzx0BBombgauKckLvdVQYU');
//define('CHAT_ID','448027369');
//define('API_URL','https://api.telegram.org/bot'.BOT_TOKEN.'/');

class MyResetPassword extends ResetPassword
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
//      Cambio de clave manual
//      echo bcrypt('HolaMundo');
//      die();
        
        //Almaceno la nueva contraseña en la variable aux.
        $aux = $this->generaPass(); 

        //Almaceno el id, token y char_id que me retorna la consulta a la base de datos en la variable users.
        $users = DB::select('select id, token, char_id from users where email = ?', [$notifiable->email]);

        //Se define las variables para el mensaje de Telegram.
        define('BOT_TOKEN',$users[0]->token); //De la variable $users extraigo el token.
        define('CHAT_ID',$users[0]->char_id); //De la variable $users extraigo el char id.
        define('API_URL','https://api.telegram.org/bot'.$users[0]->token.'/');


        //Envio de mensaje a Telegram
        $this->enviar_clave('Nueva Clave: '.$aux);
        
        //Actualizo las password del usuario dependiendo del id que envie.
        $affected = DB::update('update users set password = ? where id = ?', [bcrypt($aux), $users[0]->id]);
        
        //Personalizacion del Email
        return (new MailMessage)
        ->subject('Recuperar contraseña')
        ->greeting('Hola')
        ->line('Estás recibiendo este correo porque hiciste una solicitud de recuperación de contraseña para tu cuenta.')
        ->action('Recuperar contraseña', route('password.reset', $this->token))
        ->line('Si no realizaste esta solicitud, no se requiere realizar ninguna otra acción.')
        ->line('Nueva contraseña: '.$aux)

        ->salutation('Saludos, '. config('app.name'));
                    /*->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');*/

          //DB::statement('CALL CambioClave(?,?)', array(Auth::user()->id, 'Hola' ));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }


//GENERADOR DE CONTRASEÑA ALEATORIA
    public function generaPass(){
        //Se define una cadena de caractares. Te recomiendo que uses esta.
        $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        //Obtenemos la longitud de la cadena de caracteres
        $longitudCadena=strlen($cadena);
         
        //Se define la variable que va a contener la contraseña
        $pass = "";

        //Se define la longitud de la contraseña, en mi caso 10, pero puedes poner la longitud que quieras
        $longitudPass=10;
         
        //Creamos la contraseña
        for($i=1 ; $i<=$longitudPass ; $i++){
            //Definimos numero aleatorio entre 0 y la longitud de la cadena de caracteres-1
            $pos=rand(0,$longitudCadena-1);
         
            //Vamos formando la contraseña en cada iteraccion del bucle, añadiendo a la cadena $pass la letra correspondiente a la posicion $pos en la cadena de caracteres definida.
            $pass .= substr($cadena,$pos,1);
        }
        return $pass;
    }

    public function enviar_clave($msj)
{

    $queryArray=[ 
    'chat_id'=> CHAT_ID,
    'text'=>$msj, ];
    $url='https://api.telegram.org/bot'.BOT_TOKEN.'/sendMessage?'.http_build_query($queryArray);
    $result=file_get_contents($url);
}
}
