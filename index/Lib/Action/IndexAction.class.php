<?php
class IndexAction extends Action {

    public function index(){
        $this -> display();
    }

    public function weibo(){
        dump($_POST);
        $this -> display();
    }
}