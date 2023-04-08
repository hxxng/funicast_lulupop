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


$query_m = "select *, a1.idx as mt_idx from member_t a1 where mt_level = 3 and mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_query($query_m);
if($row_m['mt_idx']) {
    $arr['mt_point'] = (int)$row_m['mt_point'];
    $query = 'SELECT sum(plt_price) as sum_plt_price FROM point_log_t where mt_idx = '.$row_m['mt_idx'];
    $sum_point = $DB->fetch_assoc($query);
    $arr['now_point'] = (int)$sum_point['sum_plt_price'];

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 사용가능포인트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '사용가능포인트', $jwt);
    }

    //적립 포인트 쿼리
//    $query = 'SELECT sum(plt_price) FROM point_log_t where mt_idx = '.$row_m['mt_idx'].' and plt_type = "P"';

    //사용 포인트 쿼리
//    $query = 'SELECT sum(plt_price) FROM point_log_t where mt_idx = '.$row_m['mt_idx'].' and plt_type = "M"';
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>