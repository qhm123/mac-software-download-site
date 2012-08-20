<?php 

require_once('core.function.php');
require_once('vdisk.config.php');

class VDisk{
	public $appkey;
	public $appsecret;
	public $access_token;
	public static $_errno;	
	public static $_error;

	public function __construct($access_token){
		if(!(c('vdisk_appkey') && c('vdisk_appsecret'))) {	
			$this->set_error(-2, 'app_key or app_secret empty');
			return;
		}
		if(!isset($access_token)){
			$this->set_error(-3,'access_token empty');
			return;
		}
		$this->appkey = c('vdisk_appkey');
		$this->appsecret = c('vdisk_appsecret');
		$this->access_token=$access_token;
		$this->set_error(-1, 'empty');
	}

	public function account_info(){
		$data = $this->_request(VDISK_ACCOUNT_INFO);
		return $data;
	}

	public function files($file_path,$file_name){
		$array=array(
		             'file'=>"@".$file_path
		             );
		$data=$this->_request(VDISK_FILES.$file_name,$array);
		return $data;
	}

  public function metadata($file_path) {
    /*
		$array=array(
		             'file'=>"@".$file_path
		             );
     */
		$data=$this->_request(VDISK_METADATA.$file_path);
		return $data;
  }

	public function down_files($file_name){
		return $this->_down(VDISK_DOWN_FILES.'/'.$file_name);
	}

	public function media($file_name){
		$data=$this->_request(VDISK_MEDIA.$file_name);
		return $data;
	}

	public function fileops_create_folder($fold_path){
		$array=array(
		             'root'=>'basic',
		             'path'=>$fold_path
		             );
		$data=$this->_request(VDISK_FILEOPS_CREATE_FOLDER,$array);
		return $data;
	}

	public function fileops_delete($file_name){
		$array=array(
		             'root'=>'basic',
		             'path'=>$file_name
		             );
		$data=$this->_request(VDISK_FILEOPS_DELETE,$array);
		return $data;
	}

	private function _request($url,$array=null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$headers=$this->_token();
    /*
		if(!defined('IN_LOCAL')){
			$headers[]="SAEUrl:$url";
			$sae_url="http://10.67.15.17:8089/";
			curl_setopt($curl, CURLOPT_URL, $sae_url);
		}else{
     */
			curl_setopt($curl, CURLOPT_URL, $url);
		//}
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		if($array != null){
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $array);
		}
		$data = curl_exec($curl);
		if(curl_error($curl)){
      echo "<br>";
			echo "$url curl error:".curl_error($curl);
		}
		curl_close($curl);
		if($arr = json_decode($data, true)){	
			if(isset($arr['error']))
				$this->set_error($arr['error_code'], $arr['error']);
			else 
				$this->set_error(0, 'ok');
			return $arr;
		}else{
			$this->set_error(-1, 'empty');
			return false;
		}
	}

	private function _down($url,$array=null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_token());
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_HEADER, 1);
		if($array != null){
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $array);
		}
		$response = curl_exec($curl);
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

    $headers = $this->get_headers_from_curl_response($response);
    return $headers['location'];
	}


  private function get_headers_from_curl_response($response) {
      $headers = array();
      $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
      foreach (explode("\r\n", $header_text) as $i => $line) {
          if ($i === 0)
              $headers['http_code'] = $line;
          else{
              list ($key, $value) = explode(': ', $line);
              $headers[$key] = $value;
          }
      }
      return $headers;
  }

	private function _token(){
		$appkey=$this->appkey;
		$access_token=$this->access_token;
		$expire=time()+60*60;
		$sign=substr(base64_encode(hash_hmac('sha1', $appkey .  $access_token . $expire, $this->appsecret, true)), 5, 10);
		$auth=json_encode(array(
		                  'appkey'=>$this->appkey,
		                  'access_token'=>$access_token,
		                  'expires'=>$expire,
		                  'sign'=>$sign
		                  ));
		$header=array(
		              'Authorization:Weibo '.$auth
		              );
		return $header;
	}
	static public function set_error($errno, $error) {
		self::$_errno = $errno;
		self::$_error = $error;	
	}
	
	static public function errno() {
		return self::$_errno;	
	}

	static public function error() {
		return self::$_error;
	}
}
?>
