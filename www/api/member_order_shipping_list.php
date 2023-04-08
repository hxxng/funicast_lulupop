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

$arr = array();

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level in (3,5)
";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    if($row['mt_level'] == 3) {
        $qq = 0;
        unset($list_ot);
        $query_ot = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_select = 2 and ct_status > 0 and ct_status = 4 and
            order_t.mt_idx = '" . $row['mt_idx'] . "' group by order_t.ot_code order by ot_wdate desc limit " . $item_count . ", " . ($item_count + 20);
        $list_ot = $DB->select_query($query_ot);
        $count = $DB->select_query("select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_select = 2 and ct_status > 0 and ct_status = 4 and order_t.mt_idx = " . $row['mt_idx'] . " group by order_t.ot_code");
        if($count) {
            $count = count($count);
        } else {
            $count = 0;
        }

        if ($list_ot) {
            foreach ($list_ot as $row_ot) {
                $query_pt = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.mt_idx = " . $row['mt_idx'] . " and order_t.ot_code = '" . $row_ot['ot_code'] . "'";
                $list_pt = $DB->select_query($query_pt);

                $disable = "";
                if ($row_ot['ct_cdate']) {
                    $today = date("Y-m-d", time());
                    $ct_cdate = date("Y-m-d", strtotime("+1 month", strtotime($row_ot['ct_cdate'])));
                    if (strtotime($today) >= strtotime($ct_cdate)) {
                        $disable = "disabled";
                    }
                }
                unset($items);
                if ($list_pt) {
                    foreach ($list_pt as $row_pt) {
                        $query_r = "select * from review_product_t where pt_idx = " . $row_pt["pt_idx"] . " and ot_pcode = '" . $row_pt['ot_pcode'] . "' and mt_idx = " . $row['mt_idx'];
                        $count_rpt = $DB->count_query($query_r);
                        if ($count_rpt > 0) {
                            $disable = "disabled";
                        }
                        $btn_name = '';
                        switch ($row_pt['ct_status']) {
                            case '1':
                            case '2':
                            case '3':
                                $btn_name = '주문취소';
                                break;
                            case '4':
                                $ot_status_date = $row_pt['ot_dedate'];
                                $btn_name = '반품요청,교환요청';
                                break;
                            case '5':
                                $btn_name = '반품요청,교환요청,구매확정';
                                break;
                            case '6':
                                $ot_status_date = $row_pt['ot_cdate'];
                                $btn_name = 'review_btn ' . $disable;
                                break;
                        }

                        $query_p = "select * from product_t where idx = " . $row_pt['pt_idx'];
                        $p_info = $DB->fetch_assoc($query_p);
                        $pt_image1 = ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1'] : $ct_no_img_url);

                        $arr['order'][] = array(
                            'ot_code' => $row_ot['ot_code'],
                            'mt_idx' => $row_ot['mt_idx'],
                            'ot_wdate' => $row_ot['ot_wdate'] ? date('Y-m-d', strtotime($row_ot['ot_wdate'])) : '',
                            'ot_pt_name' => $row_ot['ot_pt_name'],
                            'ot_qty' => (int)$row_ot['ot_qty'],
                            'ot_price' => (int)$row_ot['ot_price'],
                            'ct_status_txt' => $arr_ct_status[$row_ot['ot_status']],
                            'pt_image1' => $pt_image1,
                            'btn_name' => $btn_name,
                        );
                    }
                }
            }
            $arr['count'] = $count;
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 배송중 리스트', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '배송중 리스트', $jwt);
            }
        } else {
            echo result_data('false', '주문내역이 없습니다.', $arr);
        }
    } else {
        //비회원
        $qq = 0;
        unset($list_ot);
        $query_ot = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_select = 2 and ct_status > 0 and ct_status = 4 and
            order_t.nmt_id = '" . $decoded_array['mt_id'] . "' group by order_t.ot_code order by ot_wdate desc limit " . $item_count . ", " . ($item_count + 20);
        $list_ot = $DB->select_query($query_ot);
        $count = $DB->select_query("select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_select = 2 and ct_status > 0 and ct_status = 4 and order_t.nmt_id = '" . $decoded_array['mt_id'] . "' group by order_t.ot_code");
        $count = count($count);

        if ($list_ot) {
            foreach ($list_ot as $row_ot) {
                $query_pt = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.nmt_id = '" . $decoded_array['mt_id'] . "' and order_t.ot_code = '" . $row_ot['ot_code'] . "'";
                $list_pt = $DB->select_query($query_pt);

                $disable = "";
                if ($row_ot['ct_cdate']) {
                    $today = date("Y-m-d", time());
                    $ct_cdate = date("Y-m-d", strtotime("+1 month", strtotime($row_ot['ct_cdate'])));
                    if (strtotime($today) >= strtotime($ct_cdate)) {
                        $disable = "disabled";
                    }
                }
                unset($items);
                if ($list_pt) {
                    foreach ($list_pt as $row_pt) {
                        $btn_name = '';
                        switch ($row_pt['ct_status']) {
                            case '1':
                            case '2':
                            case '3':
                                $btn_name = '주문취소';
                                break;
                            case '4':
                                $ot_status_date = $row_pt['ot_dedate'];
                                $btn_name = '반품요청,교환요청';
                                break;
                            case '5':
                                $btn_name = '반품요청,교환요청,구매확정';
                                break;
                            case '6':
                                $ot_status_date = $row_pt['ot_cdate'];
                                $btn_name = 'review_btn ' . $disable;
                                break;
                        }

                        $query_p = "select * from product_t where idx = " . $row_pt['pt_idx'];
                        $p_info = $DB->fetch_assoc($query_p);
                        $pt_image1 = ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1'] : $ct_no_img_url);

                        $arr['order'][] = array(
                            'ot_code' => $row_ot['ot_code'],
                            'nmt_id' => $row_ot['nmt_id'],
                            'ot_wdate' => $row_ot['ot_wdate'] ? date('Y-m-d', strtotime($row_ot['ot_wdate'])) : '',
                            'ot_pt_name' => $row_ot['ot_pt_name'],
                            'ot_qty' => (int)$row_ot['ot_qty'],
                            'ot_price' => (int)$row_ot['ot_price'],
                            'ct_status_txt' => $arr_ct_status[$row_ot['ot_status']],
                            'pt_image1' => $pt_image1,
                            'btn_name' => $btn_name,
                        );
                    }
                }
            }
            $arr['count'] = $count;
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 비회원 배송중 리스트', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '비회원 배송중 리스트', $jwt);
            }
        } else {
            echo result_data('false', '주문내역이 없습니다.', $arr);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>