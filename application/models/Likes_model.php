<?php

class Likes_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'likes';

    /** @var int */
    protected $user_id;
    /** @var int  */
    protected $assign_id;
    /** @var string Post or Comment */
    protected $source;
    /** @var string */
    protected $time_created;


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
    public function get_assign_id(): int
    {
        return $this->assign_id;
    }

    /**
     * @param int $assign_id
     *
     * @return bool
     */
    public function set_assign_id(int $assign_id)
    {
        $this->assign_id = $assign_id;
        return $this->save('assign_id', $assign_id);
    }


    /**
     * @return string
     */
    public function get_source(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return bool
     */
    public function set_source(string $source)
    {
        $this->source = $source;
        return $this->save('source', $source);
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
     * @param int $assting_id
     * @return self[]
     * @throws Exception
     */
    public static function get_all_by_assign_id_and_source(int $assign_id, string $source)
    {

        $data = App::get_ci()->s->from(self::CLASS_TABLE)->where(['assign_id' => $assign_id, 'source' => $source])->orderBy('time_created','ASC')->many();
        $ret = [];

        foreach ($data as $i)
        {
            $ret[] = (new self())->set($i);
        }
        return $ret;
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
            $o->id = $d->get_id();
            $o->user_id = $d->get_user_id();

            $o->time_created = $d->get_time_created();

            $ret[] = $o;
        }

        return $ret;
    }

}
