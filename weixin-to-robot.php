<?php
/**
  * 微信链接图灵小工具
  * 文章地址：https://veryer.org/weixin-to-robot.html
  */

//define your token
define("TOKEN", "这里填写微信公众号平台TOKEN");
define("TULINGKEY","这里填写图灵机器人开放平台的key");//调用图灵机器人http://www.tuling123.com
$wechatObj = new wechatCallbackapiTest();

//判断是否为验证，如果是验证调用验证方法，否则调用消息相应方法
if(isset($_GET["echostr"])){
	$wechatObj->valid();
}else{
	$wechatObj->responseMsg();
}

class wechatCallbackapiTest
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }
	/**
	 * 消息处理
	 */
    public function responseMsg()
    {
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
				$msgType = $postObj->MsgType;
				switch ($msgType){//判断接收的微信消息的类型，根据不同类型调用不同方法
					case "text":
						$resultStr =$this->postText($postObj);
						break;
					case "image":
						$resultStr =$this->postImage($postObj);
						break;
					case "voice":
						$resultStr =$this->postVoice($postObj);
						break;
					case "video":
						$resultStr =$this->postVideo($postObj);
						break;
					case "shortvideo":
						$resultStr =$this->postShortvideo($postObj);
						break;
					case "location":
						$resultStr =$this->postLocation($postObj);
						break;
					default:
						$resultStr =$this->postLink($postObj);
						break;
				}
				echo $resultStr;
        }else {
        	echo "";
        	exit;
        }
    }
	/**
	 *
	 * 验证TOKEN
	 */
	private function checkSignature()
	{
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	* 返回消息
	*
	*/
	private function returnMsg($object,$textTpl,$msgType,$contentStr){
		$fromUsername = $object->FromUserName;
        $toUsername = $object->ToUserName;
		$time = time();
		$resultStr = sprintf($textTpl,$fromUsername,$toUsername,$time,$msgType, $contentStr);
		return $resultStr;
	}
	/**
	 * text消息类型
	 *
	 */
	private function postText($object){
		$keyword = trim($object->Content);
		$contentStr = "";
		$msgType = "text";
		$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";             
		$get_data = $this->send_post($object);
		$tulingCode = $get_data->code;
		switch ($tulingCode){
			case 100000://文本类
				$contentStr = $get_data->text;
				break;
			case 200000://链接类
				$contentStr = $get_data->text."：<a href='".$get_data->url."'>点我查看</a>";
				break;
			case 302000://新闻类
				$newsList = $get_data->list;
				$contentStr = $get_data->text."\n";
				for($n=0;$n<count($newsList);$n++){
					$l=$n+1;
					$contentStr .= "\n <a href='".$newsList[$n]->detailurl."'>".$l."、".$newsList[$n]->article."『".$newsList[$n]->source."』</a>";
				}
				break;
			case 308000://菜谱类
				$caiList = $get_data->list;
				$contentStr = $get_data->text."\n";
				for($n=0;$n<count($caiList);$n++){
					$contentStr .= "\n".$caiList[$n]->name.":\n".$caiList[$n]->info."\n <a href='".$caiList[$n]->detailurl."'>查看详情</a> \n";
				}
				break;
			default:
				$contentStr = "纳尼，你在说什么呢，我听不懂。我还在学习中呢";
				break;
		}
        return $this->returnMsg($object,$textTpl,$msgType,$contentStr);
	}
	/**
	 * 图片消息类型
	 *
	 */
	private function postImage($object){
		$keyword = trim($object->Content);
		$contentStr = "这张图片好漂亮呀!";
		$msgType = "text";
		$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";             
		
        return $this->returnMsg($object,$textTpl,$msgType,$contentStr);
	}
	/**
	 * 语音消息类型
	 *
	 */
	private function postVoice($object){
		$keyword = trim($object->Content);
		$contentStr = "太感动了，我已经听到你声音了！";
		$msgType = "text";
		$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";   
        return $this->returnMsg($object,$textTpl,$msgType,$contentStr);
	}
	
	/**
	 * 视频消息类型
	 *
	 */
	private function postVideo($object){
		$keyword = trim($object->Content);
		$contentStr = "我不是土豪，手机流量不够了，回头再看吧！";
		$msgType = "text";
		$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";   
        return $this->returnMsg($object,$textTpl,$msgType,$contentStr);
	}
	
	/**
	 * 小视频消息类型
	 *
	 */
	private function postShortvideo($object){
		$keyword = trim($object->Content);
		$contentStr = "我不是土豪，手机流量不够了，回头再看吧！";
		$msgType = "text";
		$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";   
        return $this->returnMsg($object,$textTpl,$msgType,$contentStr);
	}
	
	/**
	 * 位置消息类型
	 *
	 */
	private function postLocation($object){
		$keyword = trim($object->Content);
		$contentStr = "原来在在这里呀，我也在这附近……";
		$msgType = "text";
		$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";   
        return $this->returnMsg($object,$textTpl,$msgType,$contentStr);
	}
	/**
	*调用图灵机器人http://www.tuling123.com
	*
	*/
	private function send_post($object) { 
		$keyword = trim($object->Content);
		$fromUserName = $object->FromUserName;
		$url = "http://www.tuling123.com/openapi/api";//图灵机器人接口api
		$post_data = array(
			'key' => TULINGKEY,
			'info' => $keyword,
			'userid' => $fromUserName //使用微信关注者的openid作为userid，实现支持上下文
		);
      $postdata = http_build_query($post_data);    
      $options = array(    
            'http' => array(    
                'method' => 'POST',    
                'header' => 'Content-type:application/x-www-form-urlencoded',    
                'content' => $postdata,    
                'timeout' => 15 * 60 // 超时时间（单位:s）    
            )    
        );    
        $context = stream_context_create($options);    
        $result = file_get_contents($url, false, $context);             
        return json_decode($result);    
	}
}

?>
