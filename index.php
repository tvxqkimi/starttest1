valid();
}else{                     //回复消息
    $wechatObj->responseMsg();
}
 
class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
 
    private function checkSignature()
    {
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
 
    //回复消息
    public function responseMsg()
    {
    $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
    if (!empty($postStr)){
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $RX_TYPE = trim($postObj->MsgType);
 
        switch ($RX_TYPE)
        {
            case "text":
                $resultStr = $this->receiveText($postObj);
                break;
            case "image":
                $resultStr = $this->receiveImage($postObj);
                break;
            case "location":
                $resultStr = $this->receiveLocation($postObj);
                break;
            case "voice":
                $resultStr = $this->receiveVoice($postObj);
                break;
            case "video":
                $resultStr = $this->receiveVideo($postObj);
                break;
            case "link":
                $resultStr = $this->receiveLink($postObj);
                break;
            case "event":
                $resultStr = $this->receiveEvent($postObj);
                break;
            default:
                $resultStr = "unknow msg type: ".$RX_TYPE;
                break;
        }
        echo $resultStr;
    }else {
        echo "";
        exit;
    }
    }
     
    //接收文本消息
    private function receiveText($object)
    {
        $keyword = trim($object->Content);
        $url = "http://api100.duapp.com/movie/?appkey=DIY_miaomiao&name=".$keyword;
        $output = file_get_contents($url,$keyword);
        $contentStr = json_decode($output, true);
        if (is_array($contentStr)){
            $resultStr = $this->transmitNews($object, $contentStr);
        }else{
            $resultStr = $this->transmitText($object, $contentStr);
        }
        return $resultStr;
    }
 
     
    //接收事件，关注等
    private function receiveEvent($object)
    {
        $contentStr = "";
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = "你关注了我";    //关注后回复内容
                break;
            case "unsubscribe":
                $contentStr = "";
                break;
            case "CLICK":
                $contentStr =  $this->receiveClick($object);    //点击事件
                break;
            default:
                $contentStr = "receive a new event: ".$object->Event;
                break;
        }
         
        return $contentStr;
    }
     
    //接收图片
    private function receiveImage($object)
    {
        $contentStr = "你发送的是图片，地址为：".$object->PicUrl;
        $resultStr = $this->transmitText($object, $contentStr);
        return $resultStr;
    }
     
     
    //接收语音
    private function receiveVoice($object)
    {
        $contentStr = "你发送的是语音，媒体ID为：".$object->MediaId;
        $resultStr = $this->transmitText($object, $contentStr);
        return $resultStr;
    }
     
    //接收视频
    private function receiveVideo($object)
    {
        $contentStr = "你发送的是视频，媒体ID为：".$object->MediaId;
        $resultStr = $this->transmitText($object, $contentStr);
        return $resultStr;
    }
     
    //位置消息
    private function receiveLocation($object)
    {
        $contentStr = "你发送的是位置，纬度为：".$object->Location_X."；经度为：".$object->Location_Y."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $resultStr = $this->transmitText($object, $contentStr);
        return $resultStr;
    }
     
    //链接消息
    private function receiveLink($object)
    {
        $contentStr = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $resultStr = $this->transmitText($object, $contentStr);
        return $resultStr;
    }
     
    
//点击菜单消息 private function receiveClick($object) { switch ($object->EventKey) { case "1": $contentStr = "猫咪酱个性DIY服装， 我们专业定制个性【班服，情侣装，亲子装等，有长短T恤，卫衣，长短裤】 来图印制即可，给你温馨可爱的TA， 有事可直接留言微信"; break; case "2": $contentStr = "你点击了菜单: ".$object->EventKey; break; case "3": $contentStr = "是傻逼"; break; default: $contentStr = "你点击了菜单: ".$object->EventKey; break; } //两种回复 if (is_array($contentStr)){ $resultStr = $this->transmitNews($object, $contentStr); }else{ $resultStr = $this->transmitText($object, $contentStr); } return $resultStr; } //回复文本消息 private function transmitText($object, $content) { $textTpl = " %s "; $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content); return $resultStr; }

//回复图文 private function transmitNews($object, $arr_item) { if(!is_array($arr_item)) return;

$itemTpl = " "; $item_str = ""; foreach ($arr_item as $item) $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);

$newsTpl = " %s %s $item_str ";

$resultStr = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item)); return $resultStr; } //音乐消息 private function transmitMusic($object, $musicArray, $flag = 0) { $itemTpl = " ";

$item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);

$textTpl = " %s $item_str %d ";

$resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $flag); return $resultStr; } } ?>