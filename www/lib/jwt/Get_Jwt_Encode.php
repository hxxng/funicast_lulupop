<?php
	error_reporting( E_ALL );
	ini_set( "display_errors", 1 );

	include_once("_common.php");
	include_once("./config.php");

	use \Firebase\JWT\JWT;

	function printr($arr_val) {
		echo "<pre>";
		print_r($arr_val);
		echo "</pre>";
	}


	$sql = "SELECT * FROM g5_shop_item where it_use='1' order by it_order asc ";

	$result = sql_query($sql);
	while($row=sql_fetch_array($result)) {
		
		$row_result_own['it_id'] = $row['it_id'];
		$row_result_own['it_name'] = $row['it_name'];
		$row_result_own['it_basic'] = $row['it_basic'];
		$row_result_own['it_use'] = $row['it_use'];
		$row_result_own['it_price'] = $row['it_price'];
		$row_result_own['it_ea'] = $row['it_ea'];

		$arr_result_item[] = $row_result_own;
		$count++;
	}


	if ($arr_result_item) {
		$message = "조회성공";
		
		$statusArr['method'] = $method;
		$statusArr['result'] = "1";
		$statusArr['message'] = $message;
		$statusArr['sql'] = $sql;
		
		$itemsArr['count'] = $count;
		$itemsArr['item'] = $arr_result_item;
		$itemsArr['SQL'] = $sql;
		
		$resultArr['resultItem'] = $statusArr;
		$resultArr['arrItems'] = $arr_result_item;
		
	} else {
		$message = "조회 실패.";
		
		$statusArr['method'] = $method;
		$statusArr['result'] = "0";
		$statusArr['message'] = $message;
		
		$itemsArr['count'] = $count;
		$itemsArr['SQL'] = $sql;
	}




	//echo "encode:\n";
	//$jwt = JWT::encode($payload, $secret_key);
	//printr($jwt);

	//echo "decode:\n";
	//$decoded = JWT::decode($jwt, $secret_key, array('HS256'));
	//printr($decoded);
	
	/*
	echo json_encode(
	array(
		"message" => "Successful",
		"jwt" => $jwt
	));
	*/
	

$jwt = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtdF9hcHBfdG9rZW4iOiJmR1VJSnM1VVMxNmpFMmN1NFhIMFA2OkFQQTkxYkdnckJHVzZrY25LQWhTRDExY1BnZkdaaF9Ha1RzZy1WUkNSdTc4Sk1wbFBLN3gwR1NRSmZnMTlPQzZzV1NXVXBFRVBIVWxqQWsxdDhyWjY5Uk9lV0hobTlYZThCVV9GQV9KRzM1YWMwU1psRmxQM21zeGhBV2ZFQTMyREpNWFlHT043emhXIiwibXRfaWQiOiJhZG1pbiIsIm10X3B3ZCI6IjEwMTYifQ.0ef3fwuweObqE3SK-6VoWCExVchY06-K6i-SElrf_1M";
	//echo "decode:\n";
	$decoded = JWT::decode($jwt, $secret_key, array('HS256'));
	printr($decoded);



?>