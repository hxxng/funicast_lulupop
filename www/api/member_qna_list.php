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
if($decoded_array['type']=="") {
    echo result_data('false', '잘못된 접근입니다. type', '');
    exit;
}

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "select *, a1.idx as mt_idx from member_t a1 where mt_level in (3,5) and mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_query($query);
if($row_m['mt_idx']) {
    if($decoded_array['type'] == "my") {
        $pt_idx = "is not null";
        $title = "나의";
    } else {
        $pt_idx = "is null";
        $title = "관리자";
    }
    $query = "select qna_t.*, rt_status, reporter_idx
        from qna_t left join report_t on report_t.report_idx = qna_t.idx and rt_table = 'qna_t'
        where pt_idx ".$pt_idx." and mt_idx = ".$row_m['mt_idx'];

    $list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));

    $count_query = "SELECT COUNT(idx) AS cnt 
            FROM (select qna_t.idx from qna_t 
            left join report_t on report_t.report_idx = qna_t.idx and rt_table = 'qna_t'
            where pt_idx ".$pt_idx." and mt_idx = ".$row_m['mt_idx']." group by qna_t.idx ) A ";
    $count = $DB->fetch_query($count_query);

    $n_page = ceil($count[0] / 20);

    if($list) {
        unset($arr);
        foreach ($list as $row) {
            if($row['rt_status'] != 2 || $row['rt_status'] == null) {
                if ($row['qt_status'] == 1) {
                    $qt_status = "답변대기";
                } else {
                    $qt_status = "답변완료";
                }
                $arr['list'][] = array(
                    "qt_idx" => $row['idx'],
                    "mt_idx" => $row['mt_idx'],
                    "qt_content" => $row['qt_content'],
                    "qt_wdate" => substr($row['qt_wdate'], 0, 10),
                    "qt_status" => $qt_status,
                );
            }
        }
        $arr['count'] = (int)$count[0];
        $arr['maxpage'] = (int)$n_page;
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] '.$title.' 문의 리스트', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', $title.' 문의 리스트', $jwt);
        }
    } else {
        $payload['data'] = [];
        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] '.$title.' 문의 리스트', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', $title.' 문의 리스트', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>