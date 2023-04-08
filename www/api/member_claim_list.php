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
if($decoded_array['filter']=="") {
    echo result_data('false', '잘못된 접근입니다. filter', '');
    exit;
}

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$arr = array();

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level in (3,5)
";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    if($decoded_array['filter'] == "cancel") {
        $ct_status = "ct_status in (7,8) and ";
        $title = "취소";
    } else if($decoded_array['filter'] == "exchange") {
        $ct_status = "ct_status in (80,81,82) and ";
        $title = "교환";
    } else if($decoded_array['filter'] == "refund") {
        $ct_status = "ct_status in (90,91) and ";
        $title = "반품";
    }
    if($row['mt_level'] == 3) {
        $qq = 0;
        unset($list_ot);
        $query_ot = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_select = 2 and ct_status > 0 and ".$ct_status." 
            order_t.mt_idx = '" . $row['mt_idx'] . "' group by order_t.ot_code order by ot_wdate desc limit " . $item_count . ", " . ($item_count + 20);
        $list_ot = $DB->select_query($query_ot);
        $count = $DB->select_query("select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_select = 2 and ct_status > 0 and ".$ct_status." order_t.mt_idx = " . $row['mt_idx']);
        if($count) {
            $count = count($count);
            $n_page = ceil($count / 20);
        } else {
            $count = 0;
        }

        if ($list_ot) {
            foreach ($list_ot as $row_ot) {
                $query_pt = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ".$ct_status." order_t.mt_idx = " . $row['mt_idx'] . " and order_t.ot_code = '" . $row_ot['ot_code'] . "'";
                $list_pt = $DB->select_query($query_pt);

                unset($items);
                if ($list_pt) {
                    foreach ($list_pt as $row_pt) {
                        $query_p = "select * from product_t where idx = " . $row_pt['pt_idx'];
                        $p_info = $DB->fetch_assoc($query_p);
                        $pt_image1 = ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1']."?cache=".strtotime($p_info['pt_udate']) : $ct_no_img_url);

                        if($title == "반품") {
                            $ct_request_status = $arr_refund_status[$row_pt['ct_request_status']];
                        } else if($title == "교환") {
                            $ct_request_status = $arr_exchange_status[$row_pt['ct_request_status']];
                        } else {
                            $ct_request_status = "취소완료";
                        }

                        $arr['list'][] = array(
                            'ot_code' => $row_ot['ot_code'],
                            'ot_pcode' => $row_pt['ot_pcode'],
                            'ot_wdate' => $row_ot['ot_wdate'] ? date('Y-m-d', strtotime($row_ot['ot_wdate'])) : '',
                            'ot_pt_name' => $row_pt['pt_title'],
                            'ct_opt_value' => $row_pt['ct_opt_value'],
                            'ot_qty' => (int)$row_pt['ct_opt_qty'],
                            'ot_price' => (int)$row_pt['ct_price'],
                            'ct_status_txt' => $arr_ct_status[$row_pt['ct_status']],
                            'pt_image1' => $pt_image1,
                            'ct_request_status' => $ct_request_status,
                        );
                    }
                }
            }
            $arr['count'] = $count;
            $arr['maxpage'] = (int)$n_page;
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] '.$title.'내역리스트', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', $title.'내역리스트', $jwt);
            }
        } else {
            $arr['list'] = [];
            $arr['maxpage'] = (int)$n_page;
            $arr['count'] = $count;
            echo result_data('false', '주문내역이 없습니다.', $arr);
        }
    } else {
        //비회원
        $qq = 0;
        unset($list_ot);
        $query_ot = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_select = 2 and ct_status > 0 and ".$ct_status."
            order_t.nmt_id = '" . $decoded_array['mt_id'] . "' group by order_t.ot_code order by ot_wdate desc limit " . $item_count . ", " . ($item_count + 20);
        $list_ot = $DB->select_query($query_ot);
        $count = $DB->select_query("select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_select = 2 and ct_status > 0 and ct_status in (80,81,82) and order_t.nmt_id = '" . $decoded_array['mt_id'] . "' group by order_t.ot_code");
        if($count) {
            $count = count($count);
            $n_page = ceil($count / 20);
        } else {
            $count = 0;
        }

        if ($list_ot) {
            foreach ($list_ot as $row_ot) {
                $query_pt = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ".$ct_status." order_t.nmt_id = '" . $decoded_array['mt_id'] . "' and order_t.ot_code = '" . $row_ot['ot_code'] . "'";
                $list_pt = $DB->select_query($query_pt);

                unset($items);
                if ($list_pt) {
                    foreach ($list_pt as $row_pt) {
                        $query_p = "select * from product_t where idx = " . $row_pt['pt_idx'];
                        $p_info = $DB->fetch_assoc($query_p);
                        $pt_image1 = ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1']."?cache=".strtotime($p_info['pt_udate']) : $ct_no_img_url);

                        if($title == "반품") {
                            $ct_request_status = $arr_refund_status[$row_pt['ct_request_status']];
                        } else if($title == "교환") {
                            $ct_request_status = $arr_exchange_status[$row_pt['ct_request_status']];
                        } else {
                            $ct_request_status = "취소완료";
                        }

                        $arr['list'][] = array(
                            'ot_code' => $row_ot['ot_code'],
                            'ot_pcode' => $row_pt['ot_pcode'],
                            'ot_wdate' => $row_ot['ot_wdate'] ? date('Y-m-d', strtotime($row_ot['ot_wdate'])) : '',
                            'ot_pt_name' => $row_pt['pt_title'],
                            'ct_opt_value' => $row_pt['ct_opt_value'],
                            'ot_qty' => (int)$row_pt['ct_opt_qty'],
                            'ot_price' => (int)$row_pt['ct_price'],
                            'ct_status_txt' => $arr_ct_status[$row_pt['ct_status']],
                            'pt_image1' => $pt_image1,
                            'ct_request_status' => $ct_request_status,
                        );
                    }
                }
            }
            $arr['count'] = $count;
            $arr['maxpage'] = (int)$n_page;
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 비회원 '.$title.'내역리스트', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '비회원 '.$title.'내역리스트', $jwt);
            }
        } else {
            $arr['list'] = [];
            $arr['maxpage'] = (int)$n_page;
            $arr['count'] = $count;
            echo result_data('false', '주문내역이 없습니다.', $arr);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>