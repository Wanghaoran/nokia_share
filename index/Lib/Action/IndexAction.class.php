<?php
class IndexAction extends Action {

    public function index(){
        //帮友排行
        $Num = M('Num');
        $user_rank = $Num -> alias('n') -> field('u.name as uname') -> join('nokia_user as u ON n.uid = u.id') -> order('n.sum DESC,u.addtime ASC') -> limit(10) -> select();
        foreach($user_rank as $key => $value){
            $user_rank[$key]['uname'] = cut_str($value['uname'], 1, 0).'****'.cut_str($value['uname'], 1, -1);
        }
        $this -> assign('user_rank', $user_rank);
        $this -> display();
    }

    public function weibo(){
        if(empty($_POST['signed_request'])){
            redirect('Index/index');
        }
        //微博POST的数据
        $weibo_post = parseSignedRequest($_POST['signed_request']);

        $this -> assign('signed_request', $_POST['signed_request']);

        if($weibo_post == '-1' || $weibo_post == '-2'){
            $this -> assign('parse_error', 1);
        //未登录
        }else if(empty($weibo_post['user_id'])){
            $this -> assign('no_login', 1);
        //已登录
        }else{
            $this -> assign('login', 1);
            $this -> assign('user_id', $weibo_post['user_id']);

            //计算排名和购买数量

            $User = M('User');
            //获取ID
            $where = array();
            $where['type'] = 'weibo';
            $where['content'] = $weibo_post['user_id'];
            $id_arr = $User -> field('id') -> where($where) -> find();
            if(!$id_arr){
                $this -> assign('nocheck', 1);
            }else{
                $uid = $id_arr['id'];
                $Num = M('Num');
                //购买数量
                $buy_num = $Num -> getFieldByuid($uid, 'sum');
                $this -> assign('buy_num', $buy_num);
                //获取排名
                //先计算比自己数量多的人的数量
                $where = array();
                $where['sum'] = array('GT', $buy_num);
                $rank_1 = $Num -> where($where) -> count();
                //再按照ID的顺序倒序排列
                $where = array();
                $where['uid'] = array('ELT', $uid);
                $where['sum'] = $buy_num;
                $rank_2 = $Num -> where($where) -> count();
                $rank = $rank_1 + $rank_2;
                $this -> assign('rank', $rank);

            }
        }

        //排名数据
        if(empty($weibo_post['user_id'])){
            $this -> assign('rank_error', 1);
        }else{
            $this -> assign('rank_success', 1);
        }

        //帮友排行
        $Num = M('Num');
        $user_rank = $Num -> alias('n') -> field('u.name as uname') -> join('nokia_user as u ON n.uid = u.id') -> order('n.sum DESC,u.id ASC') -> limit(10) -> select();
        foreach($user_rank as $key => $value){
            $user_rank[$key]['uname'] = cut_str($value['uname'], 1, 0).'****'.cut_str($value['uname'], 1, -1);
        }
        $this -> assign('user_rank', $user_rank);


        $this -> display();
    }

