<?php
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
$date = $_POST['date'];

if($_POST['type']=='search_cnt'){
    $query = $DB->select_query("SELECT count(slt_txt) as cnt, slt_txt FROM search_log_t group by slt_txt order by count(slt_txt) desc limit 10");
	if($query) {
		foreach ($query as $row) {
			$arr[] = $row;
		}
		echo json_encode(array('result' => '_ok', 'data' => $arr));
	} else {
		echo json_encode(array('result' => 'false', 'data' => ""));
	}
} else if($_POST['type']=='get_attention'){
	$query = "select count(pt_idx) as cnt, 
			(select count(*) as cnt from wish_product_t where  wpt_status = 'Y' and pt_idx = a1.idx group by pt_idx order by count(*) desc) as like_cnt,
			(select pt_view from product_t where  pt_show = 'Y' and pt_sale_now = 'Y' and idx = a1.idx order by pt_view desc) as view_cnt,
			a1.pt_title
			from product_t a1 left join cart_t on a1.idx = pt_idx and ct_select = 2 
			where pt_show = 'Y' and pt_sale_now = 'Y' group by a1.idx order by pt_title desc";
	$list = $DB->select_query($query);
	if($list) {
		$arr = array();
		foreach ($list as $row) {
			$sum = 0;
			$sum = $row['cnt'] + $row['like_cnt'] + $row['view_cnt'];
			$arr[$row['pt_title']] = $sum;
		}
		arsort($arr);

		echo json_encode(array('result' => '_ok', 'data' => $arr));
	} else {
		echo json_encode(array('result' => 'false', 'data' => ""));
	}
} else if($_POST['type']=='get_ot_price'){
	$query = "select ifnull(sum(ot_price),0) as ot_price, date_format(ot_pdate, '%Y-%m-%d') as ot_pdate from order_t where (ot_pdate BETWEEN DATE_ADD(NOW(),INTERVAL -1 WEEK ) AND NOW()) and ot_status > 1 group by date_format(ot_pdate, '%Y-%m-%d')";
	$list = $DB->select_query($query);
	if($list) {
		foreach ($list as $row) {
			$arr[] = array("label" => $row['ot_pdate'], "y" => (int)$row['ot_price']);
		}
	}
	echo json_encode(array('result' => '_ok', 'data' => $arr));
} else if($_POST['type']=='get_buy_cnt') {
	$query = "select count(*) as cnt, date_format(pclt_wdate, '%Y-%m-%d') as pclt_wdate from product_click_log_t where (pclt_wdate BETWEEN DATE_ADD(NOW(),INTERVAL -1 WEEK ) AND NOW()) group by date_format(pclt_wdate, '%Y-%m-%d')";
	$click = $DB->select_query($query);

	$query = "select count(*) as cnt, date_format(ct_pdate, '%Y-%m-%d') as ct_pdate from cart_t 
			where (ct_pdate BETWEEN DATE_ADD(NOW(),INTERVAL -1 WEEK ) AND NOW()) and ct_select = 2 and ct_status > 1 group by date_format(ct_pdate, '%Y-%m-%d')";
	$order = $DB->select_query($query);

	if($click || $order) {
		foreach ($order as $o_row) {
			foreach ($click as $c_row) {
				if($o_row['ct_pdate'] == $c_row['pclt_wdate']) {
					//구매전환율 = (주문수 / 클릭수) * 100
					$rate = ($o_row['cnt'] / $c_row['cnt']) * 100;
					$arr[] = array("label" => $o_row['ct_pdate'], "y" => (int)$rate);
				}
			}
		}
	}
	echo json_encode(array('result' => '_ok', 'data' => $arr));
}
?>
