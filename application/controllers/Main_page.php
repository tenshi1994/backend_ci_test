<?php

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 10.11.2018
 * Time: 21:36
 */
class Main_page extends MY_Controller
{

    private $post = array();

    public function __construct()
    {
        parent::__construct();

        App::get_ci()->load->library('form_validation');

        App::get_ci()->load->model('User_model');
        App::get_ci()->load->model('Login_model');
        App::get_ci()->load->model('Post_model');
        App::get_ci()->load->model('Comment_model');
        App::get_ci()->load->model('Boosterpack_model');
        App::get_ci()->load->model('Likes_model');
        App::get_ci()->load->model('BalanceOperations_model');

        if (is_prod())
        {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function index()
    {
        $user = User_model::get_user();

        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    public function check_login() {
        if (!User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }
        $user = User_model::preparation(User_model::get_user(), 'main_page');
        return $this->response_success(['user' => $user]);
    }

    public function get_balance_operations() {
        if (!User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $balance_operations = User_model::preparation(User_model::get_user(), 'balance_operations');

        return $this->response_success(['balance_operations' => $balance_operations]);
    }

    public function get_all_posts() {
        $posts =  Post_model::preparation(Post_model::get_all(), 'main_page');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_post($post_id){ // or can be $this->input->post('news_id') , but better for GET REQUEST USE THIS
        $post_id = intval($post_id);

        if (empty($post_id)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try
        {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }


        $post =  Post_model::preparation($post, 'full_info');

        return $this->response_success(['post' => $post]);
    }


    public function comment() {
        $this->form_validation->set_rules('post_id', 'Post id', 'integer|required');
        $this->form_validation->set_rules('parent_id', 'Parent Id', 'integer|required');
        $this->form_validation->set_rules('text', 'Text', 'required');


        if (!User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $post = $this->preparePost();

        if (!$this->form_validation->set_data($post)->run()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try
        {
            $post_model = new Post_model($post['post_id']);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        Comment_model::create(array(
            'user_id' => User_model::get_session_id(),
            'assign_id' => $post['post_id'],
            'parent_id' => $post['parent_id'],
            'text' => $post['text']
        ));

        $post_model =  Post_model::preparation($post_model, 'full_info');;
        return $this->response_success(['post' => $post_model]);
    }

    public function login() {
        $this->form_validation->set_rules('login', 'Login', 'trim|valid_email|required');
        $this->form_validation->set_rules('password', 'Password', 'required');

        $post = $this->preparePost();
        if (!$this->form_validation->set_data($post)->run()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        // Checking if user credentials is right
        try{
            $user = Login_model::checkLogin($post['login'], $post['password']);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_AUTH_WRONG);
        }

        Login_model::start_session($user->get_id());

        return $this->response_success(['user' => $user->get_id()]);
    }


    public function logout() {
        Login_model::logout();
        redirect(site_url('/'));
    }

    public function add_money() {
        $this->form_validation->set_rules('amount', 'Amount', 'greater_than[0]|required');

        if (!User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $post = $this->preparePost();

        if(!$this->form_validation->set_data($post)->run()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }


        // TODO: For test like this, in future when we will have an API endpoint for refill, need to add curl request
        $result = true;

        if(!$result) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_TRY_LATER);
        }

        try{
            $user = User_model::get_user();
            $user->refill_balance($post['amount']);
        }
        catch(EmeraldModelNoDataException $ex) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_TRY_LATER);
        }

        return $this->response_success(['balance' => $user->get_wallet_balance()]);
    }

    public function get_all_boosterpacks() {
        $boosterpacks =  Boosterpack_model::preparation(Boosterpack_model::get_all(), 'main_page');
        return $this->response_success(['boosterpacks' => $boosterpacks]);
    }

    public function buy_boosterpack(){
        $this->form_validation->set_rules('id', 'Boosterpack id', 'integer|required');

        if (!User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $post = $this->preparePost();

        if(!$this->form_validation->set_data($post)->run()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try{
            $boosterpack = new Boosterpack_model($post['id']);
        } catch (EmeraldModelNoDataException $ex) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        $user = User_model::get_user();

        if($boosterpack->get_price() > $user->get_wallet_balance()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NOT_ENOUGH_BALANCE);
        }

        $likes = $boosterpack->get_likes();

        $user->buy_likes($likes, $boosterpack->get_price());

        BalanceOperations_model::create(array(
            'user_id' => $user->get_id(),
            'amount' => $boosterpack->get_price(),
            'action' => 'buy_boosterpack',
            'likes' => $likes
        ));

        return $this->response_success(['likes' => $likes, 'user_likes' => $user->get_likes(), 'balance' => $user->get_wallet_balance()]);
    }


    public function like() {
        $this->form_validation->set_rules('source', 'Source', 'in_list[post,comment]|required'); // post or comment
        $this->form_validation->set_rules('id', 'Id', 'integer|required'); // id of post or comment

        if (!User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $post = $this->preparePost();
        if(!$this->form_validation->set_data($post)->run()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        $user = User_model::get_user();

        if(!$user->check_likes_balance()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NOT_ENOUGH_LIKE_BALANCE);
        }

        if($post['source'] == 'post') {
            $source = new Post_model($post['id']);
        }

        if($post['source'] == 'comment') {
            $source = new Comment_model($post['id']);
        }

        // TODO: Not sure what need to happen when user uinlike the post or comment, should the like come back to him?
        Likes_model::create(array(
            'user_id' => $user->get_id(),
            'assign_id' => $post['id'],
            'source' => $post['source']
        ));

        $likes = Likes_model::preparation($source->get_likes(), 'full_info');
        $user->spend_like();

        return $this->response_success(['likes' => $likes, 'user_likes' => $user->get_likes()]); // Колво лайков под постом \ комментарием чтобы обновить
    }

    /**
     * Return's array of post that sent by json
     * @return array
     */
    protected function preparePost() {
        return json_decode($this->input->raw_input_stream, true) ?? array();
    }
}
