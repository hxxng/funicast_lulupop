<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	set_time_limit(0);
	ini_set("memory_limit", -1);

	$arr_cell = array(
		'상품주문번호', '주문번호', '배송방법', '택배사', '송장번호', '주문상태', '결제일시', '발주일시', '상품명', '선택옵션명', '선택옵션', '수량', '직접입력옵션값', '결제금액', '구매자ID', '구매자명', '구매자명 연락처1', '구매자명 연락처2', '구매자 우편번호', '구매자 주소', '구매자 상세주소', '수령자', '수령자 연락처1', '수령자 연락처2', '수령자 우편번호', '수령자 주소', '수령자 상세주소', '배송메모'
	);

	require_once $_SERVER['DOCUMENT_ROOT'].'/lib/PHPExcel-1.8/Classes/PHPExcel.php';

	$objPHPExcel = new PHPExcel();

	$sheetIndex = $objPHPExcel->createSheet(0);
	$sheetIndex->setTitle('주문엑셀_'.$_GET['sdate'].'_'.$_GET['edate']);

	$a = 'A';
	foreach($arr_cell as $key => $val) {
		$sheetIndex->setCellValue($a.'1', $val);
		$a++;
	}

	$q = 2;
	unset($list);
	$query = "
		select * from order_t a1
		left outer join cart_t a2 on a1.ot_code = a2.ot_code
		where ".$_GET['search_date']." between '".$_GET['sdate']." 00:00:00' and '".$_GET['edate']." 23:59:59'
		and a2.ct_status in (1,2,3,4)
	";
	$list = $DB->select_query($query);

	if($list) {
		foreach($list as $row) {
			$sheetIndex->setCellValue('A'.$q, $row['ot_pcode']);
			$sheetIndex->setCellValue('B'.$q, $row['ot_code']);
			$sheetIndex->setCellValue('C'.$q, $arr_pdt_type[$row['ct_delivery_type']]);
			$sheetIndex->setCellValue('D'.$q, $row['ct_delivery_com']);
			$sheetIndex->setCellValue('E'.$q, $row['ct_delivery_number']);
			$sheetIndex->setCellValue('F'.$q, $arr_ct_status[$row['ct_status']]);
			$sheetIndex->setCellValue('G'.$q, $row['ct_pdate']);
			$sheetIndex->setCellValue('H'.$q, $row['ct_ldate']);
			$sheetIndex->setCellValue('I'.$q, $row['pt_title']);
			$sheetIndex->setCellValue('J'.$q, $row['ct_opt_name']);
			$sheetIndex->setCellValue('K'.$q, $row['ct_opt_value']);
			$sheetIndex->setCellValue('L'.$q, $row['ct_opt_qty']);
			$sheetIndex->setCellValue('M'.$q, $row['ct_opt_direct']);
			$sheetIndex->setCellValue('N'.$q, $row['ct_price']);
			$sheetIndex->setCellValue('O'.$q, $row['mt_id']);
			$sheetIndex->setCellValue('P'.$q, $row['ot_name']);
			$sheetIndex->setCellValue('Q'.$q, $row['ot_tel']);
			$sheetIndex->setCellValue('R'.$q, $row['ot_hp']);
			$sheetIndex->setCellValue('S'.$q, $row['ot_zip']);
			$sheetIndex->setCellValue('T'.$q, $row['ot_add1']);
			$sheetIndex->setCellValue('U'.$q, $row['ot_add2']);
			$sheetIndex->setCellValue('V'.$q, $row['ot_rname']);
			$sheetIndex->setCellValue('W'.$q, $row['ot_rtel']);
			$sheetIndex->setCellValue('X'.$q, $row['ot_rhp']);
			$sheetIndex->setCellValue('Y'.$q, $row['ot_rzip']);
			$sheetIndex->setCellValue('Z'.$q, $row['ot_radd1']);
			$sheetIndex->setCellValue('AA'.$q, $row['ot_radd2']);
			$sheetIndex->setCellValue('AB'.$q, $row['ot_rmemo']);

			$q++;
		}
	}

	$objPHPExcel->removeSheetByIndex('1');
	$objPHPExcel->setActiveSheetIndex(0);

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="order_excel_'.date("YmdHis").'.xls"');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Set-Cookie: fileDownload=true; path=/');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');

	include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>