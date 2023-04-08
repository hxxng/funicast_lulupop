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
    if($decoded_array['filter'] == "ready") {
        $ct_status = "ct_status in (2,3) and ";
        $title = "결제완료/배송준비중";
    } else if($decoded_array['filter'] == "shipping") {
        $ct_status = "ct_status = 4 and ";
        $title = "배송중";
    } else if($decoded_array['filter'] == "finish") {
        $ct_status = "ct_status = 5 and ";
        $title = "배송완료";
    }
    if($row['mt_level'] == 3) {
        $qq = 0;
        unset($list_ot);
        $query_ot = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_select = 2 and ct_status > 0 and ".$ct_status."
            order_t.mt_idx = '" . $row['mt_idx'] . "' group by order_t.ot_code order by ot_wdate desc limit " . $item_count . ", " . ($item_count + 20);
        $list_ot = $DB->select_query($query_ot);
        $count = $DB->select_query("select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_select = 2 and ct_status > 0 and ".$ct_status." order_t.mt_idx = " . $row['mt_idx'] . " group by order_t.ot_code");
        if($count) {
            $count = count($count);
            $n_page = ceil($count / 20);
        } else {
            $count = 0;
            $n_page = 1;
        }

        if ($list_ot) {
            foreach ($list_ot as $row_ot) {
                unset($items);
                $btn_name = '';
                switch ($row_ot['ct_status']) {
                    case '1':
                    case '2':
                    case '3':
                        $btn_name = '주문취소';
                        break;
                    case '4':
                        $ot_status_date = $row_ot['ot_dedate'];
                        $btn_name = '반품요청,교환요청';
                        break;
                    case '5':
                        $btn_name = '반품요청,교환요청,구매확정';
                        break;
                }

                $query_p = "select * from product_t where idx = " . $row_ot['pt_idx'];
                $p_info = $DB->fetch_assoc($query_p);
                $pt_image1 = ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1']."?cache=".strtotime($p_info['pt_udate']) : $ct_no_img_url);

                $arr['list'][] = array(
                    'ot_code' => $row_ot['ot_code'],
                    'nmt_id' => $row_ot['nmt_id'],
                    'ot_wdate' => $row_ot['ot_wdate'] ? date('Y-m-d', strtotime($row_ot['ot_wdate'])) : '',
                    'ot_pt_name' => $row_ot['ot_pt_name'],
                    'ot_qty' => (int)$row_ot['ot_qty'],
                    'ot_price' => (int)$row_ot['ot_price'],
                    'ct_status_txt' => $arr_ct_status[$row_ot['ot_status']],
                    'pt_image1' => $pt_image1,
                    'btn_name' => $btn_name,
                    'ot_pay_type' => $row_ot['ot_pay_type'],
                );
            }
            $arr['count'] = $count;
            $arr['maxpage'] = (int)$n_page;
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] '.$title.' 리스트', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', ''.$title.' 리스트', $jwt);
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
        $count = $DB->select_query("select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_select = 2 and ct_status > 0 and ".$ct_status." order_t.nmt_id = '" . $decoded_array['mt_id'] . "' group by order_t.ot_code");
        if($count) {
            $count = count($count);
            $n_page = ceil($count / 20);
        } else {
            $count = 0;
            $n_page = 1;
        }

        if ($list_ot) {
            foreach ($list_ot as $row_ot) {
                unset($items);
                $btn_name = '';
                switch ($row_ot['ct_status']) {
                    case '1':
                    case '2':
                    case '3':
                        $btn_name = '주문취소';
                        break;
                    case '4':
                        $ot_status_date = $row_ot['ot_dedate'];
                        $btn_name = '반품요청,교환요청';
                        break;
                    case '5':
                        $btn_name = '반품요청,교환요청,구매확정';
                        break;
                }

                $query_p = "select * from product_t where idx = " . $row_ot['pt_idx'];
                $p_info = $DB->fetch_assoc($query_p);
                $pt_image1 = ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1']."?cache=".strtotime($p_info['pt_udate']) : $ct_no_img_url);

                $arr['list'][] = array(
                    'ot_code' => $row_ot['ot_code'],
                    'nmt_id' => $row_ot['nmt_id'],
                    'ot_wdate' => $row_ot['ot_wdate'] ? date('Y-m-d', strtotime($row_ot['ot_wdate'])) : '',
                    'ot_pt_name' => $row_ot['ot_pt_name'],
                    'ot_qty' => (int)$row_ot['ot_qty'],
                    'ot_price' => (int)$row_ot['ot_price'],
                    'ct_status_txt' => $arr_ct_status[$row_ot['ot_status']],
                    'pt_image1' => $pt_image1,
                    'btn_name' => $btn_name,
                    'ot_pay_type' => $row_ot['ot_pay_type'],
                );
            }
            $arr['count'] = $count;
            $arr['maxpage'] = (int)$n_page;
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 비회원 '.$title.' 리스트', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '비회원 '.$title.' 리스트', $jwt);
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