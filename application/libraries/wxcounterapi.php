<?php
    class wxcounterApi {


        public function valid(){
            $echoStr = $_GET["echostr"];

            //valid signature , option
            if($this->checkSignature()){
                echo $echoStr;
                exit;
            }
        }

        public function getObj(){

        //CI微信兼容代码
        $postStr  = file_get_contents("php://input");
        
        /*
        $postStr = "<xml>
                     <ToUserName><![CDATA[toUser]]></ToUserName>
                     <FromUserName><![CDATA[o2Z2ujmVokqryU3ERjEjPMDCkk80]]></FromUserName> 
                     <CreateTime>1348831860</CreateTime>
                     <MsgType><![CDATA[text]]></MsgType>
                     <Content><![CDATA[adminlistall]]></Content>
                     <MsgId>1234567890123456</MsgId>
                     </xml>";
        
    

        /*
        /*
        $postStr = "<xml>
                 <ToUserName><![CDATA[toUser]]></ToUserName>
                 <FromUserName><![CDATA[fromUser]]></FromUserName>
                 <CreateTime>1348831860</CreateTime>
                 <MsgType><![CDATA[image]]></MsgType>
                 <PicUrl><![CDATA[http://1.su.bdimg.com/all_skin/19.jpg]]></PicUrl>
                 <MsgId>1234567890123456</MsgId>
                 </xml>";
        

        
        /*
        $postStr ="<xml><ToUserName><![CDATA[toUser]]></ToUserName>
        <FromUserName><![CDATA[FromUser]]></FromUserName>
        <CreateTime>123456789</CreateTime>
        <MsgType><![CDATA[event]]></MsgType>
        <Event><![CDATA[EVENT]]></Event>
        <EventKey><![CDATA[EVENTKEY]]></EventKey>
        </xml>";
        */



            if (!empty($postStr)){
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                return $postObj;
            }else{
                return false;
            }
            
        }


        public function responseRoute(){
            if(($postObj = $this->getObj())){
                $MsgType = $postObj->MsgType;
                if($MsgType == "text"){
                    return $this->responseMsg();
                }else if($MsgType == "image"){
                    return $this->responseImg();
                }else if($MsgType == "event"){
                    return $this->responseSub();
                }
            }else{
                echo "No data recieved";
            }
        }
        



        public function responseMsg(){


            $postObj = $this->getObj();

            //extract post data
            if ($postObj){

                    $fromUsername = $postObj->FromUserName;
                    $toUsername = $postObj->ToUserName;
                    $keyword = trim($postObj->Content);
                    $time = time();
                    $textTpl = "<xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[%s]]></MsgType>
                                <Content><![CDATA[%s]]></Content>
                                <FuncFlag>0</FuncFlag>
                                </xml>";             
                    if(strtolower($keyword) == 'h'){
                        $this->help($fromUsername,$toUsername,$time);
                        return array(0,0);
                    }else{
                        return array(1,$keyword);
                    }

            }else {
                echo "";
                exit;
            }
        }


        public function responseSub(){
            $postObj = $this->getObj();
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $Event = $postObj->Event;
            $time = time();
                    
            
            $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";



            if(!empty($Event)){
                $this->help($fromUsername,$toUsername,$time);
                return array(0,0);
            }else{
                echo "Input something...sub";
            }
        }

            
        private function checkSignature(){
            $signature = $_GET["signature"];
            $timestamp = $_GET["timestamp"];
            $nonce = $_GET["nonce"];    
                    
            $token = TOKEN;
            $tmpArr = array($token, $timestamp, $nonce);
            sort($tmpArr);
            $tmpStr = implode( $tmpArr );
            $tmpStr = sha1( $tmpStr );
            
            if( $tmpStr == $signature ){
                return true;
            }else{
                return false;
            }
        }





        function help($fromUsername,$toUsername,$time){

                $textTpl = " <xml>
             <ToUserName><![CDATA[".$fromUsername."]]></ToUserName>
             <FromUserName><![CDATA[".$toUsername."]]></FromUserName>
             <CreateTime>".$time."</CreateTime>
             <MsgType><![CDATA[news]]></MsgType>
             <ArticleCount>3</ArticleCount>
             <Articles>
             

             <item>
             <Title><![CDATA[外卖小助-使用详解]]></Title>
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[http://mmsns.qpic.cn/mmsns/YnOAveZOTdI9sibSIbibXlHNlTHgQ6OsCbT6N1qRKONhsAuofEWicLfibQ/0]]></PicUrl>
             <Url><![CDATA[http://mp.weixin.qq.com/mp/appmsg/show?__biz=MjM5MzgwODM0NQ==&appmsgid=10000010&itemidx=2&sign=3b5e34cc72d01789ce4d14c3f9e2d9af#wechat_redirect]]></Url>
             </item>




             <item>
             <Title><![CDATA[【幸福便当:xf】【快乐便当:kl】【贴心便当:tx】【饭米粒便当:fml】【御膳房:ysf】【一号米便当:yhm】]]></Title>
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[]]></Url>
             </item>

             <item>
             <Title><![CDATA[回复h 或 H 查看帮助]]></Title>
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[http://www.baidu.com]]></Url>
             </item>


             </Articles>
             </xml> ";      

            echo $textTpl;
        }

        function showRes($Description,$title,$url=''){
            $postObj = $this->getObj();
            $time = time();

            $textTpl = " <xml>
             <ToUserName><![CDATA[".$postObj->FromUserName."]]></ToUserName>
             <FromUserName><![CDATA[".$postObj->ToUserName."]]></FromUserName>
             <CreateTime>".$time."</CreateTime>
             <MsgType><![CDATA[news]]></MsgType>
             <ArticleCount>1</ArticleCount>
             <Articles>

             <item>
             <Title><![CDATA[".$title."]]></Title> 
             <Description><![CDATA[".$Description."]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[".$url."]]></Url>
             </item>

             </Articles>
             </xml> "; 

             echo $textTpl;
             exit();
        }



        function showDeliver($title,$shopper,$price,$person){
            $postObj = $this->getObj();
            $time = time();

            $textTpl = " <xml>
             <ToUserName><![CDATA[".$postObj->FromUserName."]]></ToUserName>
             <FromUserName><![CDATA[".$postObj->ToUserName."]]></FromUserName>
             <CreateTime>".$time."</CreateTime>
             <MsgType><![CDATA[news]]></MsgType>
             <ArticleCount>5</ArticleCount>
             <Articles>

             <item>
             <Title><![CDATA[".$title."]]></Title> 
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[]]></Url>
             </item>

             <item>
             <Title><![CDATA[商家：".$shopper."]]></Title> 
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[]]></Url>
             </item>

             <item>
             <Title><![CDATA[价格：".$price."]]></Title> 
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[]]></Url>
             </item>

             <item>
             <Title><![CDATA[递送员：".$person."]]></Title> 
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[]]></Url>
             </item>

             <item>
             <Title><![CDATA[递送时间：".date("Y-m-d H:i:s D")."]]></Title> 
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[]]></Url>
             </item>

             </Articles>
             </xml> "; 

             echo $textTpl;
             exit();
        }




        //早晚会有用
        function showList($listAll){

            $postObj = $this->getObj();
            $time = time();

            $userCounter = (int)(count($listAll));
            //$cc = 10;
            $userCounter = 10;
            

            $Content = '';

            $line = "

             <item>
                 <Title><![CDATA[%s]]></Title> 
                 <Description><![CDATA[]]></Description>
                 <PicUrl><![CDATA[]]></PicUrl>
                 <Url><![CDATA[]]></Url>
             </item>

             ";


            foreach ($listAll as $key1 => $value1) {
                $description = '';
                foreach ($value1 as $key2 => $value2) {
                    if($key2 == 'name'){
                        $description .= '【姓名:'.$value2.'】';
                    }else{    
                        $description .= '【'.$key2.':'.$value2.'元】';
                    }
                }

                $Content .= sprintf($line,$description);

            }


            $textTpl = " <xml>
             <ToUserName><![CDATA[".$postObj->FromUserName."]]></ToUserName>
             <FromUserName><![CDATA[".$postObj->ToUserName."]]></FromUserName>
             <CreateTime>".$time."</CreateTime>
             <MsgType><![CDATA[news]]></MsgType>
             <ArticleCount>".$userCounter."</ArticleCount>
             <Articles>

             <item>
             <Title><![CDATA[今日对账单  10:00~20:00]]></Title> 
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[]]></Url>
             </item>

             %s

             </Articles>
             </xml> "; 

             $textTpl = sprintf($textTpl,$Content);

             echo $textTpl;
             exit();
        }




    }

?>