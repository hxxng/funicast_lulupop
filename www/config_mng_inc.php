<?
    error_reporting(E_ERROR);
    ini_set('display_errors', '1');

	$arr_rt_status = array(
		'1' => '등록',
		'2' => '환불완료',
		'3' => '보류',
		'4' => '환불철회',
	);

	foreach($arr_rt_status as $key => $val) {
		if($val) {
			$arr_rt_status_option .= "<option value='".$key."' >".$val."</option>";
		}
	}
?>