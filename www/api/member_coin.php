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

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    $query = "select sum(ct_amount) as sum_ct_amount from coin_t where mt_idx = ".$row['mt_idx']." and ct_type = 1 and ct_status in (2,3)";
    $a_coin = $DB->fetch_assoc($query);

    $arr['a_coin'] = (int)$a_coin['sum_ct_amount'];

    $query = "select sum(ct_amount) as sum_ct_amount from coin_t where mt_idx = ".$row['mt_idx']." and ct_type = 2 ";
    $b_coin = $DB->fetch_assoc($query);

    $arr['b_coin'] = (int)$b_coin['sum_ct_amount'];

    $arr['sum_coin'] = $a_coin['sum_ct_amount'] + $b_coin['sum_ct_amount'];

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 보유 코인', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '보유 코인', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>