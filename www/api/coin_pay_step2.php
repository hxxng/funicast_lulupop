<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}
if($decoded_array['ct_price']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_price', '');
    exit;
}
if($decoded_array['ct_pg_pg_tid']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_pg_pg_tid', '');
    exit;
}
if($decoded_array['ct_code']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_code', '');
    exit;
}

$arr = array();
$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level = 3
";
$row = $DB->fetch_query($query);

if($row['mt_idx'] < 1) {
	echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
	exit;
}
$query = "select * from coin_t where mt_idx = ".$row['mt_idx']." and ct_type = 1 and ct_status = 1 and ct_price = ".$decoded_array['ct_price']." and ct_code = '".$decoded_array['ct_code']."' order by ct_wdate desc";
$coin = $DB->fetch_assoc($query);

$bonus = 0;
for($i=10000; $i<=$decoded_array['ct_price']; $i+=5000) {
    $bonus++;
    if($i == 100000) {
        $bonus = 20;
        break;
    }
}

if($coin['idx'] > 0) {
    if($coin['ct_pay_type'] == 1) {
        $arr['ct_status'] = 1;
        $arr['ct_account_num'] = $decoded_array['ct_accountNumber'];
        $arr['ct_bank'] = $decoded_array['ct_bank'];
        $arr['ct_duedate'] = $decoded_array['ct_duedate'];
        $arr['ct_pg_pg_tid'] = $decoded_array['ct_paymentKey'];
    } else {
        $arr['ct_status'] = 2;
        $arr['ct_pdate'] = "now()";
        $arr['ct_amount'] = $coin['ct_amount'] + $bonus;
        $arr['ct_pg_pg_tid'] = $decoded_array['ct_paymentKey'];

        $DB->update_query("member_t", array("mt_coin" => (int)$row['mt_coin'] + (int)$coin['ct_amount'] + (int)$bonus), " idx = ".$row['mt_idx']);
    }

    $DB->update_query("coin_t", $arr, " idx = ".$coin['idx']);

    unset($arr);
    $arr['ct_amount'] = (int)$coin['ct_amount']."코인 충전";
    $arr['bonus'] = $bonus;
    $arr['ct_price'] = (int)$coin['ct_price'];
    $arr['mt_coin'] = (int)$row['mt_coin'] + (int)$coin['ct_amount'] + $bonus;
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 코인 결제 완료', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '코인 결제 완료', $jwt);
    }
} else {
    echo result_data("false", "해당하는 결제내역이 존재하지 않습니다.", "");
    exit;
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>