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
if($decoded_array['tt_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. tt_idx', '');
    exit;
}

$query_m = "select *, a1.idx as mt_idx from member_t a1 where mt_level = 3 and mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_query($query_m);
if($row_m['mt_idx']) {
    $query = "select * from trade_t where mt_idx = ".$row_m['mt_idx']." and idx = ".$decoded_array['tt_idx'];
    $row = $DB->fetch_assoc($query);
    if($row['idx'] > 0) {
        for($i=1; $i<=10; $i++) {
            @unlink($ct_img_dir_a."/".$row['tt_img'.$i]);
        }
        $DB->del_query("trade_t", " idx = ".$decoded_array['tt_idx']);
        $DB->del_query("comment_t", " ct_idx = ".$decoded_array['tt_idx']." and ct_table = 'trade_t'");
        $DB->del_query("hide_t", " ht_table = 'trade_t' and ht_hide_idx = ".$decoded_array['tt_idx']);

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 게시글 삭제', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '게시글 삭제', $jwt);
        }
    } else {
        echo result_data("false", "삭제할 게시글이 존재하지 않습니다.", "");
        exit;
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>