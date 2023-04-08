<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/lib/Point_class.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}

if($decoded_array['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}
if($decoded_array['ot_pcode']=="") {
    echo result_data('false', '잘못된 접근입니다. ot_pcode', '');
    exit;
}

$query = "
		select *, a1.idx as mt_idx from member_t a1
		where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level in (3,5)
	";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    if($row['mt_level'] == 3) {
        $count = $DB->count_query("select * from cart_t where ot_pcode = '".$decoded_array['ot_pcode']."' and mt_idx = ".$row['mt_idx']);
        if($count > 0) {
            unset($arr_query);
            $arr_query = array(
                "ct_status" => 6,
                'ct_cdate'=> "now()",
            );

            $DB->update_query('cart_t', $arr_query, " ot_pcode = '".$decoded_array['ot_pcode']."'");

            $row_c = $DB->fetch_assoc("select cart_t.*, ot_use_point, ot_use_coupon_price, ot_hp, ot_name from cart_t left join order_t on order_t.ot_code = cart_t.ot_code where cart_t.ot_pcode = '".$decoded_array['ot_pcode']."'");
            $count2 = $DB->count_query("select * from cart_t where ot_code = '".$row_c['ot_code']."'");
            $point = ($row_c['ct_price'] - $row_c['ot_use_point'] - $row_c['ot_use_coupon_price']) * (3 / 100);

            $objPoint = new Point_class(array('db'=>$DB, 'mt_idx'=>$row['mt_idx']));
            $objPoint->insert_point(array('point'=>round($point), 'ot_code'=>$row_c['ot_code'], 'ot_pcode'=>$row_c['ot_pcode'], 'plt_memo'=>"상품 [".$row_c['pt_title']."] 구매확정", 'plt_status'=>1));

            $query = "select * from cart_t where ot_code = '".$row_c['ot_code']."'";
            $cnt = $DB->count_query($query);

            $query = "select * from cart_t where ot_code = '".$row_c['ot_code']."' and ct_status = 5";
            $confirm_cnt = $DB->count_query($query);

            if($cnt - $confirm_cnt == 0) {
                $DB->update_query("order_t", array("ot_status" => 6), " ot_code = '".$row_c['ot_code']."'");
            }

            if($row['mt_pushing'] == "Y" || $row['mt_pushing3'] == "Y") {
                $chk = "Y";
            } else {
                $chk = "N";
            }

            //알림톡 토큰 생성
            $_apiURL	  =	'https://kakaoapi.aligo.in/akv10/token/create/30/s/';
            $_hostInfo	=	parse_url($_apiURL);
            $_port		  =	(strtolower($_hostInfo['scheme']) == 'https') ? 443 : 80;
            $_variables	=	array(
                'apikey' => 'apikey',
                'userid' => 'userid'
            );

            $oCurl = curl_init();
            curl_setopt($oCurl, CURLOPT_PORT, $_port);
            curl_setopt($oCurl, CURLOPT_URL, $_apiURL);
            curl_setopt($oCurl, CURLOPT_POST, 1);
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, http_build_query($_variables));
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);

            $ret = curl_exec($oCurl);
            $error_msg = curl_error($oCurl);
            curl_close($oCurl);

            // JSON 문자열 배열 변환
            $retArr = json_decode($ret);

            //알림톡 발송
            $msg = '[구매시 포인트 누적]

안녕하세요, 룰루팝! 입니다. '.$row_c['ot_name'].' 님!! 지금 구매 포인트가 적립되었습니다. > 포인트 : '.round($point).' 포인트

※ 이 메시지는 구매하신 상품의 지급된 포인트 안내 메시지입니다.';

            $_apiURL    =	'https://kakaoapi.aligo.in/akv10/alimtalk/send/';
            $_hostInfo  =	parse_url($_apiURL);
            $_port      =	(strtolower($_hostInfo['scheme']) == 'https') ? 443 : 80;
            $_variables =	array(
                'apikey'      => 'apikey',
                'userid'      => 'userid',
                'token'       => $retArr->token,
                'senderkey'   => 'senderkey',
                'tpl_code'    => 'tpl_code',
                'sender'      => 'sender',
                'receiver_1'  => $row_c['ot_hp'],
                'recvname_1'  => $row_c['ot_name'],
                'subject_1'   => '룰루팝',
                'message_1'   => $msg,
//                'testMode'   => "Y",        //테스트모드
            );

            $oCurl = curl_init();
            curl_setopt($oCurl, CURLOPT_PORT, $_port);
            curl_setopt($oCurl, CURLOPT_URL, $_apiURL);
            curl_setopt($oCurl, CURLOPT_POST, 1);
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, http_build_query($_variables));
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);

            $ret = curl_exec($oCurl);
            $error_msg = curl_error($oCurl);
            curl_close($oCurl);

            // 리턴 JSON 문자열 확인
//            print_r($ret . PHP_EOL);

            // JSON 문자열 배열 변환
            $retArr = json_decode($ret);

            $payload['data']['ot_pcode'] = $decoded_array['ot_pcode'];

            if($decoded_array['debug_jwt']==DEBUG_JWT) {
                echo result_data('true', '[debug] 구매확정', $payload);
            }else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '구매확정', $jwt);
            }
        } else {
            echo result_data('false', '존재하지 않는 상품주문번호입니다.', $arr);
        }
    } else {
        $count = $DB->count_query("select * from cart_t where ot_pcode = '".$decoded_array['ot_pcode']."' and nmt_id = '".$decoded_array['mt_id']."'");
        if($count > 0) {
            unset($arr_query);
            $arr_query = array(
                "ct_status" => 6,
                'ct_cdate'=> "now()",
            );

            $DB->update_query('cart_t', $arr_query, " ot_pcode = '".$decoded_array['ot_pcode']."'");

            $row_c = $DB->fetch_assoc("select cart_t.*, ot_use_point, ot_use_coupon_price from cart_t left join order_t on order_t.ot_code = cart_t.ot_code where cart_t.ot_pcode = '".$decoded_array['ot_pcode']."'");
            $count2 = $DB->count_query("select * from cart_t where ot_code = '".$row_c['ot_code']."'");

            $query = "select * from cart_t where ot_code = '".$row_c['ot_code']."'";
            $cnt = $DB->count_query($query);

            $query = "select * from cart_t where ot_code = '".$row_c['ot_code']."' and ct_status = 5";
            $confirm_cnt = $DB->count_query($query);

            if($cnt - $confirm_cnt == 0) {
                $DB->update_query("order_t", array("ot_status" => 6), " ot_code = '".$row_c['ot_code']."'");
            }

            $payload['data']['ot_pcode'] = $decoded_array['ot_pcode'];

            if($decoded_array['debug_jwt']==DEBUG_JWT) {
                echo result_data('true', '[debug] 비회원 구매확정', $payload);
            }else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '비회원 구매확정', $jwt);
            }
        } else {
            echo result_data('false', '존재하지 않는 상품주문번호입니다.', $arr);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>