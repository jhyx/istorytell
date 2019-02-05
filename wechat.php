<?php
define("TOKEN", "mytoken");

// 1 判断请求方法，get请求一般为消息验证,post为其他消息交互
// 2 验证signature是否正确(消息来自微信服务器)
$handler = new WeixinHandler();
$reqMethod = strtolower($_SERVER["REQUEST_METHOD"]);
if ("get" == $reqMethod && !empty($_GET["echostr"])) {
    if ($handler->isValid()) {
        $echostr = $_GET["echostr"];
        echo $echostr;
        exit();
    }
} else {
    //判断消息类型，返回"你发送的是xxx消息"
    $handler->responseMessage();
}

class WeixinHandler
{

    function checkSignature()
    {
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $tmpArr = array(
            TOKEN,
            $timestamp,
            $nonce
        );
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr) {
            return $tmpStr;
        } else {
            return "";
        }
    }

    function isValid()
    {
        $signature = $_GET["signature"];
        if ($signature == $this->checkSignature()) {
            return true;
        } else {
            return false;
        }
    }

    function responseMessage(){
        $defaultMsgType="text";
        //从请求数据获取FromUserName和ToUserName以及消息类型
        $postStr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
        if(!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            //发送方账号（openId）
            $fromUsername = $postObj->FromUserName;
            //开发者微信号
            $toUsername = $postObj->ToUserName;
            //消息类型
            $MsgType = strtolower($postObj->MsgType);
            //消息内容
            $keyword = trim($postObj->Content);
            $typeResult="";
            $resultStr="";
            if("text"==$MsgType){
                $typeResult="你发送的是文本消息";
            }else if("image"==$MsgType){
                $typeResult="你发送的是图片消息";
            }else if("voice"==$MsgType){
                $typeResult="你发送的是语音消息";
            }else if("video"==$MsgType){
                $typeResult="你发送的是视频消息";
            }else if("location"==$MsgType){
                $typeResult="你发送的是地理位置消息";
            }else if("link"==$MsgType){
                $typeResult="你发送的是链接消息";
            }else if("event"==$MsgType){
                //事件推送处理
                $typeResult="事件推送消息";
            }else{
                $typeResult="你发送的是其他类型的消息";
            }
            if("text"==$defaultMsgType){
                $time = time(); //时间戳
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[%s]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
$resultStr=sprintf($textTpl, $fromUsername, $toUsername, $time, $defaultMsgType, $typeResult);
            }
            echo $resultStr;
        }else{
            echo "";
            exit;
        }
    }
}
?> 