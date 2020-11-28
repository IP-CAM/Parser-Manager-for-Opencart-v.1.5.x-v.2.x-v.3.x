<?php
if (!defined('PARSER_LOG')) {
	define('PARSER_LOG', DIR_LOGS.'parser.log');
}	
if (!defined('DIR_COOKIE')) {
	define('DIR_COOKIE', DIR_DOWNLOAD);
}
class MCurl {
	public static $log = '';
	protected $options = array();
	private $curl_session;	
	private $delete_cookie = 0;	
	protected static $_instance;
	public $user_agent  = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0';
	
	private function __clone(){
    }
	
	public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
	
	function __construct(){   
		if(file_exists(PARSER_LOG)){
			unlink(PARSER_LOG);
		}
		self::addMessage("Начало парсинга");
		if(!function_exists('curl_init')){
			self::addMessage("(!) Не подключен Curl ");
			echo "Не подключен Curl!";
			exit;
			}
			$this->options  = array(
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_CONNECTTIMEOUT => 15,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_REFERER => 'https://market.yandex.ru',
				CURLOPT_AUTOREFERER => 1,
				CURLOPT_HEADER => 0,
				CURLOPT_USERAGENT => $this->user_agent,	
				//CURLINFO_HEADER_OUT => 1,
				CURLOPT_ENCODING => 'gzip,deflate',				
			);
	}
	
	public function __destruct() {		 
		self::addMessage("Конец парсинга");
		file_put_contents(PARSER_LOG, self::$log, FILE_APPEND);	
	}
	
	private function headersOn(){		 
	  	$this->options[CURLOPT_HEADER] = 1;
	}
	
	private function headersOff(){		 
	  	$this->options[CURLOPT_HEADER] = 0;
	}
	
	public function setReferer($referer){		 
	  	$this->options[CURLOPT_REFERER] = $referer;
	}
	
	public function setProxy($proxy = '', $user = ''){
		$this->options[CURLOPT_PROXY] = $proxy;
		if(!empty($user)){
			$this->options[CURLOPT_PROXYUSERPWD] = $user;
		}		
		self::addMessage("Парсинг через прокси: ".$proxy. " пользователь:".$user);
	}
	
	public function setCookie($region = ''){			
		file_put_contents(DIR_COOKIE.'cookie.txt', '.yandex.ru	TRUE	/	FALSE	0	yandex_gid	'. $region, FILE_APPEND);
		$this->options[CURLOPT_COOKIEJAR] = DIR_COOKIE.'cookie.txt';
		$this->options[CURLOPT_COOKIEFILE] = DIR_COOKIE.'cookie.txt';		
		self::addMessage("Cookie включены.");
	}
	
	public function setUserAgent($user_agent){
		
		if (!empty($user_agent))
			$this->user_agent = $user_agent;
		$this->options[CURLOPT_USERAGENT] = $this->user_agent;	
		
		self::addMessage("User agent: ". $this->user_agent);
	}
	
	static public function addMessage($msg){
		self::$log .=  date('d-m-Y H:i:s').': '.$msg."\n";
	}
	
	public function getContent($url){	
		$this->curl_session = curl_init(html_entity_decode($url));	
		$this->headersOn();
		curl_setopt_array($this->curl_session, $this->options);
		$content = $this->Mcurl_exec($url);
		
		if (!$content)
			return false;
		$this->MCurlCurlClose();	
		return($content);
	}
	
	public function getImage($url, $dir = '', $file){ 
		$this->curl_session = curl_init(($url));		
		$options = array(
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_CONNECTTIMEOUT => 15,
				CURLOPT_TIMEOUT => 20,				
				CURLOPT_HEADER => 0,
			//	CURLOPT_USERAGENT => $this->user_agent,					
		);	
		
		/*$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);		
		$content = curl_exec($ch);		
		*/	
		curl_setopt_array($this->curl_session, $options);		 
		$content = $this->Mcurl_exec($url);			
		if ($content){
			file_put_contents($dir.$file, $content);
			$info = getimagesize($dir.$file);			
			if($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/gif' || $info['mime'] == 'image/png'){					
				return $file;
			} else {
				unlink($dir.$file);
			}			
		} 
		
		return false;				
	}
	
