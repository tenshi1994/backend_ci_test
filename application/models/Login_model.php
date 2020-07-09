<?php
class Login_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();

    }

    public static function checkLogin(string $login, string $password) {
        // In future we can encrypt password here
        $enc_pass = $password;

        $user = User_model::get_by_login_password($login, $enc_pass);

        if(!$user) {
            throw new CriticalException('Invalid credentials');
        }

        return $user;
    }

    public static function logout()
    {
        App::get_ci()->session->unset_userdata('id');
    }

    public static function start_session(int $user_id)
    {
        // если перенедан пользователь
        if (empty($user_id))
        {
            throw new CriticalException('No id provided!');
        }

        App::get_ci()->session->set_userdata('id', $user_id);
    }


}
