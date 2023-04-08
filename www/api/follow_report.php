<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;


if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['ft_id']=="") {
    echo result_data('false', '잘못된 접근입니다. ft_id', '');
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

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['ft_id']."'";
$row2 = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    if($row2['mt_idx']) {
        $query = "select * from report_t where rt_type = 1 and rt_table = 'member_t' and reporter_idx = " . $row['mt_idx'] . " and reported_idx = " . $row2['mt_idx'];
        $count = $DB->count_query($query);
        if ($count > 0) {
            echo result_data("false", "이미 신고 처리한 사용자입니다.", "");
            exit;
        } else {
            $arr = array(
                "rt_type" => 1,
                "rt_status" => 1,
                "rt_category" => $decoded_array['rt_category'],
                "reporter_idx" => $row['mt_idx'],
                "reported_idx" => $row2['mt_idx'],
                "rt_content" => $decoded_array['rt_content'],
                "rt_table" => "member_t",
                "rt_wdate" => "now()",
            );
            $DB->insert_query("report_t", $arr);
        }

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 사용자 신고', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '사용자 신고', $jwt);
        }
    } else {
        echo result_data("false", "신고할 사용자가 존재하지 않습니다.", "");
        exit;
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>