    public function check(){
        $User = M('User');
        $where = array();
        $where['type'] = $this -> _post('type');
        $where['content'] = $this -> _post('id');
        $result = $User -> field('id') -> where($where) -> find();
        $return_result = array();
        //没有数据则新建数据
        if(!$result){
            //获取access_token
            $weibo_post = parseSignedRequest($_POST['css']);
            //获取微博名称
            $weibo_restr = file_get_contents('https://api.weibo.com/2/users/show.json?uid=' . $this -> _post('id') . '&access_token=' . $weibo_post['oauth_token'] . '');
            $weibo_result = json_decode($weibo_restr, true);

            $data = array();
            $data['type'] = $this -> _post('type');
            $data['content'] = $this -> _post('id');
            $data['addtime'] = time();
            $data['name'] = $weibo_result['screen_name'];

            if($id = $User -> add($data)){

                //新建统计数据
                $Num = M('Num');
                $data_num = array();
                $data_num['uid'] = $id;
                $data_num['sum'] = 0;
                $Num -> add($data_num);
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

    public function check_tel(){
        $User = M('User');
        $where = array();
        $where['type'] = $this -> _post('type');
        $where['content'] = $this -> _post('id');
        $result = $User -> field('id') -> where($where) -> find();
        $return_result = array();
        //没有数据则新建数据
        if(!$result){

            $data = array();
            $data['type'] = $this -> _post('type');
            $data['content'] = $this -> _post('id');
            $data['addtime'] = time();
            $data['name'] = $this -> _post('id');

            if($id = $User -> add($data)){

                //新建统计数据
                $Num = M('Num');
                $data_num = array();
                $data_num['uid'] = $id;
                $data_num['sum'] = 0;
                $Num -> add($data_num);
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

    public function downqr(){
        $fileName="Qr.png";
        header("Content-Type: image/png");
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        $id = $this -> _get('id', 'intval');
        Vendor('phpqrcode.phpqrcode');
        $value="http://182.92.64.207/nokia_share/share/" . $id;
        $errorCorrectionLevel = "H";
        $matrixPointSize = "10";
        QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize);
        exit;
    }

    public function share(){

        //优惠码
        $code = str_replace('.', rand(10000000, 99999999), uniqid('', true));
        $this -> assign('code', $code);

        //记录优惠码
        $uid = $this -> _get('id', 'intval');
        $ShareCode = M('ShareCode');
        $data = array();
        $data['uid'] = $uid;
        $data['code'] = $code;
        $data['addtime'] = time();
        if(!$ShareCode -> add($data)){
            $this -> show('<h1>此用户不存在！</h1>');
            return;
        }

        //帮友排行
        $Num = M('Num');
        $user_rank = $Num -> alias('n') -> field('u.name as uname') -> join('nokia_user as u ON n.uid = u.id') -> order('n.sum DESC,u.addtime ASC') -> limit(5) -> select();

        foreach($user_rank as $key => $value){
            $user_rank[$key]['uname'] = cut_str($value['uname'], 1, 0).'****'.cut_str($value['uname'], 1, -1);
        }
        $this -> assign('user_rank', $user_rank);
        $this -> display();
    }

    public function serververification(){
        if(!empty($_POST['code'])){
            //监测验证码是否有效
            $ShareCode = M('ShareCode');
            $where = array();
            $where['c.code'] = $_POST['code'];
            $result = $ShareCode -> alias('c') -> field('c.id,u.name as uname,c.code,c.status,c.orderid,c.addtime,c.checktime') -> where($where) -> join('nokia_user as u ON c.uid = u.id') -> find();
            $this -> assign('result', $result);
        }
        $this -> display();
    }

    public function updateinfo(){
        $id = $this -> _post('id', 'intval');
        $orderid = $this -> _post('orderid');
        $ShareCode = M('ShareCode');
        $Num = M('Num');

        //更新验证码数据
        $data_sharecode = array();
        $data_sharecode['id'] = $id;
        $data_sharecode['orderid'] = $orderid;
        $data_sharecode['status'] = 2;
        $data_sharecode['checktime'] = time();
        if(!$ShareCode -> save($data_sharecode)){
            echo 2;
            return;
        }

        //读取此验证码所属用户
        $uid = $ShareCode -> getFieldByid($id, 'uid');

        //更新统计数据
        $where_num = array();
        $where_num['uid'] = $uid;
        if(!$Num -> where($where_num) -> setInc('sum')){
            echo 2;
            return;
        }
        echo 1;
    }

    public function query_rank(){
        //计算排名和购买数量
        $con = $_POST['con'];

        $result = array();

        $User = M('User');
        //获取ID
        $where = array();
        $where['content'] = $con;
        $id_arr = $User -> field('id') -> where($where) -> find();
        if(!$id_arr){
            $return_result['status'] = 'error';
        }else{
            $uid = $id_arr['id'];
            $Num = M('Num');
            //购买数量
            $buy_num = $Num -> getFieldByuid($uid, 'sum');
            //获取排名
            //先计算比自己数量多的人的数量
            $where = array();
            $where['sum'] = array('GT', $buy_num);
            $rank_1 = $Num -> where($where) -> count();
            //再按照ID的顺序倒序排列
            $where = array();
            $where['uid'] = array('ELT', $uid);
            $where['sum'] = $buy_num;
            $rank_2 = $Num -> where($where) -> count();
            $rank = $rank_1 + $rank_2;

            $return_result['status'] = 'success';
            $return_result['buy_num'] = $buy_num;
            $return_result['rank'] = $rank;
        }

        echo json_encode($return_result);
    }


    public function wechat(){
        $token_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . C('WECHAT_APPID') . '&redirect_uri=' . C('WECHAT_REDIRECT_URI') . '&response_type=code&scope=snsapi_base&state=index#wechat_redirect';
        echo $token_url;

    }

    public function wechat_index(){
    }
}