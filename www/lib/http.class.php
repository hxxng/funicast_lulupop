<?php

class http
{
	function GetMethodData($url, $mrefer='', $magent='',$mcookie='',$ssl=false,$buffAll=false, $key='')
	{

		$header = array(
            "Authorization:".$key
        );

		$ch = curl_init();		

		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_HTTPHEADER, $header );
		if($ssl){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}
		if($buffAll) curl_setopt($ch,CURLOPT_HEADER,1);
		else curl_setopt($ch,CURLOPT_HEADER,0);
		if($magent) curl_setopt($ch, CURLOPT_USERAGENT, $magent);
		if($mrefer) curl_setopt($ch, CURLOPT_REFERER, $mrefer);
		if($mcookie) curl_setopt($ch, CURLOPT_COOKIE, $mcookie);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

		$result = curl_exec($ch);

		curl_close($ch);

		//echo $url."<br><br>".$result;


		return $result;
	}

	function PostMethodData($url, $query=array(), $mrefer='', $magent='',$mcookie='', $ssl=false, $buffAll=false, $key='')
	{

        $header = array("Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
        "Accept-Language: ja,en-US;q=0.8,en;q=0.6",
        "Accept-Charset: Shift_JIS,utf-8;q=0.7,*;q=0.3",
        "Content-Type: application/json",
        "Authorization:".$key);


		$fields_string='';

//		foreach($query as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
//		$fields_string = rtrim($fields_string, '&');

        $fields_string = json_encode($query, true);

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: $ip", "HTTP_X_FORWARDED_FOR: $ip"));
		curl_setopt($ch,CURLOPT_HTTPHEADER, $header );
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
		if($ssl) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if($buffAll) curl_setopt($ch,CURLOPT_HEADER,1);
		else  curl_setopt($ch,CURLOPT_HEADER,0);
		if($magent) curl_setopt($ch, CURLOPT_USERAGENT, $magent);
		if($mrefer) curl_setopt($ch, CURLOPT_REFERER, $mrefer);
		if($mcookie) curl_setopt($ch, CURLOPT_COOKIE, $mcookie);
		curl_setopt($ch,CURLOPT_POST, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

		$result = curl_exec($ch);

		curl_close($ch);

		//echo $url."<br>".$result;


		return $result;
	}

	function GetCookie($data, $type=0)
	{
		preg_match_all("/Set-Cookie:\s*(.*?)\n/i", $data, $matches);

		if (!isset($matches[$type]))
			return null;

		foreach ($matches[$type] as $i=>$row)
		{
			$rows = explode(";",$row);
			$matches[$type][$i] = trim($rows[0]);
		}

		return $matches[$type];
	}

    function PostMethodData_ip($url, $query=array(), $mrefer='', $magent='',$mcookie='', $ssl=false, $buffAll=false, $key='', $ip)
    {

        $header = array("Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Accept-Language: ja,en-US;q=0.8,en;q=0.6",
            "Accept-Charset: Shift_JIS,utf-8;q=0.7,*;q=0.3",
            "Content-Type: application/json",
            "Authorization:".$key,
            "REMOTE_ADDR: $ip", "HTTP_X_FORWARDED_FOR: $ip"
        );


        $fields_string='';

//		foreach($query as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
//		$fields_string = rtrim($fields_string, '&');

        $fields_string = json_encode($query, true);

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_PROXY, $ip);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $header );
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
        if($ssl) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if($buffAll) curl_setopt($ch,CURLOPT_HEADER,1);
        else  curl_setopt($ch,CURLOPT_HEADER,0);
        if($magent) curl_setopt($ch, CURLOPT_USERAGENT, $magent);
        if($mrefer) curl_setopt($ch, CURLOPT_REFERER, $mrefer);
        if($mcookie) curl_setopt($ch, CURLOPT_COOKIE, $mcookie);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

        $result = curl_exec($ch);

        curl_close($ch);

        //echo $url."<br>".$result;


        return $result;
    }

}

?>