	private  function Mcurl_exec($url, $redirects = 0) {
		self::addMessage("Запрос: ".$url);		
		curl_setopt($this->curl_session, CURLOPT_URL, $url);		
		$data = curl_exec($this->curl_session);		
		$curl_code = curl_getinfo($this->curl_session);			
		if 	($curl_code['http_code'] == 200){		
			self::addMessage("Удачный ответ от сервера");	
			self::addMessage("Получено ".$curl_code['size_download']." байт");
			return $data;			
		} elseif ($curl_code['http_code'] == 301 || $curl_code['http_code'] == 302 || $curl_code['http_code'] == 303 || $curl_code['http_code'] == 307) {			
			list($header) = explode("\r\n\r\n", $data, 2);			
			$matches = array();
			preg_match('/(Location:|URI:)(.*?)($|\s|\n)/Uim', $header, $matches);	
			if ((isset($curl_code['redirect_url']) OR isset($matches[2])) AND $redirects < CURLOPT_MAXREDIRS ){				
				if(isset($curl_code['redirect_url'])){
					$redirect_url = trim($curl_code['redirect_url']);
				} else {				
					$redirect_url = trim($matches[2]);
				}		
				if (!preg_match('#http#', $redirect_url, $url_match)){					
					$redirect_url = 'http://'. parse_url($curl_code['url'], PHP_URL_HOST). $redirect_url;					
				}				
				self::addMessage("Перенаправление -> ".$redirect_url);					
				$redirects++;				
				return  $this->Mcurl_exec(html_entity_decode($redirect_url) ,$redirects);
			} else {
				self::addMessage("Много перенаправлений...");	
				$this->MCurlCurlClose($this->curl_session);
				unlink(DIR_COOKIE.'cookie.txt');
				return false;
			} 			
		} else {			
			self::addMessage("Неудачный ответ от сервера: ".$curl_code['http_code'].", попробуйте позже");
			return false;
		}		
	}
	
	
	public function getImageProxyList($url ,$dir, $file,  $filename){
		$proxy_array = array_unique($this->load_from_file($filename));			
		$this->curl_session = curl_init($url);	
		$i = 0;
		foreach ($proxy_array as $proxy){			
			$this->setProxy($proxy);
			//$this->options[CURLOPT_PROXYUSERPWD] = "user:password";	
			$this->headersOff();
			curl_setopt_array($this->curl_session, $this->options);
			$content = $this->Mcurl_exec($url);
			if ($content){
				file_put_contents($dir.$file, $content);
				$this->MCurlCurlClose($this->curl_session);
				return $file;
			}
			if ($i > 10)
				break;
			$i++;	
		}
		self::addMessage("В прокси-листе нет рабочих адресов...");
		$this->MCurlCurlClose($this->curl_session);
	}
	
	public function getContentProxyList($url , $filename){
		$proxy_array = array_unique($this->load_from_file($filename));			
		$this->curl_session = curl_init($url);	
		$i = 0;
		foreach ($proxy_array as $proxy){			
			$this->setProxy($proxy);
			//$this->options[CURLOPT_PROXYUSERPWD] = "user:password";	
			$this->headersOn();
			curl_setopt_array($this->curl_session, $this->options);
			$content = $this->Mcurl_exec($url);
			if ($content){
				$this->MCurlCurlClose($this->curl_session);
				return $content;
			}
			if ($i > 10)
				break;
			$i++;	
		}
		self::addMessage("В прокси-листе нет рабочих адресов...");
		$this->MCurlCurlClose($this->curl_session);
	}
			
	private function MCurlCurlClose(){
		if(is_resource($this->curl_session)){
			curl_close($this->curl_session);
		}
	}
	
	public function proxyCheckMulti($url = '', $filename, $match){	
		$proxy_array = array_unique($this->load_from_file($filename));	
		$mh = curl_multi_init();   
		$chs = array();
		$chs_proxy = array();
		
		foreach ( $proxy_array as $proxy ) {
			if(!empty($proxy)){
				$chs[] = ( $ch = curl_init() );	
				$chs_proxy[(string) $ch] = $proxy;
				curl_setopt( $ch, CURLOPT_URL, $url );
				curl_setopt( $ch, CURLOPT_HEADER, 0 );
				curl_setopt( $ch, CURLOPT_PROXY, $proxy );			
				curl_setopt_array($ch, $this->options);						
				curl_multi_add_handle( $mh, $ch );
			}
		}
	
		
		$proxy_n = 0;
		
		do {
            while (($execrun = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM) ;
            if ($execrun != CURLM_OK)
                break;	
						
            // a request was just completed -- find out which one
            while ($done = curl_multi_info_read($mh)) {				
                // get the info and content returned on the request
                $info_ch = curl_getinfo($done['handle']);				
                $content = curl_multi_getcontent($done['handle']);
				$proxy  = $info_ch['primary_ip'].':'.$info_ch['primary_port'];
				if (preg_match('/'.$match.'/',$content) AND $info_ch['http_code'] == 200){
					$this->addMessage("Найден рабочий прокси для сайта $url - ". $proxy . ". Время доступа: ".$info_ch['total_time']." сек.");			
					/*if ($proxy_n == 0){
						file_put_contents($filename, $proxy."\n");
					} else {
						file_put_contents($filename, $proxy."\n", FILE_APPEND);
					}*/	
					$proxy_n++;
				} else {
					$this->addMessage("Прокси не работает: ". $proxy);	
				}				
                // remove the curl handle that just completed
                curl_multi_remove_handle($mh, $done['handle']);

            }

            // Block for data in / output; error handling is done by curl_multi_exec
            if ($running)
                curl_multi_select($mh, 10);//$this->timeout);
			
		} while ($running);		
		
		curl_multi_close($mh); 	
		$this->addMessage("Найдено рабочих прокси для сайта $url - $proxy_n");
		
		return $proxy_n;
	}
	
	
	private function load_from_file($filename, $delim = "\n"){        
        if (file_exists($filename)){
			$fp = @fopen($filename, "r");        
       } else {
			echo "Нет файла: proxy_list.txt";
            self::addMessage("Нет файла: $filename");
            return array();
        }        
        $data = @fread($fp, filesize($filename) );
        fclose($fp);
        
        if(strlen($data)<1)        {
            self::addMessage("Пустой файл: $filename");
            return array();
        }
        
        $array = explode($delim, $data);
        
        if(is_array($array) && count($array)>0){
            foreach($array as $k => $v){
                if(strlen( trim($v) ) > 0)
                    $array[$k] = trim($v);
            }
            return $array;
        } else {
            self::addMessage("Нет данных: $filename");
            return array();
        }
    }
	
}
?>