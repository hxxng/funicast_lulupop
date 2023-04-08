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
    $query = "select * from member_draw_t where mt_idx = ".$row['mt_idx']." order by mdt_wdate desc limit 1";
    $draw = $DB->fetch_assoc($query);
    if($draw) {
        $now = DateTime::createFromFormat('U.u', microtime(true));
        $now->setTimeZone(new DateTimeZone('Asia/Seoul'));
        $date = $now->format("Y-m-d H:i:s.u");
        $stamp = $now->getTimestamp();

        if($draw['mdt_rdate']) {
            if (strtotime($draw['mdt_rdate']) <= $stamp) {
                $payload['data'] = "";
                if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                    echo result_data('false', '[debug] 이미 배송요청 되었습니다.', $payload);
                } else {
                    $jwt = JWT::encode($payload, $secret_key);
                    echo result_data('false', '이미 배송요청 되었습니다.', $jwt);
                }
                return false;
            }
        }
    }

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 뽑기 선택상품 배송 가능', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '뽑기 선택상품 배송 가능', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>