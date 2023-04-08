<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

$_act = $_GET['act'];
$exchange_confirm = "";

if($_POST['act'] == "update") {
    if($_POST['ct_status'] == 81) {
        $ct_request_ydate = "now()";
        $exchange_confirm = "Y";
    }
    else{
        $ct_request_ydate = "null";
    }

    unset($arr_query);
    $arr_query = array(
        "ct_status" => $_POST['ct_status'],
        "ct_delivery_com" => $_POST['ct_delivery_com'],
        "ct_delivery_number" => $_POST['ct_delivery_number'],
        "ct_request_ydate" => $ct_request_ydate
    );
    $DB->update_query("cart_t", $arr_query, "ot_pcode = '".$_POST['ot_pcode']."'");

    if($exchange_confirm == "Y") {
        unset($arr_query2);
        $arr_query2 = array(
            "ot_status" => 7,
        );
        $query = "select * from cart_t where ot_pcode = '".$_POST['ot_pcode']."'";
        $list = $DB->fetch_assoc($query);
        $DB->update_query("order_t", $arr_query2, "ot_code = '".$list['ot_code']."'");
    }

    p_alert("수정되었습니다.");
} else if($_POST['act'] == "save_delivery") {
    $count = $DB->count_query("select * from cart_t where ot_code = '".$_POST['ot_code']."' and ct_status = 80");
    if($count > 0) {
        $DB->update_query("cart_t", array("ct_delivery_com" => $_POST['ct_delivery_com'], "ct_delivery_number" => $_POST['ct_delivery_number'], "ct_status" => 81), "ot_code = '".$_POST['ot_code']."' and ct_status = 80");
        echo json_encode(array("result" => "_ok", "msg" => "교환이 완료되었습니다."));
    } else {
        echo json_encode(array("result" => "false", "msg" => "해당하는 교환주문이 없습니다."));
    }
} else if($_POST['act'] == "save_collect_delivery") {
    $count = $DB->count_query("select * from cart_t where ot_code = '".$_POST['ot_code']."' and ct_status = 80");
    if($count > 0) {
        $DB->update_query("cart_t", array("ct_collect_com" => $_POST['ct_collect_com'], "ct_collect_number" => $_POST['ct_collect_number'], "ct_request_status" => 2), "ot_code = '".$_POST['ot_code']."'");
        echo json_encode(array("result" => "_ok", "msg" => "회수요청이 완료되었습니다."));
    } else {
        echo json_encode(array("result" => "false", "msg" => "해당하는 교환주문이 없습니다."));
    }
}
?>