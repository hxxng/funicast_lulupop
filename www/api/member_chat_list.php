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

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_assoc($query);

if($row_m['mt_idx']) {
    $query = "select chat_t.*, (SELECT mt_image FROM member_t WHERE idx=mt_idx) as mt_image1, (SELECT mt_image FROM member_t WHERE idx=ct_mt_idx) as mt_image2,
       (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname1, (SELECT mt_nickname FROM member_t WHERE idx=ct_mt_idx) as mt_nickname2, 
       (SELECT mt_id FROM member_t WHERE idx=mt_idx) as mt_id1, (SELECT mt_id FROM member_t WHERE idx=ct_mt_idx) as mt_id2,
       (SELECT mt_udate FROM member_t WHERE idx=ct_mt_idx) as mt_udate 
        from chat_t left join member_t m1 on m1.idx = chat_t.mt_idx left join member_t m2 on m2.idx = chat_t.ct_mt_idx 
        where (mt_idx = ".$row_m['mt_idx']." or ct_mt_idx = ".$row_m['mt_idx'].") and (instr(ct_out_idx, ".$row_m['mt_idx'].") = 0 or instr(ct_out_idx, ".$row_m['mt_idx'].") is null) and m1.mt_level = 3 and m2.mt_level = 3 ";
    $list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
    $count_chat = $DB->count_query($query);
    $n_page = ceil($count_chat[0] / 20);

    if($list) {
        foreach ($list as $row) {
            $query = "select * from chat_detail_t where ct_idx = ".$row['idx']." and (instr(cdt_out_idx, ".$row_m['mt_idx'].") = 0 or instr(cdt_out_idx, ".$row_m['mt_idx'].") is null) order by cdt_wdate desc";
            $detail = $DB->fetch_assoc($query);

            if($row['mt_idx'] == $row_m['mt_idx']) {
                if($row['mt_image2']) {
                    $mt_image = $ct_img_url."/".$row['mt_image2']."?cache=".strtotime($row['mt_udate']);
                } else {
                    $mt_image = $ct_member_no_img_url;
                }
                $mt_idx = $row['ct_mt_idx'];
                $mt_nickname = $row['mt_nickname2'];
                $mt_id = $row['mt_id2'];
            } else {
                if($row['mt_image1']) {
                    $mt_image = $ct_img_url."/".$row['mt_image1']."?cache=".strtotime($row['mt_udate']);
                } else {
                    $mt_image = $ct_member_no_img_url;
                }
                $mt_idx = $row['mt_idx'];
                $mt_nickname = $row['mt_nickname1'];
                $mt_id = $row['mt_id1'];
            }

            $query = "select * from hide_t where ht_table = 'chat_t' and mt_idx = ".$row_m['mt_idx']." and ht_hide_idx = ".$row['idx'];
            $count = $DB->count_query($query);
            if($count > 0) {
                $hide_yn = "Y";
            } else {
                $hide_yn = "N";
            }

            $today = date("Y-m-d", time());
            $yesterday = date('Y-m-d', strtotime('-1 day'));

            if($row['ct_ldate'] == null) {
                $strDate_ex1 = explode(' ', $row['ct_wdate']);
                $strDate_ex2 = explode('-', $strDate_ex1[0]);
                if($strDate_ex1[0] == $today) {
                    $date = substr($row['ct_wdate'],11,5);
                } else if($strDate_ex1[0] == $yesterday) {
                    $date = "어제";
                } else {
                    $date = preg_replace('/(0)(\d)/','$2', $strDate_ex2[1])."월".preg_replace('/(0)(\d)/','$2', $strDate_ex2[2])."일";
                }
            } else {
                $strDate_ex1 = explode(' ', $row['ct_ldate']);
                $strDate_ex2 = explode('-', $strDate_ex1[0]);
                if($strDate_ex1[0] == $today) {
                    $date = substr($row['ct_ldate'],11,5);
                } else if($strDate_ex1[0] == $yesterday) {
                    $date = "어제";
                } else {
                    $date = preg_replace('/(0)(\d)/','$2', $strDate_ex2[1])."월".preg_replace('/(0)(\d)/','$2', $strDate_ex2[2])."일";
                }
            }

            $query = "SELECT count(*) as cnt FROM chat_detail_t where ct_idx = ".$row['idx']." and cdt_recipient_idx = ".$row_m['mt_idx']." and cdt_read = 'N' and instr(cdt_out_idx, ".$mt_idx.") is null";
            $read = $DB->fetch_assoc($query);

            if((int)$read['cnt'] == 0) {
                $cnt = null;
            } else {
                $cnt = (int)$read['cnt'];
            }

            $arr['list'][] = array(
                "ct_idx" => $row['idx'],
                "mt_idx" => $mt_idx,
                "mt_id" => $mt_id,
                "mt_image" => $mt_image,
                "mt_nickname" => $mt_nickname,
                "cdt_content" => $detail['cdt_content'],
                "ct_ldate" => $date,
                "hide_yn" => $hide_yn,
                "cdt_read" => $cnt,
            );
        }
    } else {
        $arr['list'] = [];
    }

    $arr['count'] = (int)$count_chat;
    if($n_page < 1) {
        $n_page = 1;
    }
    $arr['maxpage'] = (int)$n_page;

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 나의 챗 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '나의 챗 리스트', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>