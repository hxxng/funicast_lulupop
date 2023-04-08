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
    if($decoded_array['type'] == 1) {
        $plt_type = " in (1,2,3)";
    } else if($decoded_array['type'] == 2) {
        $plt_type = " = 2";
    } else if($decoded_array['type'] == 3) {
        $plt_type = " = 3";
    }
    $query = "select * from pushnotification_log_t where plt_type ".$plt_type." and instr(op_idx, ".$row_m['mt_idx'].") order by idx desc";
    $list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
    $count = $DB->count_query($query);

    $n_page = ceil($count / 20);

    if($list) {
        unset($arr);
        foreach ($list as $row) {
            if($row['plt_table'] == 'order_t') {
                $link1 = "MyPage_Payment_Item_Detail_Page";
            } else if($row['plt_table'] == 'chat_t') {
                $link1 = "Chatting_Page";
            } else if($row['plt_table'] == 'trade_t') {
                $link1 = "UsedTrade_Detail_Comment_Page";
            } else if($row['plt_table'] == 'product_t') {
                $link1 = "Product_Detail_Page";
            } else if($row['plt_table'] == 'qna_t') {
                $link1 = "MyPage_QnADetail_Page";
            } else if($row['plt_table'] == 'event_t') {
                $link1 = "Event_Detail_Page";
            } else if($row['plt_table'] == 'community_t') {
                $link1 = "Community_Detail_Page";
            } else if($row['plt_table'] == "order_t_pay") {
                $link1 = "Product_Order_Finish_Page";
            } else if($row['plt_table'] == "coin_t_pay") {
                $link1 = "Random_Coin_Payment_Finish_Page";
            } else if($row['plt_table'] == "member_t") {
                $link1 = "MyPage_Point_Page";
            } else if($row['plt_table'] == "notice_t") {
                $link1 = "MyPage_Notice_Detail_Page";
            }

            $arr['list'][] = array(
                "plt_idx" => $row['idx'],
                "plt_title" => $row['plt_title'],
                "plt_content" => $row['plt_content'],
                "plt_wdate" => substr($row['plt_wdate'], 0, 10),
                "link1" => $link1,
                "link2" => $row['plt_index'],
            );
        }

        $arr['count'] = (int)$count;
        $arr['maxpage'] = (int)$n_page;
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 알림 리스트', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '알림 리스트', $jwt);
        }
    } else {
        $arr['count'] = (int)$count;
        $arr['maxpage'] = 1;
        $arr['list'] = [];
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 알림 리스트', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '알림 리스트', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>