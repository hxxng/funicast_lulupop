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
$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_assoc($query);

if($row_m['mt_idx']) {
    $query = "SELECT * FROM product_t where pt_random_chk = 'Y' and pt_show = 'Y' and pt_sale_now = 'Y' ";
    $list = $DB->select_query($query);
    if ($list) {
        $query = "select * from member_draw_t where mt_idx = ".$row_m['mt_idx'];
        $count = $DB->count_query($query);
        if($count > 5) {
            echo result_data("false", "뽑기횟수 최대 6회 모두 사용하였습니다.\n배송 받으실 상품 선택 후 이용 바랍니다.", "");
            exit;
        }
        unset($arr);
        $arr = array();
        foreach ($list as $row) {
            $arr[$row['idx']] = $row['pt_random_percentage'];
        }

        $buff_lotto = array();

        function init_lotto()
        {
            global $buff_lotto;
            global $arr;
            $lotto = $arr;
            foreach ($lotto as $key => $value) {
                $buff_lotto = array_merge($buff_lotto, array_fill(0, $value, $key));
            }
        }

        function get_lotto()
        {
            global $buff_lotto;
            shuffle($buff_lotto);
            return end($buff_lotto);
        }

        $query = "select sum(ct_amount) as sum_ct_amount from coin_t where mt_idx = " . $row_m['mt_idx'] . " and ct_type = 1 and ct_status in (2,3)";
        $a_coin = $DB->fetch_assoc($query);
        $query = "select sum(ct_amount) as sum_ct_amount from coin_t where mt_idx = " . $row_m['mt_idx'] . " and ct_type = 2 and ct_status in (0,3)";
        $b_coin = $DB->fetch_assoc($query);

        if($row_m['mt_first_coin'] == "Y") {
            if($b_coin['sum_ct_amount'] >= 3) {
                $DB->insert_query("coin_t", array("ct_amount" => -3, "ct_type" => 2, "ct_status" => 3, "mt_idx" => $row_m['mt_idx'], "ct_wdate" => "now()"));
            } else {
                if($b_coin['sum_ct_amount'] > 0) {
                    if($a_coin['sum_ct_amount'] >= (3-$b_coin['sum_ct_amount'])) {
                        $DB->insert_query("coin_t", array("ct_amount" => -$b_coin['sum_ct_amount'], "ct_type" => 2, "ct_status" => 3, "mt_idx" => $row_m['mt_idx'], "ct_wdate" => "now()"));
                        $DB->insert_query("coin_t", array("ct_amount" => ($b_coin['sum_ct_amount']-3), "ct_type" => 1, "ct_status" => 3, "mt_idx" => $row_m['mt_idx'], "ct_wdate" => "now()"));
                    } else {
                        echo result_data("false", "보유한 코인이 3코인보다 적습니다.", "");
                        exit;
                    }
                } else {
                    if($a_coin['sum_ct_amount'] >= 3) {
                        $DB->insert_query("coin_t", array("ct_amount" => -3, "ct_type" => 1, "ct_status" => 3, "mt_idx" => $row_m['mt_idx'], "ct_wdate" => "now()"));
                    } else {
                        echo result_data("false", "보유한 코인이 3코인보다 적습니다.", "");
                        exit;
                    }
                }
            }
            $DB->update_query('member_t', array("mt_coin" => $row_m['mt_coin']-3, "mt_first_coin" => "Y"), " idx = ".$row_m['mt_idx']);
        } else {
            if($a_coin['sum_ct_amount'] >= 3) {
                $DB->insert_query("coin_t", array("ct_amount" => -3, "ct_type" => 1, "ct_status" => 3, "mt_idx" => $row_m['mt_idx'], "ct_wdate" => "now()"));
            } else if($a_coin['sum_ct_amount'] < 3 && $b_coin['sum_ct_amount'] > 0) {
                echo result_data("false", "첫번째 뽑기는 A코인으로만 가능합니다.", "");
                exit;
            } else {
                echo result_data("false", "코인이 부족합니다.", "");
                exit;
            }
            $DB->update_query('member_t', array("mt_coin" => $row_m['mt_coin']-3, "mt_first_coin" => "Y"), " idx = ".$row_m['mt_idx']);
        }

        init_lotto();
        $pt_idx = get_lotto();

        $query = "select * from catalog_t where pt_idx = " . $pt_idx;
        $catalog_info = $DB->fetch_assoc($query);
        $ct_arr = explode(",", $row_m['mt_catalog']);
        $j = 0;
        for($i=0; $i<count($ct_arr); $i++) {
            if($ct_arr[$i] == $catalog_info['idx']) {
                $j++;
            }
        }
        if($j == 0) {
            $mt_catalog = $catalog_info['idx'].",";
        }

        $DB->update_query('member_t', array('mt_catalog' => $row_m['mt_catalog'].$mt_catalog), " idx = ".$row_m['mt_idx']);

        $query = "select *, dt_effect from product_t left join draw_t on draw_t.idx = product_t.pt_random_effect where product_t.idx = ".$pt_idx." and dt_effect is not null";
        $product_info = $DB->fetch_assoc($query);

        if($product_info) {
            $catalog = array(
                "pt_idx" => $pt_idx,
                "ct_img1" => $ct_img_url . "/" . $catalog_info['ct_img1']."?cache=".strtotime($catalog_info['ct_udate']),
                "dt_effect" => $ct_img_url . "/" . $product_info['dt_effect']."?cache=".strtotime($product_info['pt_udate']),
            );
        }

        $query = "select * from member_draw_t where mt_idx = " . $row_m['mt_idx']." order by mdt_wdate desc";
        $draw = $DB->fetch_assoc($query);
        $count = $DB->count_query($query);
        if ($count > 0) {
            $DB->insert_query("member_draw_t", array("pt_idx" => $pt_idx, "mdt_cnt" => $draw['mdt_cnt'] + 1, "mt_idx" => $row_m['mt_idx'], "mdt_wdate" => "now()"));
        } else {
            $DB->insert_query("member_draw_t", array("pt_idx" => $pt_idx, "mdt_cnt" => 1, "mt_idx" => $row_m['mt_idx'], "mdt_wdate" => "now()"));
        }

        $payload['data'] = $catalog;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 랜덤 뽑기', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '랜덤 뽑기', $jwt);
        }
    } else {
        echo result_data('false', '랜덤 뽑기할 상품이 없습니다.', $arr);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>