<?php
	
	class Mwxc extends CI_Model {

        function __construct(){
            parent::__construct();
        }


        function addPrice($cmd,$price,$wxid){
        	$data = array(
        		'price' => $price,
        		'restaurant' => $cmd,
        		'wxid' => $wxid,
        	);

        	$query = $this->db->insert('deliver',$data);

        	if($query){
        		$data['id'] = $this->db->insert_id();
        		return $data;
        	}else{
        		return false;
        	}

        }


        function listAll(){

            $wxid_set = $this->db->get_where('user',array('status' => 1));
            $wxid_set = $wxid_set->result_array();

            $listAll = array();
            foreach ($wxid_set as $key => $value) {
                $temp = $this->totaltoday($value['wxid']);
                $temp['name'] = $value['name'];
                $listAll[] = $temp;
            }

            return $listAll;
            
        }

        function totaltoday($wxid){

            //时间限制
            $lunchS = strtotime(date("Y-m-d 10:00:00"));
            //$lunchE = strtotime(date("Y-m-d 14:00:00"));


            //$dinnerS = strtotime(date("Y-m-d 16:00:00"));
            $dinnerE = strtotime(date("Y-m-d 20:00:00"));


            $data = array(
              'wxid' => $wxid,
              'UNIX_TIMESTAMP(ctime)>' => $lunchS,
              'UNIX_TIMESTAMP(ctime)<' => $dinnerE,
            );



            $restaurants = $this->get_restaurant_list();

            $arraySum = array();

            foreach ($restaurants as $key => $value) {

                
                $data['restaurant'] = $value['abbr'];

                $query = $this->db->select_sum('price',$value['name'])->get_where('deliver',$data);



                $resQuery = $query->row_array();


                $arraySum[$value['name']] = sprintf('%01.2f',$resQuery[$value['name']]);
            }

            
            $data = array(
              'wxid' => $wxid,
              'UNIX_TIMESTAMP(ctime)>' => $lunchS,
              'UNIX_TIMESTAMP(ctime)<' => $dinnerE,
            );

            $query = $this->db->select_sum('price','total')->get_where('deliver',$data);

            $restotal = $query->row_array();

            $arraySum['total'] = sprintf('%01.2f',$restotal['total']);

            return $arraySum;

        }

        function admintoday(){
            //时间限制
            $lunchS = strtotime(date("Y-m-d 10:00:00"));
            //$lunchE = strtotime(date("Y-m-d 14:00:00"));


            //$dinnerS = strtotime(date("Y-m-d 16:00:00"));
            $dinnerE = strtotime(date("Y-m-d 19:00:00"));


            $data = array(
              'UNIX_TIMESTAMP(ctime)>' => $lunchS,
              'UNIX_TIMESTAMP(ctime)<' => $dinnerE,
            );



            $restaurants = $this->get_restaurant_list();

            $arraySum = array();

            foreach ($restaurants as $key => $value) {

                
                $data['restaurant'] = $value['abbr'];

                $query = $this->db->select_sum('price',$value['name'])->get_where('deliver',$data);



                $resQuery = $query->row_array();


                $arraySum[$value['name']] = sprintf('%01.2f',$resQuery[$value['name']]);
            }

            
            $data = array(
              'UNIX_TIMESTAMP(ctime)>' => $lunchS,
              'UNIX_TIMESTAMP(ctime)<' => $dinnerE,
            );

            $query = $this->db->select_sum('price','total')->get_where('deliver',$data);

            $restotal = $query->row_array();

            $arraySum['total'] = sprintf('%01.2f',$restotal['total']);

            return $arraySum;
        }

        function get_restaurant_list(){
            $query = $this->db->get('restaurant');
            return $query->result_array();
        }

        function submitwxid($wxid,$name){
            $data = array(
                'wxid' => $wxid,
                'name' => $name,
            );

            $query = $this->db->insert('wxids',$data);

            return $query;

        }

        function delPrice($id,$wxid){
        	$data = array(
       			'id' => $id,
       			'wxid' => $wxid,
       		);

        	$query = $this->db->delete('deliver',$data);

        	return $query;
        }

       	function checkOwner($id,$wxid){
       		$data = array(
       			'id' => $id,
       			'wxid' => $wxid,
       		);

       		$query = $this->db->get_where('deliver',$data);

       		if($query->num_rows() == 0){
       			return false;
       		}else{
       			return true;
       		}
       	}

       	function checkTime($id,$wxid){

       		$data = array(
       			'id' => $id,
       			'wxid' => $wxid,
       		);

       		$query = $this->db->get_where('deliver',$data);

       		$res = $query->row_array();
       		$old_time = strtotime($res['ctime']);
       		$time = time();

       		if($time - $old_time > 3600*24){
       			return false;
       		}else{
       			return true;
       		}

       	}


    }

?>