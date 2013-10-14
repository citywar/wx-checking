<?php
    class Wxcounter extends CI_Controller{

    	public $base_url;
    	public $wechatObj;
        public $username;
        public $wxid;

    	function __construct(){
            @session_start ();
            parent::__construct();
            $this->base_url = $this->config->item('base_url');

            //设置编码
            header ( 'Content-Type: text/html; charset=UTF-8' );
            //define your token
			define("TOKEN", "wxcc");

            //加载url辅助类
            $this->load->helper('url');

            //加载数据库类
            $this->load->model('mwxc','mwxc');

			//加载api类库
			$this->load->library('wxcounterapi');
			$this->wechatObj = new wxcounterApi();

        }   

        function index(){
			//$this->wechatObj->valid();

            $resCmd = $this->wechatObj->responseRoute();
            if($resCmd[0]){
                $this->cmd($resCmd[1]);
            }
        }   


        function cmd($cmdLine){
            $postObj = $this->wechatObj->getObj();
            $wxid = (string)($postObj->FromUserName);


            $regex = '/^([a-z]+)([0-9\.]+)?/i';
            preg_match($regex, $cmdLine, $matches);

            $cmd = strtolower($matches[1]);

            if($cmd == 'submitwxid'){

                $regex = '/^([a-z]+)(.+)?/i';
                preg_match($regex, $cmdLine, $matches);

                $name = $matches[2];

                if($this->mwxc->submitwxid($wxid,$name)){

                    $this->wechatObj->showRes('已经提交管理员审核，稍等片刻','信息通知');
                }else{
                    $this->wechatObj->showRes('提交审核失败','信息通知');
                }
            }elseif ($cmd == 'rootpld') {
                $arrayRoot = array(
                    'o2Z2ujmVokqryU3ERjEjPMDCkk80',  //plusman
                ); 

                if(in_array($wxid, $arrayRoot)){

                    $this->db->empty_table('user');

                    $queryWxids = $this->db->get('wxids');
                    $wxids = $queryWxids->result_array();

                    foreach ($wxids as $key => $value) {
                        $data = array(
                            'name' => $value['name'],
                            'wxid' => $value['wxid'],
                        );

                        $this->db->insert('user',$data);
                    }

                    $this->wechatObj->showRes('rootpld命令执行成功','Root');

                }


            } else{

                $resUser = $this->check_user_exist($wxid);
                if($resUser){
                    $this->username = $resUser['name'];
                    $this->wxid = $wxid;
                }else{
                    $title = '非法用户';
                    $description = $wxid.'为非法用户';
                    $this->wechatObj->showRes($description,$title);
                }
            }


            if($cmd == 'rm'){
                $deliverid = $matches[2];


                if(!$this->mwxc->checkOwner($deliverid,$this->wxid)){

                    $this->wechatObj->showRes('请尊重你的职业道德，请勿删除他人送货单','警告!!!');

                }elseif (!$this->mwxc->checkTime($deliverid,$this->wxid)) {

                    $this->wechatObj->showRes('只能删除24小时内订单','提示');

                }elseif($this->mwxc->delPrice($deliverid,$this->wxid)){
                    $description = '递送单编号为'.$deliverid.'删除成功';
                    $this->wechatObj->showRes($description,'错误订单删除成功');
                }  

                
            }elseif($cmd =='totaltoday'){
                $resTotal = $this->mwxc->totaltoday($wxid);
                if($resTotal){
                    $title = '今日个人统计：10:00~20:00';

                    $description = '';
                    foreach ($resTotal as $key => $value) {
                        $description .= '【'.$key.':'.$value.'元】';
                    }

                    $this->wechatObj->showRes($description,$title);
                }

            }elseif ( $cmd == 'admintoday') {
                $arrayAdmin = array(
                    'o2Z2ujmVokqryU3ERjEjPMDCkk80',  //plusman
                    'o2Z2ujmT2JRzNPNmGJqnGw6WxbuQ', //张波
                    'o2Z2ujgrI1dwa6F_LnvjOkCrbt_0', //方勤
                );

                if(in_array($wxid, $arrayAdmin)){
                    $resTotal = $this->mwxc->admintoday();
                    $title = '今日总体统计：10:00~20:00';

                    $description = '';
                    foreach ($resTotal as $key => $value) {
                        $description .= '【'.$key.':'.$value.'元】';
                    }

                    $this->wechatObj->showRes($description,$title);

                }else{
                    $this->wechatObj->showRes('对不起，您没有管理员权限','消息');
                }


            }elseif ($cmd == 'adminlistall') {
                $arrayAdmin = array(
                    'o2Z2ujmVokqryU3ERjEjPMDCkk80',  //plusman
                    'o2Z2ujmT2JRzNPNmGJqnGw6WxbuQ', //张波
                    'o2Z2ujgrI1dwa6F_LnvjOkCrbt_0', //方勤
                );

                if(in_array($wxid, $arrayAdmin)){

                    $url = $this->base_url.'index.php/wxcounter/showlist?wxid='.$wxid;

                    $this->wechatObj->showRes('点此查看今日详单','消息Admin',$url);

                }else{
                    $this->wechatObj->showRes('对不起，您没有管理员权限','消息');
                }



            } else{
                $price = $matches[2];

                $checkRes = $this->check_restaurant_exist($cmd);
                if($checkRes[0]){
                    $resAddPrice = $this->mwxc->addPrice($cmd,$price,$wxid);

                    if($resAddPrice){
                        $title = '送货单编号：'.$resAddPrice['id'];
                        $this->wechatObj->showDeliver($title,$checkRes[1]['name'],$price,$this->username);

                    }else{
                        $title = '错误消息';
                        $description = '添加失败';
                        $this->wechatObj->showRes($description,$title);
                    }

                }else{
                    $this->wechatObj->showRes('不存在编号为'.$cmd.'的餐厅','错误消息');
                }
            }

        }

        function check_user_exist($wxid){
            $query = $this->db->get_where('user',array('wxid'=>$wxid));
            if($query->num_rows() == 0){
                return false;
            }else{
                return $query->row_array();
            }
        }

        function check_restaurant_exist($cmd){
            $query = $this->db->get_where('restaurant',array('abbr'=>$cmd));

            if($query->num_rows() == 0){
                return array(false,false);
            }else{
                return array(true,$query->row_array());
            }
        }


        function showList(){

            $wxid = $this->input->get('wxid');
            //$wxid = 'o2Z2ujmVokqryU3ERjEjPMDCkk80';

            $arrayAdmin = array(
                'o2Z2ujmVokqryU3ERjEjPMDCkk80',  //plusman
                'o2Z2ujmT2JRzNPNmGJqnGw6WxbuQ', //张波
                'o2Z2ujgrI1dwa6F_LnvjOkCrbt_0', //方勤
            );

            if(in_array($wxid, $arrayAdmin)){
                $listAll = $this->mwxc->listAll();
            }


            $data = array(
                'base_url' => $this->base_url,
                'listAll' =>$listAll,
                'num' => count($listAll),
            );

            $this->load->view('showlist',$data);
        }

        
    }


?>