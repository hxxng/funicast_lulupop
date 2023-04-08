<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

    if($_POST['pt_free_delivery_chk'] == "Y") {
        $pt_delivery_chk = "Y";
    } else {
        $pt_delivery_chk = "N";
    }
    $arr_query = array(
        "pt_delivery_price" => $_POST['pt_delivery_price'],
        "pt_refund_price" => $_POST['pt_refund_price'],
        "pt_exchange_price" => $_POST['pt_exchange_price'],
        "pt_free_delivery_price" => $_POST['pt_free_delivery_price'],
        "pt_delivery_info" => $_POST['pt_delivery_info'],
        "pt_return_info" => $_POST['pt_return_info'],
        "pt_free_delivery_chk" => $pt_delivery_chk,
        "pt_udate" => "now()",
    );

    $DB->update_query('policy_t',$arr_query, "idx = '1'");

    p_alert('처리되었습니다.');
	include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>