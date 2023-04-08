<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/lib/Point_class.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}

if($decoded_array['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}
if($decoded_array['ot_code']=="") {
    echo result_data('false', '잘못된 접근입니다. ot_code', '');
    exit;
}
if($decoded_array['ot_price']=="") {
    echo result_data('false', '잘못된 접근입니다. ot_price', '');
    exit;
}

$arr = array();

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level = 3
";
$row = $DB->fetch_query($query);

if($row['mt_idx'] > 0) {
    $query_ot = "
        select * from order_t a1
        where a1.ot_code = '".$decoded_array['ot_code']."'
        and a1.ot_price = '".$decoded_array['ot_price']."'
    ";
    $row_ot = $DB->fetch_query($query_ot);

    if($row_ot['ot_code']!='') {
        if($row_ot['ot_pay_type'] == 1){
            $status = 1;
        } else {
            $status = 2;
        }
        unset($arr_query);
        $arr_query = array(
            "ot_status" => $status,
            "ot_pdate" => "now()",
        );

        $where_query = " ot_code = '".$row_ot['ot_code']."'";

        $DB->update_query('order_t', $arr_query, $where_query);
        $DB->update_query('cart_t', array("ct_select" => 2, "ct_status" => $status, "ct_pdate" => "now()"), " ot_code = '".$row_ot['ot_code']."'");

        //포인트 차감 쿼리
        $objPoint = new Point_class(array('db'=>$DB, 'mt_idx'=>$row['mt_idx']));
        if($row_ot['ot_use_point'] > 0) $objPoint->insert_point(array('point'=>(-1) * $row_ot['ot_use_point'], 'ot_code'=>$row_ot['ot_code'], 'plt_memo'=>"주문번호 ".$row_ot['ot_code']." 결제"));

        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.mt_idx = ".$row['mt_idx']." and cart_t.ot_code = '".$row_ot['ot_code']."'";
        $sql_query = $query." order by ot_wdate desc";
        $list_ot = $DB->select_query($sql_query);
        $list_fetch = $DB->fetch_assoc($sql_query);
        if($list_ot) {
            foreach ($list_ot as $row_ot) {
                $query_p = "select * from product_t where idx = " . $row_ot['pt_idx'];
                $p_info = $DB->fetch_assoc($query_p);
                if($p_info['pt_sale_chk'] == "Y") {
                    //할인 전 금액
                    $ct_opt_before_price = (int)$p_info['pt_selling_price'];
                } else {
                    $ct_opt_before_price = $row_ot['ct_opt_price'];
                }
                if ($p_info) {
                    $items[] = array(
                        'pt_idx' => $row_ot['pt_idx'],
                        'pt_title' => $row_ot['pt_title'],
                        'ct_opt_name' => $row_ot['ct_opt_name'],
                        'ct_opt_value' => $row_ot['ct_opt_value'],
                        'ct_opt_qty' => (int)$row_ot['ct_opt_qty'],
                        'ct_price' => (int)$row_ot['ct_price'],
                        'pt_image1' => ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1'] : $ct_no_img_url),
                    );
                }
                $product_price += $row_ot['ct_price'];

                if($p_info['pt_stock_chk'] == "Y") {
                    //재고 빼기
                    $DB->update_query('product_t', array("pt_stock" => $p_info['pt_stock'] - $row_ot['ct_opt_qty']), " idx = ".$row_ot['pt_idx']);
                }

                $arr['info'] = array(
                    'ot_wdate' => str_replace('-', '.', substr($list_fetch['ot_wdate'], 0, 10)),
                    'ot_pay_type' => $arr_ct_method[$list_fetch['ot_pay_type']],
                    'ot_use_point' => ($list_fetch['ot_use_point'] == null ? 0 : (int)$list_fetch['ot_use_point']),
                    'ot_use_coupon_price' => ($list_fetch['ot_use_coupon_price'] == null ? 0 : (int)$list_fetch['ot_use_coupon_price']),
                    'ot_price' => (int)$list_fetch['ot_price'],                //주문금액
                    'ot_product_price' => (int)$product_price,    //상품금액
                    'ot_delivery_charge' => ($list_fetch['ot_delivery_charge'] == 0 ? "무료배송" : (int)$list_fetch['ot_delivery_charge']),
                    'ot_point' => (int)$list_fetch['ot_point']
                );
                $arr['orderer'] = array(
                    'ot_name' => $list_fetch['ot_name'],
                    'ot_hp' => $list_fetch['ot_hp'],
                    'ot_zip' => $list_fetch['ot_zip'],
                    'ot_add1' => $list_fetch['ot_add1'],
                    'ot_add2' => $list_fetch['ot_add2'],
                );
                $arr['recipient'] = array(
                    'ot_b_name' => $list_fetch['ot_b_name'],
                    'ot_b_hp' => $list_fetch['ot_b_hp'],
                    'ot_b_zip' => $list_fetch['ot_b_zip'],
                    'ot_b_addr1' => $list_fetch['ot_b_addr1'],
                    'ot_b_addr2' => $list_fetch['ot_b_addr2'],
                    'ot_requests' => $list_fetch['ot_requests'],
                );

                $ot_deposit_date = date("Y-m-d H:i:s",strtotime("+1 week", strtotime($list_fetch['ot_wdate'])));
                if($list_fetch['ot_pay_type'] == 1) {
                    $query = "select * from policy_t where idx = 1";
                    $policy = $DB->fetch_assoc($query);
                    $arr['deposit'] = array(
                        "pt_account_name" => $policy['pt_account_name'],
                        "pt_account_bank" => $policy['pt_account_bank'],
                        "pt_account_number" => $policy['pt_account_number'],
                        "ot_deposit_date" => $ot_deposit_date,
                    );
                }
                $arr['items'] = $items;
            }
        }

        $payload['data'] = $arr;
        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 0원 결제 완료되었습니다.', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '0원 결제 완료되었습니다.', $jwt);
        }
    } else {
        echo result_data('false', $decoded_array['ot_code'].' | '.$decoded_array['ot_price'].' 존재하지 않는 주문정보입니다.', $arr);
    }
} else {
    $query_ot = "
        select * from order_t a1
        where a1.ot_code = '".$decoded_array['ot_code']."'
        and a1.ot_price = '".$decoded_array['ot_price']."'
    ";
    $row_ot = $DB->fetch_query($query_ot);

    if($row_ot['ot_code']!='') {
        if($row_ot['ot_pay_type'] == 1){
            $status = 1;
        } else {
            $status = 2;
        }
        unset($arr_query);
        $arr_query = array(
            "ot_status" => $status,
            "ot_pdate" => "now()",
        );

        $where_query = " ot_code = '".$row_ot['ot_code']."'";

        $DB->update_query('order_t', $arr_query, $where_query);
        $DB->update_query('cart_t', array("ct_select" => 2, "ct_status" => $status, "ct_pdate" => "now()"), " ot_code = '".$row_ot['ot_code']."'");

        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.nmt_id = '".$decoded_array['mt_id']."' and cart_t.ot_code = '".$row_ot['ot_code']."'";
        $sql_query = $query." order by ot_wdate desc";
        $list_ot = $DB->select_query($sql_query);
        $list_fetch = $DB->fetch_assoc($sql_query);
        if($list_ot) {
            foreach ($list_ot as $row_ot) {
                $query_p = "select * from product_t where idx = " . $row_ot['pt_idx'];
                $p_info = $DB->fetch_assoc($query_p);
                if($p_info['pt_sale_chk'] == "Y") {
                    //할인 전 금액
                    $ct_opt_before_price = (int)$p_info['pt_selling_price'];
                } else {
                    $ct_opt_before_price = $row_ot['ct_opt_price'];
                }
                if ($p_info) {
                    $items[] = array(
                        'pt_idx' => $row_ot['pt_idx'],
                        'pt_title' => $row_ot['pt_title'],
                        'ct_opt_name' => $row_ot['ct_opt_name'],
                        'ct_opt_value' => $row_ot['ct_opt_value'],
                        'ct_opt_qty' => (int)$row_ot['ct_opt_qty'],
                        'ct_price' => (int)$row_ot['ct_price'],
                        'pt_image1' => ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1'] : $ct_no_img_url),
                    );
                }
                $product_price += $row_ot['ct_price'];

                if($p_info['pt_stock_chk'] == "Y") {
                    //재고 빼기
                    $DB->update_query('product_t', array("pt_stock" => $p_info['pt_stock'] - $row_ot['ct_opt_qty']), " idx = ".$row_ot['pt_idx']);
                }

                $arr['info'] = array(
                    'ot_wdate' => str_replace('-', '.', substr($list_fetch['ot_wdate'], 0, 10)),
                    'ot_pay_type' => $arr_ct_method[$list_fetch['ot_pay_type']],
                    'ot_price' => (int)$list_fetch['ot_price'],   //주문금액
                    'ot_product_price' => (int)$product_price,    //상품금액
                    'ot_delivery_charge' => ($list_fetch['ot_delivery_charge'] == 0 ? "무료배송" : (int)$list_fetch['ot_delivery_charge']),
                );
                $arr['orderer'] = array(
                    'ot_name' => $list_fetch['ot_name'],
                    'ot_hp' => $list_fetch['ot_hp'],
                    'ot_zip' => $list_fetch['ot_zip'],
                    'ot_add1' => $list_fetch['ot_add1'],
                    'ot_add2' => $list_fetch['ot_add2'],
                );
                $arr['recipient'] = array(
                    'ot_b_name' => $list_fetch['ot_b_name'],
                    'ot_b_hp' => $list_fetch['ot_b_hp'],
                    'ot_b_zip' => $list_fetch['ot_b_zip'],
                    'ot_b_addr1' => $list_fetch['ot_b_addr1'],
                    'ot_b_addr2' => $list_fetch['ot_b_addr2'],
                    'ot_requests' => $list_fetch['ot_requests'],
                );

                $ot_deposit_date = date("Y-m-d H:i:s",strtotime("+1 week", strtotime($list_fetch['ot_wdate'])));
                if($list_fetch['ot_pay_type'] == 1) {
                    $query = "select * from policy_t where idx = 1";
                    $policy = $DB->fetch_assoc($query);
                    $arr['deposit'] = array(
                        "pt_account_name" => $policy['pt_account_name'],
                        "pt_account_bank" => $policy['pt_account_bank'],
                        "pt_account_number" => $policy['pt_account_number'],
                        "ot_deposit_date" => $ot_deposit_date,
                    );
                }
                $arr['items'] = $items;
            }
        }

        $payload['data'] = $arr;
        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 비회원 0원 결제 완료되었습니다.', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '비회원 0원 결제 완료되었습니다.', $jwt);
        }
    } else {
        echo result_data('false', $decoded_array['ot_code'].' | '.$decoded_array['ot_price'].' 존재하지 않는 주문정보입니다.', $arr);
    }
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>