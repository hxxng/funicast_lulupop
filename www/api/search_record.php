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
    $query = "select * from search_log_t where mt_idx = ".$row['mt_idx']." order by slt_wdate desc limit 10";
    $list = $DB->select_query($query);
    if($list) {
        foreach ($list as $row_s) {
            $arr['search'][] = array(
                "slt_idx" => $row_s['idx'],
                "slt_txt" => $row_s['slt_txt'],
            );
        }
    } else {
        $arr['search'] = [];
    }

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 스토어 검색 기록', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '스토어 검색 기록', $jwt);
    }
} else {
    echo result_data('false', '회원정보가 일치하지 않습니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>