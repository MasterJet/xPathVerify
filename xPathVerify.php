<?php
error_reporting(E_ALL);
ini_set('display_errors','On');
Class Crawler
{
	var $curl;
	/**
	* create curl handle.
	*/
	function __construct()
	{		
		$this->curl= curl_init();
	}
	/**
	* If login is required use this function
	* to have session/cookie.
	*/
	function logIn($loginActionUrl,$parameters,$referer)
	{
			curl_setopt ($this->curl, CURLOPT_URL,$loginActionUrl);	
			curl_setopt ($this->curl, CURLOPT_POST, 1);	
			curl_setopt ($this->curl, CURLOPT_POSTFIELDS, $parameters);	
			curl_setopt ($this->curl, CURLOPT_COOKIEJAR, realpath('cookie.txt')); // cookie.txt should be in same directoy, where calling script is	
			curl_setopt ($this->curl, CURLOPT_COOKIEFILE, realpath('cookie.txt'));
			curl_setopt ($this->curl, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt ($this->curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($this->curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux i586; de; rv:5.0) Gecko/20100101 Firefox/5.0');			
			curl_setopt ($this->curl, CURLOPT_REFERER, $referer);	// set referer
			curl_setopt ($this->curl, CURLOPT_SSL_VERIFYPEER, FALSE);// ssl certificate
    	    curl_setopt ($this->curl, CURLOPT_SSL_VERIFYHOST, 2);
			$result['EXE'] = curl_exec($this->curl);
			$result['INF'] = curl_getinfo($this->curl);
			$result['ERR'] = curl_error($this->curl);
			return $result;					
	}
	/**
	* simply return page content when loged in
	* or where login is not required.
	*/
	function getContent($url, $proxy="") 
	{
		
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_HEADER, 0);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		
			if($proxy != "")
			{
				curl_setopt($this->curl, CURLOPT_PROXY, $proxy);
				curl_setopt($this->curl, CURLOPT_HTTPPROXYTUNNEL, 0);
			}
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);	
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($this->curl, CURLOPT_TIMEOUT, 15);
			//curl_setopt ($this->curl, CURLOPT_COOKIEJAR, realpath('cookie.txt'));	
			//curl_setopt ($this->curl, CURLOPT_COOKIEFILE, realpath('cookie.txt'));
		curl_setopt($this->curl, CURLOPT_REFERER, 'http://www.google.com/');
		curl_setopt($this->curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux i586; de; rv:5.0) Gecko/20100101 Firefox/5.0');
		curl_setopt ($this->curl, CURLOPT_SSL_VERIFYPEER, FALSE);// ssl certificate
		curl_setopt ($this->curl, CURLOPT_SSL_VERIFYHOST, 2);
		$result['EXE'] = curl_exec($this->curl);
		$result['INF'] = curl_getinfo($this->curl);
		$result['ERR'] = curl_error($this->curl);
	 
		//curl_close($this->curl);
		unset($url);
		return $result;
	}

	public function extractor($str,$from,$to)
	{
		$from_pos = strpos($str,$from);
		$from_pos = $from_pos + strlen($from);
		$to_pos   = strpos($str,$to,$from_pos);// to must be after from
		$return	  = substr($str,$from_pos,$to_pos-$from_pos);
		unset($str,$from,$to,$from_pos,$to_pos );			
		return $return;
	}
	/**
	* array getSelectiveContent($content,$from,$to,$exclude="")
	* return array of content between provided 
	* from and to positions.
	*/
	public function getSelectiveContent($content,$from,$to,$exclude="")
	{
		$return = array(); // array for return elements
		$size_FROM = strlen($from); 
		$size_TO = strlen($to);
		while(true)
		{
			$pos = strpos($content,$from); // find first occurance of $from
			if( $pos === false )
			{
				break;  // if not exist break loop
			}
			else
			{
				$element  = $this->extractor($content,$from,$to); // fetch first element
				if($exclude == "")
				{
					if( trim($element) != "" )
					{
						$return[] = trim($element);
					}
				}
				else
				{
					if(trim($element) != "" && !strstr($element,$exclude)) // if nothing in range, and exclude is not in it
					{
						$return[] = trim($element); // put fetched content in array.
					}
				}
				$content = substr($content,$pos+strlen($element)+$size_FROM+$size_TO); // remove $from to $to from content 
			}
		}
		unset($content,$from,$to,$element,$exclude,$pos,$size_FROM,$size_TO);
		return $return;
	}
	
	/** 
	* string refine_str($str,$from,$to="")
	* remove $from to $to string from subject string
	* if $to is not provided $from will be considered
	* a string to remove.
	*/
	
	public	function refine_str($str,$from,$to="")
		{
		while ($from_pos = strpos($str,$from))
		{
			if($to != "")
			{
				$to_pos   = strpos($str,$to,$from_pos);// to must be after from
				$str1 	  = substr($str,0,$from_pos);
				$str2 	  = substr($str,$to_pos+strlen($to));
				$str	  = $str1.$str2;
			}
			else
			{
				$str1 	  = substr($str,0,$from_pos);
				$str2 	  = substr($str,$from_pos+strlen($from));
				$str	  = $str1.$str2;
			}
		}
		unset ($str1,$str2);	
	return $str;
	}
	
	/** 
	* string CustomStrStr($str,$needle,$position = false,$sub = false)
	* $str = "This is sample text. it is really simple example";
	* $position = false and $sub = false show result of before first occurance of $needle
	* $position = true and $sub false show result of before last occurance of $needle
	* $position = false and $sub = true show result of after first occurance of $needle
	* $position = true and $sub true show result of after last occurance of $needle
	*/	
	
	
	public function CustomStrStr($str,$needle,$position = false,$sub = false)
	{
		$Isneedle = strpos($str,$needle);
		if ($Isneedle === false)
		return false;
		
			$needlePos =0;
			$return;
			if ( $position === false )
				$needlePos = strpos($str,$needle);
			else
				$needlePos = strrpos($str,$needle);
				
			if ($sub === false)
				$return = substr($str,0,$needlePos);
			else
				$return = substr($str,$needlePos+strlen($needle));
				
		return $return;			
	}
	
	
	
}// class	

