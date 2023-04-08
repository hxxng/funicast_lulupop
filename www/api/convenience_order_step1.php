<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/lib/http.class.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}

function get_openssl_encrypt2($data) {
    $pass = DECODEKEY;
    $iv = IV;

    $endata = openssl_encrypt($data , "aes-256-cbc", $pass, true, $iv);
    $endata = base64_encode($endata);

    return $endata;
}

function get_openssl_decrypt2($endata) {
    $pass = DECODEKEY;
    $iv = IV;

    $data = base64_decode($endata);
    $dedata = openssl_decrypt($data , "aes-256-cbc", $pass, true, $iv);

    return $dedata;
}

$arr = array();

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (3,5) and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_query($query);

$mt_idx = $row['mt_idx'];
$ot_code = get_ot_code();

$http = new http;

if($decoded_array['ct_idx']) {
    if($row['mt_level'] == 3) {
        $query = "select * from cart_t where idx in(".$decoded_array['ct_idx'].") and mt_idx = ".$mt_idx;
        $list = $DB->select_query($query);
    } else {
        $query = "select * from cart_t where idx in(".$decoded_array['ct_idx'].") and nmt_id = '".$decoded_array['mt_id']."'";
        $list = $DB->select_query($query);
    }
    $i=0;
    if($list) {
        foreach($list as $row_c) {
            $product = $DB->fetch_query("select *, idx as pt_idx from product_t where idx=".$row_c['pt_idx']." and pt_show = 'Y' and pt_sale_now = 'Y'");

            if($product['pt_stock_chk'] == "Y") {
                if ($row_c['ct_opt_qty'] > $product['pt_stock']) {  //재고부족
                    echo result_data('false', "재고가 부족합니다. 현재 재고수량 : " . (int)$product['pt_stock'], "");
                    exit;
                }
            }

            $sum_ct_price += $row_c['ct_price'];
            $sum_ct_qty += $row_c['ct_opt_qty'];
            $pt_title = $row_c['pt_title'];
            $i++;
            $DB->update_query("cart_t", array('ct_select' => '1', "ct_status" => 1, 'ct_select_wdate' => date('Y-m-d H:i:s'), "ot_code"=>$ot_code), "idx = '" . $row_c['idx'] . "'");
        }
    } else {
        echo result_data('false', "주문할 장바구니가 없습니다.", "");
        exit;
    }
    if($i > 1) {
        $ot_pt_name = $pt_title." 외 ".($i-1)."개";
    } else {
        $ot_pt_name = $pt_title;
    }
} else {
    $order_act = 'direct';
    $pt_idx = $decoded_array['pt_idx'];
    $product = $DB->fetch_query("select * from product_t where idx = ".$pt_idx." and pt_show = 'Y' and pt_sale_now = 'Y'");
    if($product['pt_stock_chk'] == "Y") {
        if ($decoded_array['ct_opt_qty'] > $product['pt_stock']) {  //재고부족
            echo result_data('false', '재고 부족', $arr);
            exit;
        }
    }
    $sum_ct_price = $product['pt_price']*$decoded_array['ct_opt_qty'];
    $sum_ct_qty = $decoded_array['ct_opt_qty'];
    $ot_pt_name = $product['pt_title'];
}

//배송비
$policy = $DB->fetch_query("select * from policy_t where idx='1'");
$pt_delivery_price = $policy['pt_delivery_price'];
if($policy['pt_free_delivery_chk'] == "Y") {
    if($sum_ct_price >= $policy['pt_free_delivery_price']) {
        $pt_delivery_price = 0;
    }
}

//추가배송비
//$row_d = $DB->fetch_query("select * from policy_delivery_t where pdt_zip='".$decoded_array['odt_rzip']."' AND pdt_addr1 = '".$decoded_array['odt_radd1']."'");
//if($row_d['idx'] > 0){
//    $add_shipping_cost_1 = $policy['pt_plus_delivery_price'];
//}
//$row_d = $DB->fetch_query("select * from policy_delivery_t where pdt_szip <=".$decoded_array['odt_rzip']." AND pdt_ezip >= ".$decoded_array['odt_rzip']);
//if($row_d['idx'] > 0){
//    $add_shipping_cost_2 = $row_d['pdt_delivery_price'];
//}
//$calculation = ($add_shipping_cost_1 > $add_shipping_cost_2) ? $add_shipping_cost_1 : $add_shipping_cost_2;
//$add_shipping_cost = ($calculation > 0) ? $calculation : 0;

