<?php
    class wxcounter {

        public $base_url = '';

        public function setURL($base_url){
            $this->base_url = $base_url;
        }

        public function valid()
        {
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
                     <FromUserName><![CDATA[fromUser]]></FromUserName> 
                     <CreateTime>1348831860</CreateTime>
                     <MsgType><![CDATA[text]]></MsgType>
                     <Content><![CDATA[kb11083131]]></Content>
                     <MsgId>1234567890123456</MsgId>
                     </xml>";
        
    

        */
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
                        echo "Input something...";
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

            
        public function responseImg(){
            $postObj = $this->getObj();
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->PicUrl);
            $time = time();

            return array(1,$postObj);
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

            $loginUrl = $this->base_url.'index.php/user/wxapi/signin?wxid='.$fromUsername;
            $addrurl = $this->base_url.'index.php/user/wxapi/editaddr?wxid='.$fromUsername;

                $textTpl = " <xml>
             <ToUserName><![CDATA[".$fromUsername."]]></ToUserName>
             <FromUserName><![CDATA[".$toUsername."]]></FromUserName>
             <CreateTime>".$time."</CreateTime>
             <MsgType><![CDATA[news]]></MsgType>
             <ArticleCount>6</ArticleCount>
             <Articles>

             <item>
             <Title><![CDATA[lomo让生活开放有魔力]]></Title> 
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[http://mmsns.qpic.cn/mmsns/u5icfl2nVMNxySInLUofoqiauQyTd68FGxFk7ibFaYUwMXTXTYwxichyiaA/0]]></PicUrl>
             <Url><![CDATA[http://mp.weixin.qq.com/mp/appmsg/show?__biz=MjM5MjczNjU4NQ==&appmsgid=10000002&itemidx=1&sign=83a9cd2caa73bf89175afb49e20899fb#wechat_redirect]]></Url>
             </item>
             

             <item>
             <Title><![CDATA[微信拍立得-使用详解]]></Title>
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[http://mp.weixin.qq.com/mp/appmsg/show?__biz=MjM5MjczNjU4NQ==&appmsgid=10000002&itemidx=2&sign=32a98d76aad563fd7a8bf8d2db34341e#wechat_redirect]]></Url>
             </item>
             
             <item>
             <Title><![CDATA[点击此处绑定ffzy.me帐号]]></Title>
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[".$loginUrl."]]></Url>
             </item>

             <item>
             <Title><![CDATA[点击此处修改收货地址]]></Title>
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[".$addrurl."]]></Url>
             </item>


             <item>
             <Title><![CDATA[团队求贤]]></Title>
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[http://mp.weixin.qq.com/mp/appmsg/show?__biz=MjM5MjczNjU4NQ==&appmsgid=10000002&itemidx=4&sign=f2a3fb2641dedd9be12ffc5bd580d1f5#wechat_redirect]]></Url>
             </item>

             <item>
             <Title><![CDATA[输入h查看帮助]]></Title>
             <Description><![CDATA[]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[]]></Url>
             </item>

             </Articles>
             </xml> ";      

            $resultStr = $textTpl;
            echo $resultStr;
        }


        function loginRoute($Description,$Url){

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
             <Title><![CDATA[微信拍立得小助手]]></Title> 
             <Description><![CDATA[".$Description."]]></Description>
             <PicUrl><![CDATA[]]></PicUrl>
             <Url><![CDATA[".$Url."]]></Url>
             </item>

             </Articles>
             </xml> "; 

             echo $textTpl;

        }

        function imgOrder($Description,$Url){

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
             <Title><![CDATA[你的lomo卡片出炉啦]]></Title> 
             <Description><![CDATA[".$Description."]]></Description>
             <PicUrl><![CDATA[".$Url."]]></PicUrl>
             <Url><![CDATA[]]></Url>
             </item>

             </Articles>
             </xml> "; 

             echo $textTpl;

        }




    }

?>