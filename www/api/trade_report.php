<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;


if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['tt_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. tt_idx', '');
    exit;
}
if($decoded_array['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}
if($decoded_array['rt_category']=="") {
    echo result_data('false', '잘못된 접근입니다. rt_category', '');
    exit;
}
if($decoded_array['rt_content']=="") {
    echo result_data('false', '잘못된 접근입니다. rt_content', '');
    exit;
}

$query_m = "select *, a1.idx as mt_idx from member_t a1 where mt_level = 3 and mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_query($query_m);
if($row_m['mt_idx']) {
    $query = "select * from trade_t where idx = ".$decoded_array['tt_idx']." and tt_status = 1";
    $row = $DB->fetch_assoc($query);
    if($row['idx'] > 0) {
        $query = "select * from report_t where rt_type = 2 and rt_table = 'trade_t' and reporter_idx = ".$row_m['mt_idx']." and report_idx = ".$decoded_array['tt_idx'];
        $count = $DB->count_query($query);
        if($count > 0) {
            echo result_data("false", "이미 신고 처리한 글입니다.", "");
            exit;
        } else {
            $arr = array(
                "rt_type" => 2,
                "rt_status" => 1,
                "rt_category" => $decoded_array['rt_category'],
                "reporter_idx" => $row_m['mt_idx'],
                "report_idx" => $decoded_array['tt_idx'],
                "rt_content" => $decoded_array['rt_content'],
                "rt_table" => "trade_t",
                "rt_wdate" => "now()",
            );

            $DB->insert_query("report_t", $arr);
        }

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 중고거래 게시글 신고', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '중고거래 게시글 신고', $jwt);
        }
    } else {
        echo result_data("false", "신고할 게시글이 존재하지 않습니다.", "");
        exit;
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>