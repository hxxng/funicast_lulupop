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
    $query = 'SELECT * FROM coupon_t where ct_sdate <= curdate() and ct_edate >= curdate() 
             and (ct_used_person < ct_use_person) and ct_code in (select mct_ct_code from member_coupon_t where mt_idx = '.$row_m['mt_idx'].')';
    $list = $DB->select_query($query);
    if($list) {
        foreach ($list as $row) {
            $arr[] = array(
                "ct_code" => $row['ct_code'],
                "ct_name" => $row['ct_name'],
                "ct_sale_price" => (int)$row['ct_sale_price'],
                "ct_min_price" => (int)$row['ct_min_price'],
                "ct_edate" => $row['ct_edate'],
            );
        }
    }

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 사용가능쿠폰', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '사용가능쿠폰', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>