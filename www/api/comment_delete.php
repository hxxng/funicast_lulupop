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
if($decoded_array['ct_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_idx', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    $query = "select * from comment_t where idx = ".$decoded_array['ct_idx']." and mt_idx = ".$row['mt_idx'];
    $count = $DB->count_query($query);
    if($count > 0) {
        $DB->del_query("comment_t", " idx = ".$decoded_array['ct_idx']);
        $DB->del_query("comment_t", " ct_parent_idx = ".$decoded_array['ct_idx']);

        $payload['data'] = null;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 댓글 삭제', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '댓글 삭제', $jwt);
        }
    } else {
        echo result_data("false", "삭제할 댓글이 존재하지 않습니다.", "");
        exit;
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>