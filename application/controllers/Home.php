<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library('pagination');
		$this->load->helper('url');
	}

	public function index()
	{
		$this->load->view('home');
	}

	public function yuanchuang(){
		$content = $_POST['content'];
		$fenci = $this->do_fenci($content);
		$fenci = array_unique($fenci);
		/**
		if(count($fenci) >= 10){
			$fenci = array_splice($fenci,0,10);
		}
		**/
		$fenci = array_splice($fenci,0,20);
		foreach ($fenci as $key => $value) {
			$jiyi = $this->get_jinyici($value);
			if(!empty($jiyi['key'])){
				unset($fenci[$key]);
			}else{
				$content = str_replace($value,$jiyi['val'],$content);
				$f['yuan'][] = $value;
				$f['zhuan'][] = $jiyi['val'];
			}
		}
		$result = array();
		$yc =  implode($f['yuan'],",");
		$zc =  implode($f['zhuan'],",");
		$result['yc'] = $yc;
		$result['zc'] = $zc;
		$result['data'] = $content;
		//print_r($result);
		echo json_encode($result);
	}

	public function get_jinyici($keywords){
		$url = 'http://apis.baidu.com/baidu_openkg/zici_kg/zici_kg';
		$data = '{"query": "'.$keywords.'的同义词", "resource": "zici"}';
		$apikey = "9a6c60f3c9f9771333d8d3110da2124e";
		$re = $this->request_jinyici($url,$data,$apikey);
		$res = json_decode($re,1);
		if (isset($res['data'][0]['term_synonym'])){
			$val = $res['data'][0]['term_synonym'][0];
			$key = "";
		}else{
			$key = $keywords;
			$val = "";
		}
		return array("key"=>$key,"val"=>$val);
	}

	public function request_jinyici($url,$data,$apikey){
		$ch = curl_init();
    $header = array(
        'Content-Type:application/x-www-form-urlencoded',
        'apikey: '.$apikey,
    );
    // 添加apikey到header
    curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
    // 添加参数
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // 执行HTTP请求
    curl_setopt($ch , CURLOPT_URL , $url);
    $res = curl_exec($ch);
		return $res;
	}

	public function do_fenci($data){
		$url = 'http://api.pullword.com/post.php';
		$source = $data;
		$param1 = "1"; // 返回概率超过这个数字的值 0-1
		$param2 = "0"; //是否开启调试模式，0 不开启。 1开启，显示概率
		$params['source'] = $source;
		$params['param1'] = $param1;
		$params['param2'] = $param2;
		$result = $this->post_request($url,$params);
		$res = explode("\r\n",$result,-3);
		return $res;
	}

	public function post_request($url,$fields){
		$ch = curl_init();
		$ret = curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		if (is_array($fields)) {
		    $sets = array();
		    foreach ($fields AS $key=>$val) {
		        $sets[] = $key . '=' . urlencode($val);
		    }
		    $fields = implode('&', $sets);
		}
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		//设置curl超时秒数
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$ret = curl_exec($ch);
		return $ret;
	}



}
