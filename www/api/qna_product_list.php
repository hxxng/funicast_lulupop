<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;


if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['pt_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. pt_idx', '');
    exit;
}

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "
		select *, a1.idx as mt_idx from member_t a1
		where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level = 3
	";
$row_m = $DB->fetch_query($query);

if($row_m['mt_idx']) {
    $where = " and qna_t.idx not in (SELECT report_idx FROM report_t where reporter_idx=".$row_m['mt_idx']." and rt_table = 'qna_t') ";
} else {
    $where = "";
}

$query = "select qna_t.*, rt_status, reporter_idx, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname, (SELECT mt_image FROM member_t WHERE idx=mt_idx) as mt_image, 
       (SELECT idx FROM member_t WHERE idx=mt_idx) as mt_idx, (SELECT mt_udate FROM member_t WHERE idx=mt_idx) as mt_udate 
        from qna_t left join report_t on report_t.report_idx = qna_t.idx and rt_table = 'qna_t'
        where pt_idx = ".$decoded_array['pt_idx'].$where." and qna_t.idx not in (SELECT report_idx FROM report_t where reporter_idx=".$row_m['mt_idx']." and rt_table = 'qna_t')";

if($decoded_array['mt_id']) {
    $query_m = "select *, a1.idx as mt_idx from member_t a1 where mt_level = 3 and mt_id = '".$decoded_array['mt_id']."'";
    $row_m = $DB->fetch_query($query_m);
}
if($decoded_array['chk_mt_id']) {
    if($row_m['mt_idx']) {
        $query .= " and mt_idx = ".$row_m['mt_idx'];
    }
}
$list = $DB->select_query($query." order by qt_wdate desc limit ".$item_count.", ".($item_count+20));

$count_query = "SELECT COUNT(idx) AS cnt 
            FROM (select qna_t.idx from qna_t 
            left join report_t on report_t.report_idx = qna_t.idx and rt_table = 'qna_t'
            where pt_idx = ".$decoded_array['pt_idx'].$where." group by qna_t.idx) A ";
$count = $DB->fetch_query($count_query);

$n_page = ceil($count[0] / 20);

if($list) {
    unset($arr);
    foreach ($list as $row) {
        $query = "select * from hide_t where ht_table = 'qna_t' and mt_idx = ".$row_m['mt_idx']." and ht_hide_idx = ".$row['idx'];
        $hide_count = $DB->count_query($query);
        if($hide_count > 0) {
            $hide_yn = "Y";
        } else {
            $hide_yn = "N";
        }
        if($row['rt_status'] != 2 || $row['rt_status'] == null) {
            if($row_m['mt_idx'] > 0 && $row_m['mt_idx'] == $row['reporter_idx']) {
                $arr['qna_product'][] = array(
                    "qt_idx" => $row['idx'],
                    "mt_idx" => $row['mt_idx'],
                    "mt_nickname" => $row['mt_nickname'],
                    "mt_image" => ($row['mt_image'] ? $ct_img_url . '/' . $row['mt_image']."?cache=".strtotime($row['mt_udate']) : $ct_member_no_img_url),
                    "qt_content" => "신고된 게시글입니다.",
                    "qt_answer" => $row['qt_answer'],
                    "qt_wdate" => substr($row['qt_wdate'], 0, 10),
                    "qt_status" => $qt_status,
                    "qt_secret" => $row['qt_secret'],
                    "hide_yn" => $hide_yn,
                );
            } else {
                if ($row['qt_status'] == 1) {
                    $qt_status = "답변대기";
                } else {
                    $qt_status = "답변완료";
                }
                if($row['qt_secret'] == "Y" && ($row_m['mt_idx'] == $row['mt_idx'])) {
                    $qt_content = $row['qt_content'];
                } else if($row['qt_secret'] == "Y" && ($row_m['mt_idx'] != $row['mt_idx'])) {
                    $qt_content = "비밀글입니다.";
                } else {
                    $qt_content = $row['qt_content'];
                }
                $arr['qna_product'][] = array(
                    "qt_idx" => $row['idx'],
                    "mt_idx" => $row['mt_idx'],
                    "mt_nickname" => $row['mt_nickname'],
                    "mt_image" => ($row['mt_image'] ? $ct_img_url . '/' . $row['mt_image']."?cache=".strtotime($row['mt_udate']) : $ct_member_no_img_url),
                    "qt_content" => $qt_content,
                    "qt_answer" => $row['qt_answer'],
                    "qt_wdate" => substr($row['qt_wdate'], 0, 10),
                    "qt_status" => $qt_status,
                    "qt_secret" => $row['qt_secret'],
                    "hide_yn" => $hide_yn,
                );
            }
        }
    }
    $arr['count'] = (int)$count[0];
    $arr['maxpage'] = (int)$n_page;
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 상품 문의 전체 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '상품 문의 전체 리스트', $jwt);
    }
} else {
    echo result_data('false', '상품 문의 리스트가 존재하지 않습니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>