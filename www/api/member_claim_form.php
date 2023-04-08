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
if($decoded_array['ot_code']=="") {
    echo result_data('false', '잘못된 접근입니다. ot_code', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level in (3,5)
";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    if($row['mt_level'] == 3) {
        $query_ot = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.mt_idx = ".$row['mt_idx']." and cart_t.ot_code = '".$decoded_array['ot_code']."'";
        $row_ot = $DB->fetch_query($query_ot);
        if($row_ot) {
            unset($arr);
            $arr['form'] = array(
                'ot_code' => $row_ot['ot_code'],
            );

            $query_ct = "select *, (select pt_image1 from product_t where product_t.idx = pt_idx) as pt_image from cart_t where ct_status in (4,5) and ot_code = '" . $row_ot['ot_code'] . "'";
            $list_ct = $DB->select_query($query_ct);
            if ($list_ct) {
                foreach ($list_ct as $row_ct) {
                    $items[] = array(
                        'ot_status' => $row_ct['ct_status'],
                        'ot_status_txt' => $arr_ct_status[$row_ct['ct_status']],
                        'ot_pcode' => $row_ct['ot_pcode'],
                        'pt_idx' => $row_ct['pt_idx'],
                        'pt_title' => $row_ct['pt_title'],
                        'pt_price' => (int)$row_ct['pt_price'],
                        'pt_image1' => ($row_ct['pt_image'] ? $ct_img_url . '/' . $row_ct['pt_image']."?cache=".strtotime($row_ct['pt_udate']) : $ct_no_img_url),
                        'ct_opt_name' => $row_ct['ct_opt_name'],
                        'ct_opt_value' => $row_ct['ct_opt_value'],
                        'ct_opt_qty' => (int)$row_ct['ct_opt_qty'],
                        'ct_price' => (int)$row_ct['ct_price'],
                    );
                }
            }
            $arr['form']['delivery'] = array(
                "ot_b_name" => $row_ot['ot_b_name'],
                "ot_b_hp" => $row_ot['ot_b_hp'],
                "ot_b_zip" => $row_ot['ot_b_zip'],
                "ot_b_addr1" => $row_ot['ot_b_addr1'],
                "ot_b_addr2" => $row_ot['ot_b_addr2'],
            );

            for($i=1; $i<=3; $i++) {
                if($i == 1) {
                    $title = "상품 오배송/불량";
                } else if($i == 2) {
                    $title = "물건 퀄리티문제";
                } else {
                    $title = "기타";
                }
                $type[] = array(
                    "idx" => $i,
                    "title" => $title
                );
            }
            $arr['form']['type'] = $type;
            $arr['form']['items'] = $items;

            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 취소/교환/반품신청폼', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '취소/교환/반품신청폼', $jwt);
            }
        }
    } else {
        $query_ot = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.nmt_id = '".$decoded_array['mt_id']."' and cart_t.ot_code = '".$decoded_array['ot_code']."'";
        $row_ot = $DB->fetch_query($query_ot);
        if($row_ot) {
            unset($arr);
            $arr['form'] = array(
                'ot_code' => $row_ot['ot_code'],
            );

            $query_ct = "select *, (select pt_image1 from product_t where product_t.idx = pt_idx) as pt_image from cart_t where ct_status in (4,5) and ot_code = '" . $row_ot['ot_code'] . "'";
            $list_ct = $DB->select_query($query_ct);
            if ($list_ct) {
                foreach ($list_ct as $row_ct) {
                    $items[] = array(
                        'ot_status' => $row_ct['ct_status'],
                        'ot_status_txt' => $arr_ct_status[$row_ct['ct_status']],
                        'ot_pcode' => $row_ct['ot_pcode'],
                        'pt_idx' => $row_ct['pt_idx'],
                        'pt_title' => $row_ct['pt_title'],
                        'pt_price' => (int)$row_ct['pt_price'],
                        'pt_image1' => ($row_ct['pt_image'] ? $ct_img_url . '/' . $row_ct['pt_image']."?cache=".time() : $ct_no_img_url),
                        'ct_opt_name' => $row_ct['ct_opt_name'],
                        'ct_opt_value' => $row_ct['ct_opt_value'],
                        'ct_opt_qty' => (int)$row_ct['ct_opt_qty'],
                        'ct_price' => (int)$row_ct['ct_price'],
                    );
                }
            }
            $arr['form']['delivery'] = array(
                "ot_b_name" => $row_ot['ot_b_name'],
                "ot_b_hp" => $row_ot['ot_b_hp'],
                "ot_b_zip" => $row_ot['ot_b_zip'],
                "ot_b_addr1" => $row_ot['ot_b_addr1'],
                "ot_b_addr2" => $row_ot['ot_b_addr2'],
            );
            for($i=1; $i<=3; $i++) {
                if($i == 1) {
                    $title = "상품 오배송/불량";
                } else if($i == 2) {
                    $title = "물건 퀄리티문제";
                } else {
                    $title = "기타";
                }
                $type[] = array(
                    "idx" => $i,
                    "title" => $title
                );
            }
            $arr['form']['type'] = $type;
            $arr['form']['items'] = $items;

            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 비회원 취소/교환/반품신청폼', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '비회원 취소/교환/반품신청폼', $jwt);
            }
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>