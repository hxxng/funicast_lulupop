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
if($decoded_array['qt_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. qt_idx', '');
    exit;
}
if($decoded_array['qt_content']=="") {
    echo result_data('false', '잘못된 접근입니다. qt_content', '');
    exit;
}

$query = "select *, a1.idx as mt_idx from member_t a1 where mt_level in (3,5) and mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_query($query);
if($row_m['mt_idx']) {
    $query = "select * from qna_t where idx = ".$decoded_array['qt_idx'];
    $row = $DB->fetch_assoc($query);

    if($row) {
        unset($arr);
        $arr = array(
            "qt_content" => $decoded_array['qt_content'],
            "qt_udate" => "now()",
        );
        $DB->update_query("qna_t", $arr, " idx = ".$decoded_array['qt_idx']);

        unset($arr);
        if ($row['qt_status'] == 1) {
            $qt_status = "답변대기";
        } else {
            $qt_status = "답변완료";
        }

        $arr['qna'] = array(
            "qt_idx" => $row['idx'],
            "mt_idx" => $row['mt_idx'],
            "qt_content" => $decoded_array['qt_content'],
            "qt_wdate" => substr($row['qt_wdate'], 0, 10),
            "qt_status" => $qt_status,
            "qt_answer" => $row['qt_answer'],
        );

        if($row['pt_idx']) {
            $query = "select * from product_t where idx = ".$row['pt_idx'];
            $product = $DB->fetch_assoc($query);
            if($product['idx']) {
                $product = array(
                    "pt_idx" => $product['idx'],
                    "pt_title" => $product['pt_title'],
                    "pt_selling_price" => (int)$product['pt_selling_price'],
                    "pt_sale_chk" => $product['pt_sale_chk'],
                    "pt_price" => (int)$product['pt_price'],
                );
            }

            $arr['product'] = $product;
            $title = "나의";
        } else {
            $title = "관리자";
        }

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] '.$title.' 문의 수정', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', $title.' 문의 수정', $jwt);
        }
    } else {
        echo result_data('false', '문의가 존재하지 않습니다.', $arr);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>