<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

    if($_POST['act'] == "input" || $_POST['act'] == "update") {
        unset($arr_query);
        $arr_query = array(
            "ct_name" => $_POST['ct_name'],
            "ct_sdate" => $_POST['ct_sdate'],
            "ct_edate" => $_POST['ct_edate'],
            "ct_sale_price" => $_POST['ct_sale_price'],
            "ct_min_price" => $_POST['ct_min_price'],
            "ct_use_person" => $_POST['ct_use_person'],
            "ct_wdate" => "now()",
        );

        if($_POST['ct_idx']) {
            $query_ptc = "select * from coupon_t where idx = '".$_POST['ct_idx']."'";
            $row_etc = $DB->fetch_query($query_ptc);
        }

        if($_POST['ct_idx']=='') {
            $ct_code = get_ct_code();
            $arr_query['ct_code'] = $ct_code;
            $DB->insert_query('coupon_t', $arr_query);
        } else {
            $where_query = "idx = '".$row_etc['idx']."'";
            unset($arr_query['ct_wdate']);
            $arr_query['ct_udate'] = date('Y-m-d H:i:s');
            $DB->update_query('coupon_t', $arr_query, $where_query);
        }

        p_alert('처리되었습니다.');
    }

	include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>