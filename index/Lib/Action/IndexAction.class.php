<?php
class IndexAction extends Action {

    public function index(){
        $this -> display();
    }

    public function weibo(){
        if(empty($_POST['signed_request'])){
            redirect('Index/index');
        }
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

    public function check(){
        $User = M('User');
        $where = array();
        $where['type'] = $this -> _get('type');
        $where['content'] = $this -> _get('id');
        $result = $User -> field('id') -> where($where) -> find();
        $return_result = array();
        //没有数据则新建数据
        if(!$result){
            $data = array();
            $data['type'] = $this -> _get('type');
            $data['content'] = $this -> _get('id');
            if($id = $User -> add($data)){
                $return_result['status'] = 'success';
                $return_result['id'] = $id;
            }else{
                $return_result['status'] = 'error';
            }
        }else{
            $return_result['status'] = 'success';
            $return_result['id'] = $result['id'];
        }
        echo json_encode($return_result);
    }

    public function checkqr(){
        $id = $this -> _get('id', 'intval');
        Vendor('phpqrcode.phpqrcode');
        $value="http://182.92.64.207/nokia_share/share/" . $id;
        $errorCorrectionLevel = "H";
        $matrixPointSize = "10";
        QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize);
        exit;
    }

    public function share(){
        dump($_GET);
    }
}