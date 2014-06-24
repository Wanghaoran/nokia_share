<?php
class IndexAction extends Action {

    public function index(){
        $this -> display();
    }

    public function weibo(){
        //微博POST的数据
        $weibo_post = parseSignedRequest($_POST['signed_request']);
        if($weibo_post == '-1' || $weibo_post == '-2'){
            $this -> assign('parse_error', 1);
        }else if(empty($weibo_post['user_id'])){
            $this -> assign('no_login', 1);
        }else{
            $this -> assign('login', 1);
            $this -> assign('user_id', $weibo_post['user_id']);
        }

        $this -> display();
    }
}