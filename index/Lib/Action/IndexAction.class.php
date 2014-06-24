<?php
class IndexAction extends Action {

    public function index(){
        $this -> display();
    }

    public function weibo(){
        dump(parseSignedRequest($_POST['signed_request']));
        $this -> display();
    }
}