//$pt_plus_delivery_price = $add_shipping_cost;

//바로구매 일때
if($order_act=='direct'){
    $DB->del_query('cart_t', "ot_code='".$ot_code."' and ct_direct=1");

    $field_arr = array();
    $field_arr['ot_code'] = $ot_code;
    $field_arr['ot_pcode'] = get_ot_pcode();
    if($mt_idx > 0) {
        $field_arr['mt_idx'] = $mt_idx;
    } else {
        $field_arr['nmt_id'] = $decoded_array['mt_id'];
    }
    $field_arr['pt_idx'] = $decoded_array['pt_idx'];
    $field_arr['pt_code'] = $product['pt_code'];
    $field_arr['pt_title'] = $product['pt_title'];
    $field_arr['pt_price'] = $product['pt_price'];	//상품가
    for($j=1; $j<=3; $j++) {
        if($product['pt_option_name'.$j] != "") {
            $ct_opt_name .= $product['pt_option_name'.$j]."/";
        }
    }
    $ct_opt_name = substr($ct_opt_name, 0, -1);
    if($ct_opt_name) $field_arr['ct_opt_name'] = $ct_opt_name;	//선택 옵션명
    if($decoded_array['ct_opt_value']) $field_arr['ct_opt_value'] = $decoded_array['ct_opt_value'];	//선택 옵션값
    if($decoded_array['ct_opt_qty']) $field_arr['ct_opt_qty'] = $decoded_array['ct_opt_qty'];	//선택 수량
    $field_arr['ct_opt_price'] = $product['pt_price'];	//선택 옵션가
    $field_arr['ct_price'] = $product['pt_price']*$decoded_array['ct_opt_qty'];		//총 금액 = 상품가 * 수량
    $field_arr['ct_delivery_default_price'] = $pt_delivery_price;	//기본배송비
    $field_arr['ct_wdate'] = date('Y-m-d H:i:s');	//등록일시
    if($order_act=='direct'){    //바로구매
        $field_arr['ct_direct'] = '1';  //바로구매
        $field_arr['ct_select'] = '2';  //주문
        $field_arr['ct_status'] = '1';  //arr_ct_status 참조
        $field_arr['ct_select_wdate'] = date('Y-m-d H:i:s');
    }
    $DB->insert_query("cart_t", $field_arr);
}else{
    if(!$ot_code){
        echo result_data('false', '주문번호가 없습니다.', $arr);
        exit;
    }
}

if($mt_idx > 0) {
    if ($decoded_array['ot_use_coupon']) {
        $query = "select * from coupon_t where ct_code = '" . $decoded_array['ot_use_coupon'] . "'";
        $coupon = $DB->fetch_assoc($query);
        if ($coupon['idx'] > 0) {
            if ($coupon['ct_use_person'] - $coupon['ct_used_person'] > 0) {
                if ($coupon['ct_min_prcie'] > $sum_ct_price) {
                    echo result_data("false", "쿠폰을 사용하기 위한 최소결제금액을 충족시켜주세요. 최소 결제 금액 : " . (int)$coupon['ct_min_prcie'], "");
                    exit;
                }
                $coupon_price = $coupon['ct_sale_price'];
                $DB->update_query("coupon_t", array("ct_used_person" => $coupon['ct_used_person'] + 1), "idx = " . $coupon['idx']);

                $query = "select * from member_coupon_t where mt_idx = " . $mt_idx . " and mct_ct_code = '" . $decoded_array['ot_use_coupon'] . "'";
                $count = $DB->count_query($query);
                if ($count > 0) {
                    $DB->update_query("member_coupon_t", array("mct_status" => "Y"), " mt_idx = " . $mt_idx . " and mct_ct_code = '" . $decoded_array['ot_use_coupon'] . "'");
                } else {
                    echo result_data("false", "등록한 쿠폰이 아닙니다.", "");
                    exit;
                }
            } else {
                echo result_data("false", "쿠폰이 사용가능 인원을 초과하였습니다.", "");
                exit;
            }
        } else {
            echo result_data("false", "해당되는 쿠폰이 존재하지 않습니다.", "");
            exit;
        }
    }
}

