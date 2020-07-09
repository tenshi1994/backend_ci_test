<?php

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class User_model extends CI_Emerald_Model {
    const CLASS_TABLE = 'user';


    /** @var string */
    protected $email;
    /** @var string */
    protected $password;
    /** @var string */
    protected $personaname;
    /** @var string */
    protected $profileurl;
    /** @var string */
    protected $avatarfull;
    /** @var int */
    protected $rights;
    /** @var int */
    protected $likes;
    /** @var float */
    protected $wallet_balance;
    /** @var float */
    protected $wallet_total_refilled;
    /** @var float */
    protected $wallet_total_withdrawn;
    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    protected $balance_operations;


    private static $_current_user;

    /**
     * @return string
     */
    public function get_email(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public function set_email(string $email)
    {
        $this->email = $email;
        return $this->save('email', $email);
    }

    /**
     * @return string|null
     */
    public function get_password(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return bool
     */
    public function set_password(string $password)
    {
        $this->password = $password;
        return $this->save('password', $password);
    }

    /**
     * @return string
     */
    public function get_personaname(): string
    {
        return $this->personaname;
    }

    /**
     * @param string $personaname
     *
     * @return bool
     */
    public function set_personaname(string $personaname)
    {
        $this->personaname = $personaname;
        return $this->save('personaname', $personaname);
    }

    /**
     * @return string
     */
    public function get_avatarfull(): string
    {
        return $this->avatarfull;
    }

    /**
     * @param string $avatarfull
     *
     * @return bool
     */
    public function set_avatarfull(string $avatarfull)
    {
        $this->avatarfull = $avatarfull;
        return $this->save('avatarfull', $avatarfull);
    }

    /**
     * @return int
     */
    public function get_rights(): int
    {
        return $this->rights;
    }

    /**
     * @param int $rights
     *
     * @return bool
     */
    public function set_rights(int $rights)
    {
        $this->rights = $rights;
        return $this->save('rights', $rights);
    }

    /**
     * @return float
     */
    public function get_wallet_balance(): float
    {
        return $this->wallet_balance;
    }

    /**
     * @param float $wallet_balance
     *
     * @return bool
     */
    public function set_wallet_balance(float $wallet_balance)
    {
        $this->wallet_balance = $wallet_balance;
        return $this->save('wallet_balance', $wallet_balance);
    }

    /**
     * @return float
     */
    public function get_wallet_total_refilled(): float
    {
        return $this->wallet_total_refilled;
    }

    /**
     * @param float $wallet_total_refilled
     *
     * @return bool
     */
    public function set_wallet_total_refilled(float $wallet_total_refilled)
    {
        $this->wallet_total_refilled = $wallet_total_refilled;
        return $this->save('wallet_total_refilled', $wallet_total_refilled);
    }

    /**
     * @return int
     */
    public function get_likes(): int
    {
        return $this->likes;
    }

    /**
     * @param int $$likes
     *
     * @return bool
     */
    public function set_likes(int $likes)
    {
        $this->likes = $likes;
        return $this->save('likes', $likes);
    }

    /**
     * @return float
     */
    public function get_wallet_total_withdrawn(): float
    {
        return $this->wallet_total_withdrawn;
    }

    /**
     * @param float $wallet_total_withdrawn
     *
     * @return bool
     */
    public function set_wallet_total_withdrawn(float $wallet_total_withdrawn)
    {
        $this->wallet_total_withdrawn = $wallet_total_withdrawn;
        return $this->save('wallet_total_withdrawn', $wallet_total_withdrawn);
    }

    /**
     * @return string
     */
    public function get_time_created(): string
    {
        return $this->time_created;
    }

    /**
     * @param string $time_created
     *
     * @return bool
     */
    public function set_time_created(string $time_created)
    {
        $this->time_created = $time_created;
        return $this->save('time_created', $time_created);
    }

    /**
     * @return string
     */
    public function get_time_updated(): string
    {
        return $this->time_updated;
    }

    /**
     * @param string $time_updated
     *
     * @return bool
     */
    public function set_time_updated(string $time_updated)
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }

    public function get_balance_operations() {
        $this->is_loaded(TRUE);
        if (empty($this->balance_operations))
        {
            $this->balance_operations = BalanceOperations_model::get_all_by_user_id($this->get_id());
        }
        return $this->balance_operations;
    }

    function __construct($id = NULL)
    {
        parent::__construct();

        App::get_ci()->load->model('BalanceOperations_model');

        $this->set_id($id);
    }

    public function reload(bool $for_update = FALSE)
    {
        parent::reload($for_update);

        return $this;
    }

    public static function create(array $data)
    {
        App::get_ci()->s->from(self::CLASS_TABLE)->insert($data)->execute();
        return new static(App::get_ci()->s->get_insert_id());
    }

    public function delete()
    {
        $this->is_loaded(TRUE);
        App::get_ci()->s->from(self::CLASS_TABLE)->where(['id' => $this->get_id()])->delete()->execute();
        return (App::get_ci()->s->get_affected_rows() > 0);
    }

    /**
     * @return self[]
     * @throws Exception
     */
    public static function get_all()
    {

        $data = App::get_ci()->s->from(self::CLASS_TABLE)->many();
        $ret = [];
        foreach ($data as $i)
        {
            $ret[] = (new self())->set($i);
        }
        return $ret;
    }

    /**
     * @param string $login
     * @param string $password
     * @return static|false
     */
    public static function get_by_login_password(string $login, string $password) {
        $data = App::get_ci()->s->from(self::CLASS_TABLE)
            ->where('email', $login)
            ->where('password', $password)
            ->one();

        if (!$data) {
            return false;
        }

        return (new self())->set($data);
    }

    public function refill_balance(float $amount) {

        $wallet_balance = $this->get_wallet_balance();
        $wallet_total_refilled = $this->get_wallet_total_refilled();

        $this->set_wallet_balance($wallet_balance + $amount);
        $this->set_wallet_total_refilled($wallet_total_refilled + $amount);

        BalanceOperations_model::create(array(
           'user_id' => $this->get_id(),
           'amount' => $amount,
           'action' => 'refill'
        ));
    }

    public function buy_likes(int $likes, float $amount) {
        $wallet_balance = $this->get_wallet_balance();
        $wallet_total_withdrawn = $this->get_wallet_total_withdrawn();
        $user_likes = $this->get_likes();

        $this->set_wallet_balance($wallet_balance - $amount);
        $this->set_wallet_total_withdrawn($wallet_total_withdrawn + $amount);
        $this->set_likes($user_likes + $likes);
    }

    public function spend_like() {
        $likes = $this->get_likes();
        return $this->set_likes(--$likes);
    }

    public function check_likes_balance() {
        return $this->likes > 0;
    }

    /**
     * @param User_model|User_model[] $data
     * @param string $preparation
     * @return stdClass|stdClass[]
     * @throws Exception
     */
    public static function preparation($data, $preparation = 'default')
    {
        switch ($preparation)
        {
            case 'main_page':
                return self::_preparation_main_page($data);
            case 'full_info':
                return self::_preparation_full_info($data);
            case 'balance_operations':
                return self::_preparation_balance_operations($data);
            case 'default':
                return self::_preparation_default($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }

    /**
     * @param User_model $data
     * @return stdClass
     */
    private static function _preparation_main_page($data)
    {
        $o = new stdClass();

        $o->id = $data->get_id();

        $o->personaname = $data->get_personaname();
        $o->avatarfull = $data->get_avatarfull();
        $o->wallet_balance = $data->get_wallet_balance();
        $o->likes = $data->get_likes();

        $o->time_created = $data->get_time_created();
        $o->time_updated = $data->get_time_updated();


        return $o;
    }

    /**
     * @param User_model $data
     * @return stdClass
     */
    private static function _preparation_full_info($data)
    {
        $o = new stdClass();

        $o->id = $data->get_id();

        $o->personaname = $data->get_personaname();
        $o->avatarfull = $data->get_avatarfull();

        $o->wallet_balance = $data->get_wallet_balance();
        $o->wallet_total_refilled = $data->get_wallet_total_refilled();
        $o->wallet_total_withdrawn = $data->get_wallet_total_withdrawn();
        $o->likes = $data->get_likes();
        $o->balance_operations = BalanceOperations_model::preparation($data->get_balance_operations(), 'full_info');
        $o->time_created = $data->get_time_created();
        $o->time_updated = $data->get_time_updated();


        return $o;
    }

    /**
     * @param User_model $data
     * @return stdClass
     */
    private static function _preparation_balance_operations($data)
    {
        $o = new stdClass();

        $o->id = $data->get_id();

        $o->wallet_balance = $data->get_wallet_balance();
        $o->wallet_total_refilled = $data->get_wallet_total_refilled();
        $o->wallet_total_withdrawn = $data->get_wallet_total_withdrawn();
        $o->balance_operations = BalanceOperations_model::preparation($data->get_balance_operations(), 'full_info');

        return $o;
    }

    /**
     * @param User_model $data
     * @return stdClass
     */
    private static function _preparation_default($data)
    {
        $o = new stdClass();

        if (!$data->is_loaded())
        {
            $o->id = NULL;
        } else {
            $o->id = $data->get_id();

            $o->personaname = $data->get_personaname();
            $o->avatarfull = $data->get_avatarfull();

            $o->time_created = $data->get_time_created();
            $o->time_updated = $data->get_time_updated();
        }

        return $o;
    }


    /**
     * Getting id from session
     * @return integer|null
     */
    public static function get_session_id(): ?int
    {
        return App::get_ci()->session->userdata('id');
    }

    /**
     * @return bool
     */
    public static function is_logged()
    {
        $steam_id = intval(self::get_session_id());
        return $steam_id > 0;
    }


    /**
     * Returns current user or empty model
     * @return User_model
     */
    public static function get_user()
    {
        if (! is_null(self::$_current_user)) {
            return self::$_current_user;
        }
        if ( ! is_null(self::get_session_id()))
        {
            self::$_current_user = new self(self::get_session_id());
            return self::$_current_user;
        } else
        {
            return new self();
        }
    }



}
