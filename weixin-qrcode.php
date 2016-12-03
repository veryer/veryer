<?php 

define("SCENE_ID", "这里填写需要的sceneid");	
define("APPID", "这里填写开发者应用appid");
define("APPSECRET", "这里填写开发者应用appsecret");

$wxobj=new CreateWechatQrcode();
$wxobj->qrcode(SCENE_ID,APPID,APPSECRET);


class CreateWechatQrcode{
	public function qrcode($scene_id,$appid,$appsecret){
		//获取access_token
		$access_token=$this->getToken($appid,$appsecret);
		//获取ticket
		$ticket=$this->getTicket($access_token,$scene_id);
		//获取二维码图片地址url
		$qrcodeimg=$this->getQrcode($ticket)
		echo $qrcodeimg;
		//echo "<img src='".$this->getQrcode($ticket)."'/>";
	}
	//获取access_token123
	private function getToken($appid,$appsecret){
		$token_access_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
		$res = file_get_contents($token_access_url); //获取文件内容或获取网络请求的内容
		//echo $res;
		$result = json_decode($res, true); //接受一个 JSON 格式的字符串并且把它转换为 PHP 变量
		$access_token = $result['access_token'];
		return $access_token;
	}
	/**
	*
	*  http请求方式: POST
	*  URL: https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKENPOST数据格式：json
	*  POST数据例子：{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}
	*  sessionid作为scene_id
	*  临时二维码或永久二维码根据微信开发文档修改相应的字段
	*/
	private function getTicket($access_token,$scene_id){
		$url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;
		$data = '{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
		$ch = curl_init();
        $header = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            //curl_close( $ch )
			$result = json_decode($ch, true); 
            return $ch;
        }else{
            //curl_close( $ch ) 
			$result = json_decode($tmpInfo, true); 
            return $tmpInfo;
        }
        curl_close( $ch ) ;
		
		
	}
	//HTTP GET请求（请使用https协议）https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=TICKET提醒：TICKET记得进行UrlEncode
	private function getQrcode($ticket){
		return "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$ticket;
	}
}