//총 결제 금액 = 전체 상품 가격 + 배송비(무료배송도 가능) - 사용포인트 - 사용쿠폰금액
$total_amount = (int)$sum_ct_price + (int)$pt_delivery_price - (int)$decoded_array['ot_use_point'] - (int)$coupon_price;

$order = $DB->fetch_query("select * from order_t where ot_code='".$ot_code."'");

$ot_price_half = round(($sum_ct_price + $pt_delivery_price) * 0.3);
if($ot_price_half < $decoded_array['ot_use_point']){
    echo result_data('false', '결제 금액의 30%보다 많은 포인트 사용', array('ot_price_half'=>$ot_price_half, 'ot_use_point'=>$decoded_array['ot_use_point']));
    exit;
}
if($row['mt_point'] < $decoded_array['ot_use_point']){
    echo result_data('false', '보유 포인트보다 많은 포인트 사용',  array('mt_point'=>$row['mt_point'], 'ot_use_point'=>$decoded_array['ot_use_point']));
    exit;
}

unset($arr_query);

if($ot_code) $arr_query['ot_code'] = $ot_code;
if($row['mt_level'] == 3) {
    $arr_query['mt_idx'] = $mt_idx;
} else {
    $arr_query['nmt_id'] = $decoded_array['mt_id'];
}
$arr_query['ot_status'] = 1;
if($decoded_array['ot_use_point']) $arr_query['ot_use_point'] = $decoded_array['ot_use_point'];
if($decoded_array['ot_use_coupon']) $arr_query['ot_use_coupon'] = $decoded_array['ot_use_coupon'];
if($decoded_array['ot_use_coupon']) $arr_query['ot_use_coupon_price'] = $coupon_price;
if($pt_delivery_price) $arr_query['ot_delivery_charge'] = $pt_delivery_price;
if($pt_delivery_price > 0) $arr_query['ot_delivery_charge1'] = $pt_delivery_price;
//if($pt_plus_delivery_price) $arr_query['ot_delivery_charge2'] = $pt_plus_delivery_price;
$arr_query['ot_price'] = $total_amount;
$arr_query['ot_point'] = round(((int)$sum_ct_price - (int)$decoded_array['ot_use_point'] - (int)$coupon_price) * 0.03);       //결제 금액의 3% 적립
$arr_query['ot_pay_type'] = 4;
if($decoded_array['ot_name']) $arr_query['ot_name'] = $decoded_array['ot_name'];
if($decoded_array['ot_hp']) $arr_query['ot_hp'] = $decoded_array['ot_hp'];
if($decoded_array['ot_zip']) $arr_query['ot_zip'] = $decoded_array['ot_zip'];
if($decoded_array['ot_add1']) $arr_query['ot_add1'] = $decoded_array['ot_add1'];
if($decoded_array['ot_add2']) $arr_query['ot_add2'] = $decoded_array['ot_add2'];
if($decoded_array['ot_b_name']) $arr_query['ot_b_name'] = $decoded_array['ot_b_name'];
if($decoded_array['ot_b_hp']) $arr_query['ot_b_hp'] = $decoded_array['ot_b_hp'];
if($decoded_array['ot_b_zip']) $arr_query['ot_b_zip'] = $decoded_array['ot_b_zip'];
if($decoded_array['ot_b_addr1']) $arr_query['ot_b_addr1'] = $decoded_array['ot_b_addr1'];
if($decoded_array['ot_b_addr2']) $arr_query['ot_b_addr2'] = $decoded_array['ot_b_addr2'];
if($decoded_array['ot_requests']) $arr_query['ot_requests'] = $decoded_array['ot_requests'];
$arr_query['ot_pt_name'] = $ot_pt_name;
$arr_query['ot_qty'] = $sum_ct_qty;
$arr_query['ot_wdate'] = date('Y-m-d H:i:s');

