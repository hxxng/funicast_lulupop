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
    if($decoded_array['filter']) {
        if($decoded_array['filter'] == "cancel") {
            $ct_status = "ct_status in (7,8) and ";
        } else if($decoded_array['filter'] == "ready") {
            $ct_status = "ct_status = 3 and ";
        } else if($decoded_array['filter'] == "shipping") {
            $ct_status = "ct_status = 4 and ";
        } else if($decoded_array['filter'] == "finish") {
            $ct_status = "ct_status = 5 and ";
        } else if($decoded_array['filter'] == "exchange") {
            $ct_status = "ct_status in (80,81,82) and ";
        } else if($decoded_array['filter'] == "refund") {
            $ct_status = "ct_status in (90,91) and ";
        } else if($decoded_array['filter'] == "pay") {
            $ct_status = "ct_status = 2 and ";
        }
    } else {
        $ct_status = "";
    }

    if(empty($decoded_array['ot_hp'])) {
        $qq = 0;
        unset($list_ot);
        $query_ot = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_status > 0 and ".$ct_status."
            order_t.mt_idx = '" . $row['mt_idx'] . "' group by order_t.ot_code order by ot_wdate desc limit " . $item_count . ", " . ($item_count + 20);
        $list_ot = $DB->select_query($query_ot);
        $count = $DB->select_query("select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_status > 0 and ".$ct_status." order_t.mt_idx = " . $row['mt_idx'] . " group by order_t.ot_code");
        if($count) {
            $count = count($count);
            $n_page = ceil($count / 20);
        } else {
            $count = 0;
            $n_page = 1;
        }

        if ($list_ot) {
            foreach ($list_ot as $row_ot) {
                $btn_name = '';
                switch ($row_ot['ot_status']) {
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

                $query_pt = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.mt_idx = " . $row['mt_idx'] . " and ".$ct_status." order_t.ot_code = '" . $row_ot['ot_code'] . "'";
                $list_pt = $DB->select_query($query_pt);

                unset($items);
                if ($list_pt) {
                    foreach ($list_pt as $row_pt) {
                        $query_p = "select * from product_t where idx = " . $row_pt['pt_idx'];
                        $p_info = $DB->fetch_assoc($query_p);
                        $pt_image1 = ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1']."?cache=".strtotime($p_info['pt_udate']) : $ct_no_img_url);

                        if($row_pt['ct_status'] >= 90) {
                            $ct_request_status = $arr_refund_status[$row_pt['ct_request_status']];
                        } else if($row_pt['ct_status'] >= 80 && $row_pt['ct_status'] <= 82) {
                            $ct_request_status = $arr_exchange_status[$row_pt['ct_request_status']];
                        } else {
                            $ct_request_status = "취소완료";
                        }

                        if($row_pt['ct_status'] > 6) {
                            $arr['list'][] = array(
                                'ot_code' => $row_ot['ot_code'],
                                'ot_pcode' => $row_pt['ot_pcode'],
                                'mt_idx' => $row_ot['mt_idx'],
                                'ot_wdate' => $row_ot['ot_wdate'] ? date('Y-m-d', strtotime($row_ot['ot_wdate'])) : '',
                                'ot_pt_name' => $row_pt['pt_title'],
                                'ot_qty' => (int)$row_pt['ct_opt_qty'],
                                'ot_price' => (int)$row_pt['ct_price'],
                                'status_txt' => $ct_request_status,
                                'pt_image1' => $pt_image1,
                                'btn_name' => "",
                                'ot_pay_type' => $row_ot['ot_pay_type'],
                            );
                        }
                    }
                }
                if($row_ot['ct_status'] < 7) {
                    if($row_ot['ct_status'] == 1) {
                        if($row_ot['ot_pay_type'] != 2 && $row_ot['ct_status'] == 1 && ($row_ot['ot_barcode'] != "" || $row_ot['ot_account_num'] != "")) {
                            $arr['list'][] = array(
                                'ot_code' => $row_ot['ot_code'],
                                'ot_pcode' => $row_ot['ot_pcode'],
                                'mt_idx' => $row_ot['mt_idx'],
                                'ot_wdate' => $row_ot['ot_wdate'] ? date('Y-m-d', strtotime($row_ot['ot_wdate'])) : '',
                                'ot_pt_name' => $row_ot['ot_pt_name'],
                                'ot_qty' => (int)$row_ot['ot_qty'],
                                'ot_price' => (int)$row_ot['ot_price'],
                                'status_txt' => $arr_ct_status[$row_ot['ot_status']],
                                'pt_image1' => $pt_image1,
                                'btn_name' => $btn_name,
                                'ot_pay_type' => $row_ot['ot_pay_type'],
                            );
                        }
                    } else {
                        $arr['list'][] = array(
                            'ot_code' => $row_ot['ot_code'],
                            'ot_pcode' => $row_ot['ot_pcode'],
                            'mt_idx' => $row_ot['mt_idx'],
                            'ot_wdate' => $row_ot['ot_wdate'] ? date('Y-m-d', strtotime($row_ot['ot_wdate'])) : '',
                            'ot_pt_name' => $row_ot['ot_pt_name'],
                            'ot_qty' => (int)$row_ot['ot_qty'],
                            'ot_price' => (int)$row_ot['ot_price'],
                            'status_txt' => $arr_ct_status[$row_ot['ot_status']],
                            'pt_image1' => $pt_image1,
                            'btn_name' => $btn_name,
                            'ot_pay_type' => $row_ot['ot_pay_type'],
                        );
                    }
                }
            }
            $arr['count'] = $count;
            $arr['maxpage'] = (int)$n_page;
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 전체 주문내역리스트', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '전체 주문내역리스트', $jwt);
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
        $query_ot = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_status > 0 and ".$ct_status."
            order_t.nmt_id = '" . $decoded_array['mt_id'] . "' and order_t.ot_hp = '".$decoded_array['ot_hp']."' group by order_t.ot_code order by ot_wdate desc limit " . $item_count . ", " . ($item_count + 20);
        $list_ot = $DB->select_query($query_ot);
        $count = $DB->select_query("select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_status > 0 and ".$ct_status." order_t.nmt_id = '" . $decoded_array['mt_id'] . "' group by order_t.ot_code");
        if($count) {
            $count = count($count);
            $n_page = ceil($count / 20);
        } else {
            $count = 0;
            $n_page = 1;
        }

        if ($list_ot) {
            foreach ($list_ot as $row_ot) {
                $query_pt = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.nmt_id = '" . $decoded_array['mt_id'] . "' and ".$ct_status." order_t.ot_code = '" . $row_ot['ot_code'] . "'";
                $list_pt = $DB->select_query($query_pt);
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
                        }

                        if($row_pt['ct_status'] >= 90) {
                            $ct_request_status = $arr_refund_status[$row_pt['ct_request_status']];
                        } else if($row_pt['ct_status'] >= 80 && $row_pt['ct_status'] <= 82) {
                            $ct_request_status = $arr_exchange_status[$row_pt['ct_request_status']];
                        } else {
                            $ct_request_status = "취소완료";
                        }

                        $query_p = "select * from product_t where idx = " . $row_pt['pt_idx'];
                        $p_info = $DB->fetch_assoc($query_p);
                        $pt_image1 = ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1']."?cache=".strtotime($p_info['pt_udate']) : $ct_no_img_url);

                        if($row_pt['ct_status'] > 6) {
                            $arr['list'][] = array(
                                'ot_code' => $row_ot['ot_code'],
                                'ot_pcode' => $row_pt['ot_pcode'],
                                'nmt_id' => $row_ot['nmt_id'],
                                'ot_wdate' => $row_ot['ot_wdate'] ? date('Y-m-d', strtotime($row_ot['ot_wdate'])) : '',
                                'ot_pt_name' => $row_pt['pt_title'],
                                'ot_qty' => (int)$row_pt['ct_opt_qty'],
                                'ot_price' => (int)$row_pt['ct_price'],
                                'status_txt' => $ct_request_status,
                                'pt_image1' => $pt_image1,
                                'btn_name' => $btn_name,
                                'ot_pay_type' => $row_ot['ot_pay_type'],
                            );
                        }
                    }
                }
                if($row_pt['ct_status'] < 7) {
                    $arr['list'][] = array(
                        'ot_code' => $row_ot['ot_code'],
                        'ot_pcode' => $row_ot['ot_pcode'],
                        'nmt_id' => $row_ot['nmt_id'],
                        'ot_wdate' => $row_ot['ot_wdate'] ? date('Y-m-d', strtotime($row_ot['ot_wdate'])) : '',
                        'ot_pt_name' => $row_ot['ot_pt_name'],
                        'ot_qty' => (int)$row_ot['ot_qty'],
                        'ot_price' => (int)$row_ot['ot_price'],
                        'status_txt' => $arr_ct_status[$row_ot['ot_status']],
                        'pt_image1' => $pt_image1,
                        'btn_name' => $btn_name,
                        'ot_pay_type' => $row_ot['ot_pay_type'],
                    );
                }
            }
            $arr['count'] = $count;
            $arr['maxpage'] = (int)$n_page;
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 비회원 전체 주문내역리스트', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '비회원 전체 주문내역리스트', $jwt);
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