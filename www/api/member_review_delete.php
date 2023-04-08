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
if($decoded_array['review_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. review_idx', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_assoc($query);

if($row_m['mt_idx']) {
    $review_idx = explode(",", $decoded_array['review_idx']);
    for ($i = 0; $i < count($review_idx); $i++) {
        if($review_idx[$i]) {
            $query = "select * from review_product_t where idx = '" . $review_idx[$i] . "' and mt_idx = " . $row_m['mt_idx'];
            $review = $DB->fetch_assoc($query);
            if ($review) {
                $DB->del_query("review_product_t", " idx = " . $review['idx']);
                for ($i = 1; $i <= 5; $i++) {
                    @unlink($ct_img_dir_a . "/" . $review['rpt_img' . $i]);
                }
            } else {
                echo result_data("false", "삭제할 리뷰가 존재하지 않습니다.", "");
                exit;
            }
        }
    }

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 나의 후기 삭제', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '나의 후기 삭제', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>