$en_ot_code = get_openssl_encrypt2($ot_code);
$en_amt = get_openssl_encrypt2($total_amount);

$query = [];
$query['msgId'] = "msgId";
$query['msgVer'] = "msgVer";
$query['transactionNo'] = $en_ot_code;
$query['custCode'] = "custCode";
$query['itemCode'] = "itemCode";
$query['dealDate'] = date("Ymd");
$query['dealTime'] = date("His");
$query['itemInfo'] = $ot_pt_name;
$query['amt'] = $en_amt;
$query['smsFlag'] = "";
$query['expirationDate'] = "";

$getToken = $http->PostMethodData('https://tapi.cashgate.co.kr/numIssue', $query, $mReferer, '', $mCookie, true);

$result = json_decode($getToken, true);

if($result['resCode'] == 0000) {
    $approvalNo = get_openssl_decrypt2($result['approvalNo']);
    $barcode = get_openssl_decrypt2($result['barcode']);

    $arr_query['ot_approvalNo'] = $approvalNo;
    $arr_query['ot_barcode'] = $barcode;
    if($order['mt_idx'] > 0){
        $DB->update_query('order_t', $arr_query, "ot_code='".$ot_code."'");
    }else{
        $DB->insert_query('order_t', $arr_query);
    }

    unset($arr_query);
    if($ot_code) $arr_query['ot_code'] = $ot_code;
    if($row['mt_idx']) $arr_query['mt_idx'] = $row['mt_idx'];
    if($decoded_array['ot_name']) $arr_query['odt_name'] = $decoded_array['ot_name'];
    if($decoded_array['ot_hp']) $arr_query['odt_hp'] = $decoded_array['ot_hp'];
    if($decoded_array['ot_zip']) $arr_query['odt_zip'] = $decoded_array['ot_zip'];
    if($decoded_array['ot_add1']) $arr_query['odt_add1'] = $decoded_array['ot_add1'];
    if($decoded_array['ot_add2']) $arr_query['odt_add2'] = $decoded_array['ot_add2'];
    if($decoded_array['ot_b_name']) $arr_query['odt_rname'] = $decoded_array['ot_b_name'];
    if($decoded_array['ot_b_hp']) $arr_query['odt_rhp'] = $decoded_array['ot_b_hp'];
    if($decoded_array['ot_b_zip']) $arr_query['odt_rzip'] = $decoded_array['ot_b_zip'];
    if($decoded_array['ot_b_addr1']) $arr_query['odt_radd1'] = $decoded_array['ot_b_addr1'];
    if($decoded_array['ot_b_addr2']) $arr_query['odt_radd2'] = $decoded_array['ot_b_addr2'];
    if($decoded_array['ot_requests']) $arr_query['odt_rmemo'] = $decoded_array['ot_requests'];
    if($pt_delivery_price) $arr_query['odt_delivery_default_price'] = $pt_delivery_price;
    if($pt_delivery_price > 0) $arr_query['odt_delivery_charge'] = $pt_delivery_price;

    if($order['mt_idx'] > 0){
        $DB->update_query('order_delivery_t', $arr_query, "ot_code='".$ot_code."'");
    }else{
        $DB->insert_query('order_delivery_t',$arr_query);
    }

    $payload['data']['ot_code'] = $ot_code;
    $payload['data']['ot_price'] = (int)$total_amount;
    $payload['data']['barcode'] = $barcode;
    if($decoded_array['ot_pay_type'] == 1) {
        $payload['data']['deposit_date'] = date("Y-m-d H:i:s",strtotime("+1 week", time()));
    }
    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 주문하기가 완료되었습니다.', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '주문하기가 완료되었습니다.', $jwt);
    }
} else {
    echo result_data("false", "errorCode : ".$result['resCode'],"");
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>