function ColoredEcho($BG,$FG,$TXT){
  $ForeGrounds = array(
    "Black" => "0;30",
    "DarkGrey" => "1;30",
    "Red" => "0;31",
    "LightRed" => "1;31",
    "Green" => "0;32",
    "LightGreen"=>"1;32",
    "Brown" => "0;33",
    "Yellow" => "1;33",
    "Blue" => "0;34",
    "LightBlue" => "1;34",
    "Magenta" => "0;35",
    "LightMagenta" => "1;35",
    "Cyan" => "0;36",
    "LightCyan" => "1;36",
    "LightGrey" => "0;37",
    "White" => "1;37" 
  );

  $BackGrounds = array(
    "Black" => ";40m",
    "Red" => ";41m",
    "Green" => ";42m",
    "Yellow" => ";43m",
    "Blue" => ";44m",
    "Magenta" => ";45m",
    "Cyan" => ";46m",
    "LightGrey" => ";47m"
  );

  return "\e[" . $ForeGrounds[$FG] . $BackGrounds[$BG] . $TXT ."\e[0m";
}
function prompt(){
	echo ColoredEcho("LightGrey", "Black", ">>>");
	echo " ";
}
function getUserInput($MSG){
	prompt();
	$cmd= readline($MSG);
	return $cmd;
}

function IsUrl($url){

	    $regex = "((https?|ftp)\:\/\/)?"; // SCHEME 
	    $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass 
	    $regex .= "([a-z0-9-.]*)\.([a-z]{2,3})"; // Host or IP 
	    $regex .= "(\:[0-9]{2,5})?"; // Port 
	    $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path 
	    $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query 
	    $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor 

       if(preg_match("/^$regex$/i", $url)) // `i` flag for case-insensitive
       { 
            return true; 
       }else{
       		return true;
       } 
}

function welcome(){
	
	echo "\n";
	prompt();
	echo "xPathFinder v-1.\n";
	prompt();
	echo "No page is Loaded in DOM.\n";
	prompt();
	echo "Provide URL to start hunting.\n"; 
	prompt();
	echo "Type exit to quit.\n\n";	
}



function CommandProcessor($cmd, &$HTML_LOADED, &$loop, &$DOM){
	if(strtolower($cmd) == 'exit'){
		return "EXIT";
	}
	if(IsUrl($cmd)){
		return "URL";
	}else{
		return "BadURL";
	}		
}
function checkEXIT($cmd){
	if(strtolower($cmd) == 'exit'){
		exit(0);
	}
}
function LoadURL($URL, $spider, &$xPath, &$HTML_LOADED, &$DOM){
	prompt(); echo "Loading URL In DOM" . PHP_EOL;
	$pageHTML = $spider->getContent ($URL);
	if (! empty($pageHTML ['ERR'])) {
		echo PHP_EOL ."ERROR" . PHP_EOL. PHP_EOL;
		echo $pageHTML['ERR']. PHP_EOL;
		return false;
	}else{
		@$DOM->loadHTML ( $pageHTML ['EXE'] ); 
		$xPath = new DOMXPath ( $DOM );
		$HTML_LOADED = true;
		return true;
	}
}

$spider = new crawler ();
$pageHTML = "";

$HTML_LOADED = false;
$loop = true;
$DOM = new DOMDocument ();
$xPath = "";
$command = "";

welcome();

while($loop){

	if(!$HTML_LOADED){
		$cmd= getUserInput("URL: ");
		checkEXIT($cmd);
		if(IsUrl($cmd)){
			$res = LoadURL($cmd, $spider, $xPath, $HTML_LOADED, $DOM);
				if(!$res){
					prompt(); echo "Page Loading Error:" . PHP_EOL;
					continue;
				}else{
					prompt(); echo "$cmd Loaded in DOM" . PHP_EOL;
				}
		}else{
			echo ColoredEcho("LightGrey", "Black",">>> Invalid URL") . PHP_EOL;
			continue;
		}
	}

	$cmd= getUserInput("Enter Xpath: ");
	checkEXIT($cmd);

	prompt(); echo "$cmd" . PHP_EOL;
	$element_ARR = $xPath->query($cmd);
	$totalHits = $element_ARR->length;
	if($element_ARR->length < 1)
	{
		echo PHP_EOL ."No Element Selected" . PHP_EOL;
		continue;
	}else{
		// print total hits
		$cmd= getUserInput("Attribute To Show Data: ");
		if($cmd == "text"){
			foreach ($element_ARR as $element) {
				echo $element->nodeValue. PHP_EOL;
			}
		}else{
			foreach ($element_ARR as $element) {
				echo $element->getAttribute($cmd). PHP_EOL;
			}
		}
	}	

	prompt(); echo PHP_EOL;
	prompt(); echo "Total Hits: ". $totalHits . PHP_EOL;
}

?>