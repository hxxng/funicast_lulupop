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
if($decoded_array['ct_code']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_code', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    $coin_list = $DB->fetch_assoc("select * from coin_t where mt_idx = ".$row['mt_idx']." and ct_code = '".$decoded_array['ct_code']."'");
    if($coin_list) {
        if($coin_list['ct_status'] == 4) {
            $ct_status = "구매 적립";
            $status = "적립";
            $ct_pdate = substr($coin_list['ct_pdate'],0,10);
            if($coin_list['ct_refund_status'] == 1) {
                $ct_refund_status = "환불요청";
                $ct_refund_date = null;
            } else {
                $ct_refund_status = "환불완료";
                $ct_refund_date = substr($coin_list['ct_refund_edate'],0,10);
            }
        }

        $arr = array(
            "ct_idx" => $coin_list['idx'],
            "ct_code" => $coin_list['ct_code'],
            "status" => $status,
            "ct_status" => $ct_status,
            "ct_amount" => $coin_list['ct_amount']."코인 충전",
            "ct_price" => (int)$coin_list['ct_price'],
            "ct_refund_status" => $ct_refund_status,
            "ct_pdate" => substr($coin_list['ct_pdate'],0,10),
            "ct_refund_date" => $ct_refund_date,
        );
        $payload['data'] = $arr;
    } else {
        $payload['data'] = null;
    }

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 코인 환불 상세', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '코인 환불 상세', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>