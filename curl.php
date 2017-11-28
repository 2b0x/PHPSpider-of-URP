<?php
// 用cURL对页面进行解析
	include_once("simple_html_dom.php"); 
	$cookie_file =tempnam('./temp','scookie'); 
 
	login("1540129538","1");

	// schedular();
	grade();





//函数封装

function get_content($url){
    $ch = curl_init();
    global $cookie_file;
    $this_header = array(
    "content-type: application/x-www-form-urlencoded; 
    charset=GBK"
    );
    curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
	 curl_setopt($ch, CURLOPT_URL, $url);  //设置访问地址  
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    $result = curl_exec($ch);
    $result=mb_convert_encoding($result, 'UTF-8', 'GBK');
    curl_close($ch);
    return $result;
}

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
	 curl_setopt($ch,CURLOPT_TIMEOUT,20);
	 curl_setopt($ch, CURLOPT_HEADER, 1);  //false代表响应内容中不显示头信息  
	 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果把这行注释掉的话，就会直接输出响应信息    
	 curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
	 $result=curl_exec($ch);  //开始执行，响应内容以字符串的形式赋值到$result中  
	curl_close($ch);


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
	    curl_setopt($ch2,CURLOPT_TIMEOUT,20);
	    curl_setopt($ch2,CURLOPT_RETURNTRANSFER,true);
	    curl_setopt($ch2, CURLOPT_HEADER,0);
	    curl_setopt($ch2, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); 
	    curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, 0);
	    curl_setopt($ch2, CURLOPT_COOKIEJAR, $cookie_file);  
	    curl_setopt($ch2, CURLOPT_REFERER, $url);
	    curl_setopt($ch2, CURLOPT_POST,1);
	    curl_setopt($ch2, CURLOPT_POSTFIELDS,$postdata);
	    $mysise=curl_exec($ch2);
	    curl_close($ch2);
	    $mysise=mb_convert_encoding($mysise, 'UTF-8', 'GBK');
} 

function clearUp($str){	
	if ( strpos($str, "(")!== false ){
		//以左括号为标识，切分字符串为数组。将课程信息括号内的内容提出出来
		$str1 = explode("(",$str);
			
		//将括号内的内容按空格分隔开
		$str2 = explode(" ", $str1[1]);

		//取数组前两位值(即班级代码和老师姓名)
		$strNum = array_slice($str2,0,2);

		//取上课周数
		$strS = array_slice($str2,2,1);
		array_push($strS, "~");
		$strE = array_slice($str2,-2,1);
		$strN = array_merge($strS,$strE);
		$strT[] = implode("", $strN);//将数组以某字符串拼接成字符

		// 取数组后最后位(即教室)
		$strClass = array_slice($str2,-1,1);

		// 合并数组
		$str5 = array_merge($strNum,$strT,$strClass);
		$strInfo = implode(" ", $str5);

		// 整合信息并转化为字符串
		$new[] = $str1[0];
		$new[] = $strInfo;
		$totle = implode(" ", $new);
		return $totle;
	}else{
		return $str;
	}
}

function schedular(){
	$url_schedular='http://class.sise.com.cn:7001/sise/module/student_schedular/student_schedular.jsp';

   $data = get_content($url_schedular); 

	// echo $data;

   $datas = str_get_html($data);
   
   		$newData = $datas->find('table',6)->find('td');
   		foreach ($newData as $key =>   $td) {
   			$item[] = $td->plaintext;
		}   

		// 写入二维数组
		$count = count($item);  
		$arr = array();  
		for($y = 0; $y < 9; $y++){  
		    for($x = 0; $x < 8; $x++){  
		        $arr[$y][$x] = clearUp($item[$y*8+$x]);  
		    }  
		} 

		for ($i=0; $i < 9; $i++) { 
			$arr[3][$i] = "午休";
		}
		for($y = 1; $y < 9; $y++){  
		    for($x = 1; $x < 6; $x++){  
		        if ( ord($arr[$y][$x]) == 38 ) {
					$arr[$y][$x] = "自由时间";
				}
		    }  
		}

		for ($i=1; $i < 9; $i++) { 
			$mon[] = $arr[$i][1]; $tues[] = $arr[$i][2]; $wed[] = $arr[$i][3]; $thur[] = $arr[$i][4]; $fri[] = $arr[$i][5];
		}
 

		print_r($mon) ;
		echo "<br><br>" . "----------******----------" . "<br><br>";
		print_r($tues) ;
		echo "<br><br>" . "----------******----------" . "<br><br>";
		print_r($wed) ;
		echo "<br><br>" . "----------******----------" . "<br><br>";
		print_r($thur) ;
		echo "<br><br>" . "----------******----------" . "<br><br>";
		print_r($fri) ;


 		// 遍历二维数组
		// foreach($arr as $key=>$value){
		// 	foreach($value as $key2=>$value2){
		// 	   echo "  -* ";
		// 	   echo $value2;
		// 	   echo " *-  ";			   
		// 	}
		// 	echo "<br><br>" . "------------" . "<br><br>";
		// }
		// echo "----------******----------" . "<br>" . "<br>";
		
   $datas->clear();
}

function grade(){
	$url_grade = "http://class.sise.com.cn:7001/SISEWeb/pub/course/courseViewAction.do?method=doMain&studentid=Ikot/O61TKk=";

	$data = get_content($url_grade); 

	echo $data;

   $datas = str_get_html($data);

   

}

?>