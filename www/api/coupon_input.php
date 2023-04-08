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
    $query = "select * from member_coupon_t where mct_ct_code = '".$decoded_array['ct_code']."' and mt_idx = ".$row['mt_idx'];
    $chk = $DB->fetch_assoc($query);
    if($chk['idx'] > 0) {
        echo result_data("false", "이미 등록한 쿠폰입니다.", "");
        exit;
    } else {
        $query = "select * from coupon_t where ct_code = '".$decoded_array['ct_code']."' and ct_sdate <= CURDATE() and ct_edate >= CURDATE() ";
        $list = $DB->fetch_assoc($query);
        if($list['idx'] > 0) {
            if($list['ct_use_person'] > $list['ct_used_person']) {
                $DB->insert_query("member_coupon_t", array("mt_idx" => $row['mt_idx'], "mct_ct_code" => $decoded_array['ct_code'], "mct_wdate" => "now()"));
            } else {
                echo result_data("false", "사용가능 인원을 초과한 쿠폰입니다.", "");
                exit;
            }

            $arr['ct_code'] = $list['ct_code'];
            $arr['ct_name'] = $list['ct_name'];

            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 쿠폰 등록', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '쿠폰 등록', $jwt);
            }
        } else {
            echo result_data("false", "사용가능한 쿠폰이 아닙니다.", "");
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>