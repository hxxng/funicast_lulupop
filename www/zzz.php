<?php
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/lib/Point_class.php";
include $_SERVER['DOCUMENT_ROOT']."/lib/Payment_class.php";
include $_SERVER['DOCUMENT_ROOT']."/lib.mail.php";

$ot_code = "B220428175417D18";
$row['ct_idx'] = '781';
$DB->update_query('cart_t', array('ct_pdate'=> 'now()', 'ot_code'=>$ot_code, 'ct_select'=> 2, 'ct_status'=>2), "idx = ".$row['ct_idx']);

exit;
$fromName = '보내는사람';
$fromEmail = 'dhtale@o-stage.co.kr';
$toName = ' 받는사람';
$toEmail = 'zsura@naver.com';
$subject = 'test';
$contents = '내용';
$isDebug = 1;
//sendMail($fromName, $fromEmail, $toName, $toEmail, $subject, $contents, $isDebug=0);
echo mailer_new($fromName, $fromEmail, $toEmail, $toName, $subject, $contents);
exit;
	$url = "https://graph.instagram.com/me/media?fields=caption,id,media_type,media_url,permalink,thumbnail_url,timestamp,username&access_token=IGQVJVeDZAoeDhWbDQ1SmczV1JqeFVOUEthUzQ5dk1yUzFZAMW9QcmpLamh2Nm4zNC1CN1pYNnVWQnA4bzdjZAnlUck9aa2JXamhYWm1Ma0Ewd3Q1WV9LTXRkUDFTUjNqMThEdDJMRm1HOW5GMU90dkRPOQZDZD&limit=10";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$rtn = curl_exec($ch);
		curl_close($ch);

				$rtn = json_decode($rtn, true);
print_R($rtn);
exit;
$objPayment = new Payment_class(array('db'=>$DB, 'mt_idx'=>'50'));

$objPayment->success(array('1'));

exit;
//$objPoint = new Point_class(array('db'=>$DB, 'mt_idx'=>$_SESSION['_mt_idx']));
$objPoint = new Point_class(array('db'=>$DB, 'mt_idx'=>'47'));


$objPoint->insert_point(array('point'=>'10000', 'plt_memo'=>"관리자 적립"));
exit;
$list_ct = $DB->select_assoc("select * from contents_t");
foreach($list_ct as $row_ct){
	if($row_ct['idx'] % 4 == '2') $DB->update_query("contents_t", array('ct_price'=>'200'), "idx=".$row_ct['idx']);
	if($row_ct['idx'] % 4 == '3') $DB->update_query("contents_t", array('ct_price'=>'300'), "idx=".$row_ct['idx']);
	if($row_ct['idx'] % 4 == '0') $DB->update_query("contents_t", array('ct_price'=>'400'), "idx=".$row_ct['idx']);
	if($row_ct['idx'] % 4 == '1') $DB->update_query("contents_t", array('ct_price'=>'100'), "idx=".$row_ct['idx']);

}

exit;
$list_ct = $DB->select_assoc("select * from contents_t");
foreach($list_ct as $row_ct){
	$row_ct_idx = $row_ct['idx'];
	unset($row_ct['idx']);
	$row_ct['ct_wdate'] = date('Y-m-d H:i:s');
	$DB->insert_query("contents_t", $row_ct);
	$ct_idx = $DB->insert_id();
	$list_cst = $DB->select_assoc("select * from contents_section_t where ct_idx=".$row_ct_idx);
	foreach($list_cst as $row_cst){
		$row_cst_idx = $row_cst['idx'];
		unset($row_cst['idx']);
		$row_cst['ct_idx'] = $ct_idx;
		$row_cst['cst_wdate'] = date('Y-m-d H:i:s');
		$DB->insert_query("contents_section_t", $row_cst);
		$cst_idx = $DB->insert_id();
		$list_csdt = $DB->select_assoc("select * from contents_section_detail_t where cst_idx=".$row_cst_idx);
		foreach($list_csdt as $row_csdt){
			unset($row_csdt['idx']);
			$row_csdt['cst_idx'] = $cst_idx;
			$row_csdt['csdt_wdate'] = date('Y-m-d H:i:s');
			$DB->insert_query("contents_section_detail_t", $row_csdt);
		}
	}
}
?>