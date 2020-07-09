<?php

class BalanceOperations_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'balance_operations';

    /** @var int */
    protected $user_id;
    /** @var float  */
    protected $amount;
    /** @var string */
    protected $action;
    /** @var int */
    protected $likes;
    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    /**
     * @return int
     */
    public function get_user_id(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function set_user_id(int $user_id)
    {
        $this->user_id = $user_id;
        return $this->save('user_id', $user_id);
    }

    /**
     * @return int
     */
    public function get_amount(): float
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     *
     * @return bool
     */
    public function set_amount(float $amount)
    {
        $this->amount = $amount;
        return $this->save('amount', $amount);
    }


    /**
     * @return string
     */
    public function get_action(): string
    {
        return $this->action;
    }

    /**
     * @param string $source
     *
     * @return bool
     */
    public function set_action(string $action)
    {
        $this->action = $action;
        return $this->save('action', $action);
    }

    /**
     * @return string
     */
    public function get_likes(): int
    {
        return $this->likes;
    }

    /**
     * @param int $income_likes
     *
     * @return bool
     */
    public function set_likes(int $likes)
    {
        $this->likes = $likes;
        return $this->save('likes', $likes);
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
    public function set_time_updated(int $time_updated)
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }

    function __construct($id = NULL)
    {
        parent::__construct();

        $this->set_id($id);
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


    public static function get_all_by_user_id(int $user_id) {
        $data = App::get_ci()->s->from(self::CLASS_TABLE)->where(['user_id' => $user_id])->orderBy('time_created','DESC')->many();
        $ret = [];

        foreach ($data as $i)
        {
            $ret[] = (new self())->set($i);
        }
        return $ret;
    }

    /**
     * @param self|self[] $data
     * @param string $preparation
     * @return stdClass|stdClass[]
     * @throws Exception
     */
    public static function preparation($data, $preparation = 'default')
    {
        switch ($preparation)
        {
            case 'full_info':
                return self::_preparation_full_info($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }


    /**
     * @param self[] $data
     * @return stdClass[]
     */
    private static function _preparation_full_info($data)
    {
        $ret = [];

        foreach ($data as $d){
            $o = new stdClass();

            $o->user_id = $d->get_user_id();
            $o->amount = $d->get_amount();
            $o->action = $d->get_action();
            $o->likes = $d->get_likes();

            $o->time_created = $d->get_time_created();

            $ret[] = $o;
        }

        return $ret;
    }

}
