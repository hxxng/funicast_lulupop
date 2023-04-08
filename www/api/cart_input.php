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
if($decoded_array['pt_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. pt_idx', '');
    exit;
}
if($decoded_array['ct_opt_qty']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_opt_qty', '');
    exit;
}
$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (3,5) and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    if($row['mt_level'] == 3) {
        $chk_update = "N";
        $arr = array();

        $query = "select * from cart_t where ct_select = 0 and pt_idx = " . $decoded_array['pt_idx'] . " and mt_idx = " . $row['mt_idx'] . " and ot_code is null";
        $list_c = $DB->select_query($query);
        //같은 옵션일 때 수량 증가
        if ($list_c) {
            foreach ($list_c as $row_c) {
                if ($row_c['ct_opt_name'] == $decoded_array['ct_opt_name'] && $row_c['ct_opt_value'] == $decoded_array['ct_opt_value']) {
                    $ct_opt_qty = $row_c['ct_opt_qty'] + $decoded_array['ct_opt_qty'];
                    $ot_pcode = $row_c['ot_pcode'];
                    $idx = $row_c['idx'];

                    $DB->update_query("cart_t", array("ct_opt_qty" => $ct_opt_qty, "ct_price" => $row_c['ct_price'] * $ct_opt_qty), " ot_pcode = '" . $ot_pcode . "'");
                    $chk_update = "Y";
                    $arr['ot_pcode'][] = $ot_pcode;
                    $arr['ct_idx'][] = $idx;
                }
            }
        }
        if ($chk_update == "N") {
            $query_pt = "select * from product_t where idx = '" . $decoded_array['pt_idx'] . "'";
            $row_pt = $DB->fetch_query($query_pt);

            $query = "select * from product_option_t where pt_idx = " . $decoded_array['pt_idx'];
            $count_option = $DB->count_query($query);
            if ($count_option > 0) {
                if ($decoded_array['ct_opt_value'] == "") {
                    echo result_data('false', '필수 입력값입니다. ct_opt_value', '');
                    exit;
                }
            }
            $ct_price = $row_pt['pt_price'] * $decoded_array['ct_opt_qty'];

            for ($i = 1; $i <= 3; $i++) {
                if ($row_pt['pt_option_name' . $i] != "") {
                    $ct_opt_name .= $row_pt['pt_option_name' . $i] . ",";
                }
            }
            $ct_opt_name = substr($ct_opt_name, 0, -1);

            $ot_pcode = get_ot_pcode();

            unset($arr_query);
            $arr_query = array(
                "ot_pcode" => $ot_pcode,
                "mt_idx" => $row['mt_idx'],
                "pt_idx" => $decoded_array['pt_idx'],
                "pt_code" => $row_pt['pt_code'],
                "pt_title" => $row_pt['pt_title'],
                "pt_price" => $row_pt['pt_price'],
                "ct_opt_name" => $ct_opt_name,
                "ct_opt_value" => $decoded_array['ct_opt_value'],
                "ct_opt_price" => $row_pt['pt_price'],
                "ct_opt_qty" => $decoded_array['ct_opt_qty'],
                "ct_price" => $ct_price,
                "ct_direct" => 0,
                "ct_select" => 0,
                "ct_wdate" => "now()",
            );

            $DB->insert_query('cart_t', $arr_query);
            $_last_ct_idx = $DB->insert_id();

            $arr['ot_pcode'][] = $ot_pcode;
            $arr['ct_idx'][] = $_last_ct_idx;
        }
        $payload['data'] = $arr;
        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 장바구니 담기', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '장바구니 담기', $jwt);
        }
    } else {
        $query = "
		select *, a1.idx as mt_idx from member_t a1
		where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level = 5
	";
        $row_m = $DB->fetch_query($query);

        if($row_m) {
            //비회원 장바구니
            $chk_update = "N";
            $arr = array();

            $query = "select * from cart_t where ct_select = 0 and pt_idx = " . $decoded_array['pt_idx'] . " and nmt_id = '".$decoded_array['mt_id']."' and ot_code is null";
            $list_c = $DB->select_query($query);
            //같은 옵션일 때 수량 증가
            if($list_c) {
                foreach ($list_c as $row_c) {
                    if($row_c['ct_opt_name'] == $decoded_array['ct_opt_name'] && $row_c['ct_opt_value'] == $decoded_array['ct_opt_value']) {
                        $ct_opt_qty = $row_c['ct_opt_qty'] + $decoded_array['ct_opt_qty'];
                        $ot_pcode = $row_c['ot_pcode'];
                        $idx = $row_c['idx'];

                        $DB->update_query("cart_t", array("ct_opt_qty" => $ct_opt_qty, "ct_price" => $row_c['ct_price'] * $ct_opt_qty), " ot_pcode = '".$ot_pcode."'");
                        $chk_update = "Y";
                        $arr['ot_pcode'][] = $ot_pcode;
                        $arr['ct_idx'][] = $idx;
                    }
                }
            }
            if($chk_update == "N") {
                $query_pt = "select * from product_t where idx = '" . $decoded_array['pt_idx'] . "'";
                $row_pt = $DB->fetch_query($query_pt);

                $query = "select * from product_option_t where pt_idx = ".$decoded_array['pt_idx'];
                $count_option = $DB->count_query($query);
                if($count_option > 0) {
                    if($decoded_array['ct_opt_value']=="") {
                        echo result_data('false', '필수 입력값입니다. ct_opt_value', '');
                        exit;
                    }
                }

                $ct_price = $row_pt['pt_price'] * $decoded_array['ct_opt_qty'];

                for($i=1; $i<=3; $i++) {
                    if($row_pt['pt_option_name'.$i] != "") {
                        $ct_opt_name .= $row_pt['pt_option_name' . $i] . ",";
                    }
                }
                $ct_opt_name = substr($ct_opt_name, 0, -1);

                $ot_pcode = get_ot_pcode();

                unset($arr_query);
                $arr_query = array(
                    "ot_pcode" => $ot_pcode,
                    "nmt_id" => $decoded_array['mt_id'],
                    "pt_idx" => $decoded_array['pt_idx'],
                    "pt_code" => $row_pt['pt_code'],
                    "pt_title" => $row_pt['pt_title'],
                    "pt_price" => $row_pt['pt_price'],
                    "ct_opt_name" => $ct_opt_name,
                    "ct_opt_value" => $decoded_array['ct_opt_value'],
                    "ct_opt_price" => $row_pt['pt_price'],
                    "ct_opt_qty" => $decoded_array['ct_opt_qty'],
                    "ct_price" => $ct_price,
                    "ct_direct" => 0,
                    "ct_select" => 0,
                    "ct_wdate" => "now()",
                );

                $DB->insert_query('cart_t', $arr_query);
                $_last_ct_idx = $DB->insert_id();

                $arr['ot_pcode'][] = $ot_pcode;
                $arr['ct_idx'][] = $_last_ct_idx;
            }
            $payload['data'] = $arr;
            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 비회원 장바구니 담기', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '비회원 장바구니 담기', $jwt);
            }
        } else {
            echo result_data("false", "해당하는 비회원 정보가 없습니다.", null);
            exit;
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>