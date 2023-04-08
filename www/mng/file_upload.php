<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	$img = $_FILES['upload']['tmp_name'];
	$img_name = $_FILES['upload']['name'];
	$img_size = $_FILES['upload']['size'];
	$img_type = $_FILES['upload']['type'];

	if(check_file_ext($img_name, $ct_image_ext) == false) {
		$str = explode(";", $ct_image_ext);
		$msg = "";
		for ($i=0; $i<count($str); $i++) {
			$msg .= $str[$i]." ";
		}
		alert("첨부파일 확장자는 ".$msg." 만 가능합니다.");
	}

	if(check_file_ext($img_name, $ct_image_ext) == false) {
		$msg = str_replace(";", " ", $ct_image_ext);
		alert("이미지 확장자는 ".$msg." 만 가능합니다.");
	}

	$img_on = $_GET['upload_name']."_ediotr_".date("YmdHis").".".get_file_ext($img_name);
	upload_file($img, $img_on, $ct_img_dir_a."/");

	if($_GET['CKEditorFuncNum']=='') {
		$_GET['CKEditorFuncNum'] = '1';
	}

	$funcNum = $_GET['CKEditorFuncNum'];
	$CKEditor = $_GET['upload_name'];
	$langCode = "ko";
	$url = $ct_img_url."/".$img_on;
	$message = "";

	echo '{"filename" : "'.$img_on.'", "uploaded" : 1, "url":"'.$url.'"}';

    include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>