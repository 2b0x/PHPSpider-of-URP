<?php
include_once("simple_html_dom.php"); 
// $cookie_file =tempnam('./temp','scookie'); 

$mainUrl;$gradeUrl;$examTimeUrl;$SchedularUrl;
$cookie_file;
	
	define("TOKEN", "royrocco");
	$wechatObj = new wechatCallbackapiTest();
	if( isset($_GET['echostr']) ){
		$wechatObj -> valid();
	}else{
		$wechatObj -> responseMsg();
	}

	class wechatCallbackapiTest{	
		public function valid(){
	        $echoStr = $_GET["echostr"];
	        $signature = $_GET["signature"];
	        $timestamp = $_GET["timestamp"];
	        $nonce = $_GET["nonce"];
	        $token = TOKEN;
	        $tmpArr = array($token, $timestamp, $nonce);
	        sort($tmpArr);
	        $tmpStr = implode($tmpArr);
	        $tmpStr = sha1($tmpStr);
	        if($tmpStr == $signature){
				header('content-type:text');
	            echo $echoStr;
	            exit;
	        }
	    }

		public function responseMsg(){
			global $mainUrl,$SchedularUrl,$gradeUrl,$examTimeUrl;
			$postStr = file_get_contents("php://input");
			if (!empty($postStr)) {
				$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
				$formUsername = $postObj -> FromUserName;
				$toUsername = $postObj -> ToUserName;
				$keyword = trim($postObj -> Content);
				$time = time();
				$textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";

				$str = $keyword;
				$msgType = "text";

				// $str = '命令#学号密码';
				$index = strpos($str, '#');
				$order = substr($str, 0, $index);
				$username = substr($str, $index+1, 10);
				$password = substr($str, $index+11);

				tes($username,$password);
				switch ($order) {
					case '成绩':
						$content = grade($gradeUrl);
						$result = sprintf($textTpl, $formUsername, $toUsername, $time, $msgType, $content);
						echo $result;
						break;

					case '课表':
						$content = schedular($SchedularUrl);
						// $data = schedular($gradeUrl);
						// $content = $data[0];
						$result = sprintf($textTpl, $formUsername, $toUsername, $time, $msgType, $content);
						echo $result;
						break;

					case '个人信息':
						$content = personInfo($gradeUrl);
						$result = sprintf($textTpl, $formUsername, $toUsername, $time, $msgType, $content);
						echo $result;
						break;
						
					case '考试时间':
						$content = examTime($examTimeUrl);
						$result = sprintf($textTpl, $formUsername, $toUsername, $time, $msgType, $content);
						echo $result;
						break;

					default:
						$msgType = "text";
						$content = '为了北方神的荣耀';
						$result = sprintf($textTpl, $formUsername, $toUsername, $time, $msgType, $content);
						echo $result;
						break;
				}//switch end
			}else{
				echo "";
				exit;
			}
		}
	}

	// 获取各接口链接
	function tes($username,$password){
			global $mainUrl,$SchedularUrl,$gradeUrl,$examTimeUrl;

			login($username,$password);

			$mainUrl = 'http://class.sise.com.cn:7001/sise/module/student_states/student_select_class/main.jsp';
			$mainData = get_content($mainUrl); 
			$mainUrl = "http://class.sise.com.cn:7001/";
			preg_match_all("/SISEWeb(.*?)'/",$mainData,$linksA);
			preg_match_all("/sise(.*?)'/",$mainData,$linksB);

			// 课程表链接
			 $SchedularUrl=substr_replace($mainUrl.$linksB[0][0], "", -1, 1);
			// 成绩表链接
			 $gradeUrl=substr_replace($mainUrl.$linksA[0][0], "", -1, 1);	
			// 考试时间链接	
			 $examTimeUrl = substr_replace($mainUrl.$linksA[0][1], "", -1, 1);
			//学生奖罚查询	
			$RewardPunishUrl = substr_replace($mainUrl.$linksB[0][2], "", -1, 1);
			// 学生违规、用电链接
			$electroUrl = substr_replace($mainUrl.$linksA[0][10], "", -1, 1);
	}
	//获取文档结构
 	function get_content($url){
	    $ch = curl_init();
	    global $cookie_file;
	    $this_header = array( "content-type: application/x-www-form-urlencoded; 
	    charset=GBK" );
	    curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
		 curl_setopt($ch, CURLOPT_URL, $url);  //设置访问地址  
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_COOKIE, $cookie_file); 
	    // curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
	    $result = curl_exec($ch);
	    $result=mb_convert_encoding($result, 'UTF-8', 'GBK');
	    curl_close($ch);
	    return $result;
	}
	// 登录
	function login($username,$password){
		global $cookie_file;
		// 模拟浏览器信息
		$header[]="User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36";  
		$header[]="Cache-Control: max-age=0";     
		$header[]="Connection: keep-alive";  
		$header[]="Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";   
		$header[]="Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3";  
		$header[]="Host:class.sise.com.cn:7001";

		$url="http://class.sise.com.cn:7001/sise/";

		$ch = curl_init();  //初始化  

		 curl_setopt($ch,CURLOPT_HTTPHEADER,$header);  
		 curl_setopt($ch, CURLOPT_URL, $url);  //设置访问地址  
		 curl_setopt($ch,CURLOPT_VERBOSE,1);
		 curl_setopt($ch,CURLOPT_TIMEOUT,50);
		 curl_setopt($ch, CURLOPT_HEADER, 1);  //false代表响应内容中不显示头信息  
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果把这行注释掉的话，就会直接输出响应信息    
		 $result=curl_exec($ch);  //开始执行，响应内容以字符串的形式赋值到$result中  
		 curl_close($ch);

		 list($header, $body) = explode("\r\n\r\n", $result); 
		//解析cookie
		preg_match("/set\-cookie:([^\r\n]*)/i", $header, $matches); 
		$cookie_file = $matches[1];


		$pattern = '<input type="hidden" name="(.*?)"  value="(.*?)">';
		preg_match($pattern ,$result,$data);
		$data_name = $data[1];
		$data_value = $data[2];
		$pattern = '<input id="random"   type="hidden"  value="(.*?)"  name="random" />';
		preg_match($pattern ,$result,$random);
		$random_value = $random[1];

		 $postdata=$data_name."=".$data_value."&random=".$random_value."&username=".$username."&password=".$password;

		 $url2="http://class.sise.com.cn:7001/sise/login_check.jsp";
		 $ch2=curl_init();
		    curl_setopt($ch2,CURLOPT_URL,$url2);
		    curl_setopt($ch2,CURLOPT_TIMEOUT,50);
		    curl_setopt($ch2,CURLOPT_RETURNTRANSFER,true);
		    curl_setopt($ch2, CURLOPT_HEADER,0);
		    curl_setopt($ch2, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); 
		    curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, 0);
		    curl_setopt($ch2, CURLOPT_COOKIE, $cookie_file);

		    // curl_setopt($ch2, CURLOPT_COOKIEJAR, $cookie_file);  
		    curl_setopt($ch2, CURLOPT_REFERER, $url);
		    curl_setopt($ch2, CURLOPT_POST,1);
		    curl_setopt($ch2, CURLOPT_POSTFIELDS,$postdata);
		    $mysise=curl_exec($ch2);
		    curl_close($ch2);
		    $mysise=mb_convert_encoding($mysise, 'UTF-8', 'GBK');
	} 
	// 考试时间
	 function examTime($url){
		$data = get_content($url); 
	    $datas = str_get_html($data);
	    $ero = $datas->find('font[face=Helvetica]',0);
	    if( is_null($ero) ){
	    	$new = $datas->find('table.table',0)->find('td');
			// echo $datas;
			//获取页面所有文本
			foreach ($new as $key => $value) {
				$newData[] = $value->plaintext;
			}
			// 获取主要信息并存数组
			for ($i=1; $i <count($newData) ; $i=$i+8) { 
				$examTime[] = $newData[$i] . ' ' . $newData[$i+1] . ' ' . $newData[$i+2] . ' ' . $newData[$i+3] . ' 座位号:' . $newData[$i+5];
			}
			return implode("\n\n", $examTime);
			// print_r($examTime);
			return $examTime;			
	    }else{
	    	echo '帐号或密码错误，请确认后重试';
	    }     
	   $datas->clear();
	}
	//成绩表
	function grade($url){
		$data = get_content($url); 
	    $datas = str_get_html($data);

	    // 查找系统当前最新的绩点
	 	$PGA = $datas->find('table',-1)->find('tr',6)->find('td',3)->plaintext;
	 	// 遍历课程名称
	 	foreach($datas->find('table.table') as $table) {
	       	foreach ($table->find('a') as  $newData) {
	       		$newData2[] = $newData->plaintext;
	       	} 	
	    }
	    // 获取成绩
	 	$newData3 = $datas->find('table.table');
	 	foreach ($newData3 as  $table) {
	 		foreach ($table->children(1)->find('tr') as $tr) {
	 			$tdata[] = $tr->find('td',-2)->plaintext;	
	 		}
	 	}
	 	// 获取学期信息
	 	$newData4 = $datas->find('table.table');
	 	foreach ($newData3 as  $table) {
	 		foreach ($table->children(1)->find('tr') as $tr) {
	 			$tdataT[] = $tr->find('td',-3)->plaintext;	
	 		}
	 	}
	 	// 获取当前学期 
	 	$year = $datas->find('table.table1',0)->find('table',0)->find('tr',0)->find('td',-3);
	 	$nowYear = getdate()['year'];
	 	$nowMon = getdate()['mon'];
	 	if(3<=$nowMon&&$nowMon<=9){
	 		$xueqi = $nowYear-1 . '年第二学期';
	 	}else{
	 		$xueqi = $nowYear . '年第一学期';
	 	}
	 	// 获取当前学期成绩
		for ($i=0; $i < count($tdataT); $i++) { 
			if ($tdataT[$i] == $xueqi) {
				$grade[$newData2[$i]] = $tdata[$i];
			}
		}
	 	$grade['当前最新绩点：'] = $PGA;

		$result;
		foreach ($grade as $key => $value) {
			$result = $result . $key . ":" . $value . "\n\n";
		}
	 	return $result;
	}
	// 个人信息
	function personInfo($url){
		$data = get_content($url); 
	    $datas = str_get_html($data);
	    $year = $datas->find('table.table1',0)->find('table',0)->find('td');
	    foreach ($year as $key => $value) {
	    	$temporaryData[] = $value->plaintext;
	    }
	    array_splice($temporaryData, 12, 4);
	    array_splice($temporaryData, 18, 2);

	    for ($i=0; $i < count($temporaryData); $i=$i+2) { 
	    	$personInfo[] = $temporaryData[$i]  . $temporaryData[$i+1];
	    }
	    $result;
	    foreach ($personInfo as $value) {
	    	$result = $result . $value . "\n\n";
	    }
	    return $result;
	}
	// 课程表
	 function schedular($url){
	   $data = get_content($url);

	   $datas = str_get_html($data);

	   $newData = $datas->find('table',6)->find('td');
	   foreach ($newData as $key => $td) {
	   		$item[] = $td->plaintext;
	   }	   	   
	   for ($i=25; $i < 30; $i++) { 
	   		$item[$i] ='午休';
	   }
	   for ($i=0; $i < 72 ; $i++) { 
	   		if( ord($item[$i]) == 38 ){
	   			$item[$i] = "自由时间";
	   		}
	   }
	   for ($i=1; $i < 6; $i++) { 
	   		$result[] = $item[$i] . "\n\n" . $item[$i+8] . "\n\n" .$item[$i+16] . "\n\n" .$item[$i+24] . "\n\n" .$item[$i+32] . "\n\n" .$item[$i+40] . "\n\n" .$item[$i+48] . "\n\n" .$item[$i+56] . "\n\n" .$item[$i+64];
	   }
	   return $result[0];
	   $datas->clear();
	}
?>