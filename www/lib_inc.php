<?
	ob_start('ob_gzhandler');
	header("Content-Type: text/html; charset=utf-8");
	header("Access-Control-Allow-Origin: *");

	ini_set('session.cache_expire',86400);
	ini_set('session.gc_maxlifetime',86400);
	ini_set('session.use_trans_sid', 0);
	ini_set('url_rewriter.tags','');
	ini_set("session.gc_probability", 1);
	ini_set("session.gc_divisor", 100);

	session_save_path($_SERVER['DOCUMENT_ROOT'].'/sessions');
	session_cache_limiter('nocache, must_revalidate');
	session_set_cookie_params(0, "/");
	session_start();

	header('P3P: CP="ALL CURa ADMa DEVa TAIa OUR BUS IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE LOC OTC"');

	include $_SERVER['DOCUMENT_ROOT']."/db_inc.php";
	include $_SERVER['DOCUMENT_ROOT']."/config_inc.php";
	include $_SERVER['DOCUMENT_ROOT']."/config_arr_inc.php";
	include $_SERVER['DOCUMENT_ROOT']."/Mobile_Detect.php";
	include $_SERVER['DOCUMENT_ROOT']."/lib.mail.php";
	include $_SERVER['DOCUMENT_ROOT']."/lib/Login_chk_class.php";
	$objLogin = new Login_chk_class(array('db'=>$DB, 'mt_idx'=>$_SESSION['_mt_idx']));
	$member_info = $objLogin->act('login_chk');
	if($member_info['idx'] > 0){
		$cart = $DB->fetch_query("select count(0) as cnt from cart_t where mt_idx='".$member_info['idx']."' and ct_select = 0 and ct_direct!=1 and ct_status=0");
	}

	$detect_mobile = new Mobile_Detect;
	if($detect_mobile->isMobile()) {
		$chk_mobile = true;
	} else {
		$chk_mobile = false;
	}

	if($_SERVER['REMOTE_ADDR']=='ip') {
		$chk_admin = true;
	} else {
		$chk_admin = false;
	}

	function alert($msg, $url="") {
		if($url == "") {
			$url = "history.go(-1)";
		} else {
			$url = "document.location.href = '".$url."'";
		}

		if($msg != "") {
			echo "<script type=\"text/javascript\">
					alert('".$msg."');".$url.";
				</script>";
		} else {
			echo "<script type=\"text/javascript\">
					".$url.";
				</script>";
		}
		exit;
	}

	function just_alert($msg) {
	    echo "<script type=\"text/javascript\">
			alert('".$msg."');
		</script>";
	}

	function p_alert($msg, $url="") {
		if($url == "") {
			$url = "parent.location.reload()";
		} else {
			$url = "parent.document.location.href = '".$url."'";
		}

		if($msg != "") {
			echo "<script type=\"text/javascript\">
					alert('".$msg."');".$url.";
				</script>";
		} else {
			echo "<script type=\"text/javascript\">
					".$url.";
				</script>";
		}
		exit;
	}

	function p_confirm($msg, $url1, $url2) {
		echo "<script type=\"text/javascript\">
				if(confirm('".$msg."')) {
					parent.document.location.href = '".$url1."';
				} else {
					parent.document.location.href = '".$url2."';
				}
			</script>";
		exit;
	}

	function p_reload_to($url="") {
		if($url == "") {
			$url = "parent.location.reload()";
		} else {
			$url = "parent.document.location.href = '".$url."'";
		}

		echo "<script type=\"text/javascript\">
				".$url.";
			</script>";
		exit;
	}

	function gotourl($url) {
		$url = "document.location.href = '".$url."'";
		echo "<script type=\"text/javascript\">
				".$url.";
			</script>";
		exit;
	}

	function top_location_url($url) {
		$url = "top.location.href = '".$url."'";
		echo "<script type=\"text/javascript\">
				".$url.";
			</script>";
		exit;
	}

	function p_gotourl($url) {
		$url = "parent.document.location.href = '".$url."'";
		echo "<script type=\"text/javascript\">
				".$url.";
			</script>";
		exit;
	}

	function ps_gotourl($url) {
		$url = "opener.document.location.href = '".$url."'";
		echo "<script type=\"text/javascript\">
				".$url.";
			</script>";
		exit;
	}

	function page_listing($cur_page, $total_page, $url, $link_id="") {
		$retValue = "<nav class=\"m-3\" aria-label=\"Page navigation\"><ul class=\"page-light pagination justify-content-center\">";
		if($cur_page > 1) {
			$retValue .= "<li class=\"page-item\"><a class=\"page-link\" aria-label=\"이전\" href=\"".$url.($cur_page-1).$link_id."\"><span aria-hidden=\"true\">&laquo;</span></a></li>";
		} else {
			$retValue .= "<li class=\"page-item disabled\"><a class=\"page-link\" href=\"#\" tabindex=\"-1\" aria-disabled=\"true\"><span aria-hidden=\"true\">&laquo;</span></a></li>";
		}
		$start_page = ( ( (int)( ($cur_page - 1 ) / 5 ) ) * 5 ) + 1;
		$end_page = $start_page + 5;
		if($end_page >= $total_page) $end_page = $total_page;
		if($total_page > 1)
		for ($k=$start_page;$k<=$end_page;$k++)
		if($cur_page != $k) $retValue .= "<li class=\"page-item\"><a class=\"page-link\" href=\"".$url.$k.$link_id."\">".$k."</a></li>";
		else $retValue .= "<li class=\"page-item active\" aria-current=\"page\"><a class=\"page-link\" href=\"".$url.$k.$link_id."\">".$k."</a></li>";

		if($cur_page < $total_page && $total_page > 1) {
			$retValue .= "<li class=\"page-item\"><a class=\"page-link\" aria-label=\"다음\" href=\"".$url.($cur_page+1).$link_id."\"><span aria-hidden=\"true\">&raquo;</span></a></li>";
		} else {
			$retValue .= "<li class=\"page-item disabled\"><a class=\"page-link\" href=\"#\" tabindex=\"-1\" aria-disabled=\"true\"><span aria-hidden=\"true\">&raquo;</span></a></li>";
		}
		$retValue .= "</ul></nav>";

		return $retValue;
	}

	function page_listing_xhr($cur_page, $total_page, $obj_t, $pt_barcode_t, $act2="") {
		$retValue = "<nav class=\"text-center\"><ul class=\"pagination\">";
		if($cur_page > 1) {
			$retValue .= "<li><a href=\"javascript:get_review_pg('".$obj_t."', '".$pt_barcode_t."', '".($cur_page-1)."');\" aria-label=\"다음\"><span aria-hidden=\"true\">&laquo;</span></a></li>";
		} else {
			$retValue .= "<li class=\"disabled\"><span aria-hidden=\"true\">&laquo;</span></li>";
		}
		$start_page = ( ( (int)( ($cur_page - 1 ) / 5 ) ) * 5 ) + 1;
		$end_page = $start_page + 5;
		if($end_page >= $total_page) $end_page = $total_page;
		if($total_page > 1)
		for ($k=$start_page;$k<=$end_page;$k++)
		if($cur_page != $k) $retValue .= "<li><a href=\"javascript:get_review_pg('".$act2."', '".$obj_t."', '".$pt_barcode_t."', '".$k."');\">".$k."</a></li>";
		else $retValue .= "<li class=\"active\"><a href=\"javascript:get_review_pg('".$act2."', '".$obj_t."', '".$pt_barcode_t."', '".$k."');\">".$k."</a></li>";

		if($cur_page < $total_page && $total_page > 1) {
			$retValue .= "<li><a href=\"javascript:get_review_pg('".$act2."', '".$obj_t."', '".$pt_barcode_t."', '".($cur_page+1)."');\"><span aria-hidden=\"true\">&raquo;</span></a></li>";
		} else {
			$retValue .= "<li class=\"disabled\"><span aria-hidden=\"true\">&raquo;</span></li>";
		}
		$retValue .= "</ul></nav>";

		return $retValue;
	}

	function pageing_list($cur_page, $total_page, $url, $link_id="") {
		$retValue = "<ul class=\"page_btn\">";
		if($cur_page > 1) {
			$retValue .= "<li><a href=\"".$url.($cur_page-1).$link_id."\"><img src=\"./img/m_btn_left.png\" alt=\"왼쪽 버튼\" style=\"width: 27px;\"></a></li>";
		} else {
			$retValue .= "<li><a href=\"javascript:;\" tabindex=\"-1\"><img src=\"./img/m_btn_left.png\" alt=\"왼쪽 버튼\" style=\"width: 27px;\"></a></li>";
		}
		$start_page = ( ( (int)( ($cur_page - 1 ) / 5 ) ) * 5 ) + 1;
		$end_page = $start_page + 5;
		if($end_page >= $total_page) $end_page = $total_page;
		if($total_page > 1)
		for ($k=$start_page;$k<=$end_page;$k++)
		if($cur_page != $k) $retValue .= "<li><a href=\"".$url.$k.$link_id."\">".$k."</a></li>";
		else $retValue .= "<li><a class=\"on\" href=\"".$url.$k.$link_id."\">".$k."</a></li>";

		if($cur_page < $total_page && $total_page > 1) {
			$retValue .= "<li><a href=\"".$url.($cur_page+1).$link_id."\"><img src=\"./img/m_btn_right.png\" alt=\"오른쪽 버튼\" style=\"width: 27px;\"></a></li>";
		} else {
			$retValue .= "<li><a href=\"#\" tabindex=\"-1\"><img src=\"./img/m_btn_right.png\" alt=\"오른쪽 버튼\" style=\"width: 27px;\"></a></li>";
		}
		$retValue .= "</ul>";

		return $retValue;
	}

	function pageing_list_ajax($cur_page, $total_page, $url, $function_name) {
		$retValue = "<ul class=\"page-light pagination justify-content-center\">";

		if($cur_page > 1) {
			$retValue .= "<li class=\"page-item\"><a class=\"page-link\" aria-label=\"이전\" href=\"javascript:".$function_name."('".$url."', '".($cur_page-1)."');\" style='pointer-events: auto;'><span aria-hidden=\"true\">&laquo;</span></a></li>";
		} else {
			$retValue .= "<li class=\"page-item disabled\"><a class=\"page-link\" href=\"#\" tabindex=\"-1\" aria-disabled=\"true\"><span aria-hidden=\"true\">&laquo;</span></a></li>";
		}

		$start_page = ( ( (int)( ($cur_page - 1 ) / 5 ) ) * 5 ) + 1;
		$end_page = $start_page + 5;

		if($end_page >= $total_page) $end_page = $total_page;

		if($total_page > 1)

		for ($k=$start_page;$k<=$end_page;$k++)
		if($cur_page != $k) $retValue .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:".$function_name."('".$url."', '".$k."');\" style='pointer-events: auto;'>".$k."</a></li>";
		else $retValue .= "<li class=\"page-item active\" aria-current=\"page\"><a class=\"page-link\" href=\"javascript:".$function_name."('".$url."', '".$k."');\" style='pointer-events: auto;'>".$k."</a></li>";

		if($cur_page < $total_page && $total_page > 1) {
			$retValue .= "<li class=\"page-item\"><a class=\"page-link\" aria-label=\"다음\" href=\"javascript:".$function_name."('".$url."', '".($cur_page+1)."');\" style='pointer-events: auto;'><span aria-hidden=\"true\">&raquo;</span></a></li>";
		} else {
			$retValue .= "<li class=\"page-item disabled\"><a class=\"page-link\" href=\"#\" tabindex=\"-1\" aria-disabled=\"true\" style='pointer-events: auto;'><span aria-hidden=\"true\">&raquo;</span></a></li>";
		}

		$retValue .= "</ul>";

		return $retValue;
	}

	function check_file_ext($filename, $allow_ext) {
		if($filename == "") return true;
		$ext = get_file_ext($filename);
		$allow_ext = explode(";", $allow_ext);
		$sw_allow_ext = false;
		for ($i=0; $i<count($allow_ext); $i++)
			if($ext == $allow_ext[$i])
			{
				$sw_allow_ext = true;
				break;
			}

		return $sw_allow_ext;
	}

	function upload_file($srcfile, $destfile, $dir) {
		if($destfile == "") return false;
		move_uploaded_file($srcfile, $dir.$destfile);
		chmod($dir.$destfile, 0666);

		return true;
	}

	function get_file_ext($filename) {
		if($filename == "") return "";
		$type = explode(".", $filename);
		$ext = strtolower($type[count($type)-1]);

		return $ext;
	}

	function cut_str($strSource,$iStart,$iLength,$tail="") {
		$iSourceLength = mb_strlen($strSource, "UTF-8");

		if($iSourceLength > $iLength) {
			return mb_substr($strSource, $iStart, $iLength, "UTF-8").$tail;
		} else {
			return $strSource;
		}
	}

	function mailer($fname, $fmail, $to, $tname, $subject, $content, $type="1", $file="", $charset="utf-8", $cc="", $bcc="") {
		//사용안함 2019-08-21
		global $Mail_sender;

		$Mail_sender->isSMTP();
		$Mail_sender->CharSet = 'UTF-8';
		$Mail_sender->SMTPDebug = 0;
		$Mail_sender->Debugoutput = 'html';
		$Mail_sender->Host = 'smtp.daum.net';
		$Mail_sender->Port = 465;
		$Mail_sender->SMTPSecure = 'ssl';
		$Mail_sender->SMTPAuth = true;
		$Mail_sender->Username = "";
		$Mail_sender->Password = "";
		$Mail_sender->setFrom($fmail, $fname);
		$Mail_sender->addAddress($to, $tname);
		$Mail_sender->Subject = $subject;
		$Mail_sender->msgHTML($content);

		if(!$Mail_sender->send()) {
			return 'Message could not be sent.';
			return 'Mailer Error: ' . $Mail_sender->ErrorInfo;
		} else {
			return 'Message has been sent';
		}
	}

	function mailer_new($fname, $fmail, $to, $tname, $subject, $content) {
		global $Mail_sender;

		$Mail_sender->isSMTP();
		$Mail_sender->SMTPDebug = 0;
		$Mail_sender->CharSet = 'UTF-8';
		$Mail_sender->Debugoutput = 'html';
		//$Mail_sender->Host = 'smtp.gmail.com';
		//$Mail_sender->Port = 587;
		$Mail_sender->Host = 'smtp.mailplug.co.kr';
		$Mail_sender->Port = 465;

		//$Mail_sender->SMTPSecure = 'tls';
		$Mail_sender->SMTPSecure = 'ssl';
		$Mail_sender->SMTPAuth = true;
		$Mail_sender->Username = "";
		$Mail_sender->Password = "";

		$Mail_sender->setFrom($fmail, $fname);
		$Mail_sender->addAddress($to, $tname);

		$Mail_sender->Subject = $subject;
		$Mail_sender->msgHTML($content);

		if(!$Mail_sender->send()) {
			//return 'Message could not be sent.';
			return 'Mailer Error: ' . $Mail_sender->ErrorInfo;
		} else {
			return 'Message has been sent';
		}
	}

	/** @smtp Mail 보내기
	 *
	 * @param $fromName 보내는 사람 이름
	 * @param $fromEmail 보내는 사람 메일
	 * @param $toName 받는 사람 이름
	 * @param $toEmail 받는 사람 메일
	 * @param $subject 메일제목
	 * @param $contents 메일 내용
	 * @param $isDebug 디버깅할때 1로 해서 사용하세요.
	 * @return sendmail_flag 성공(true) 실패(false) 여부
	 */
	function sendMail($fromName, $fromEmail, $toName, $toEmail, $subject, $contents, $isDebug=0) {
		//Configuration
		//$smtp_host = "smtp.gmail.com";
		//$port = 587;
		$type = "text/html";
		$charSet = "UTF-8";
		$smtp_host = 'smtp.mailplug.co.kr';
		$port = 465;

		//Open Socket
		$fp = @fsockopen($smtp_host, $port, $errno, $errstr, 1);
		if($fp){
			//Connection and Greetting
			$returnMessage = fgets($fp, 128);
			if($isDebug)
				print "CONNECTING MSG:".$returnMessage."\n";
			fputs($fp, "HELO YA\r\n");
			$returnMessage = fgets($fp, 128);
			if($isDebug)
				print "GREETING MSG:".$returnMessage."\n";

			// 이부분에 다음과 같이 로긴과정만 들어가면됩니다.
			fputs($fp, "auth login\r\n");
			fgets($fp,128);
			fputs($fp, base64_encode("id")."\r\n");
			fgets($fp,128);
			fputs($fp, base64_encode("pwd")."\r\n");
			fgets($fp,128);

			fputs($fp, "MAIL FROM: <".$fromEmail.">\r\n");
			$returnvalue[0] = fgets($fp, 128);
			fputs($fp, "rcpt to: <".$toEmail.">\r\n");
			$returnvalue[1] = fgets($fp, 128);

			if($isDebug){
				print "returnvalue:";
				print_r($returnvalue);
			}

			//Data
			fputs($fp, "data\r\n");
			$returnMessage = fgets($fp, 128);
			if($isDebug)
				print "data:".$returnMessage;
			fputs($fp, "Return-Path: ".$fromEmail."\r\n");
			$fromName = "=?".$fromName."?B?".base64_encode($fromName)."?=";
			fputs($fp, "From: ".$fromName." <".$fromEmail.">\r\n");
			fputs($fp, "To: <".$toEmail.">\r\n");
			$subject = "=?".$charSet."?B?".base64_encode($subject)."?=";

			fputs($fp, "Subject: ".$subject."\r\n");
			fputs($fp, "Content-Type: ".$type."; charset=\"".$charSet."\"\r\n");
			fputs($fp, "Content-Transfer-Encoding: base64\r\n");
			fputs($fp, "\r\n");
			$contents= chunk_split(base64_encode($contents));

			fputs($fp, $contents);
			fputs($fp, "\r\n");
			fputs($fp, "\r\n.\r\n");
			$returnvalue[2] = fgets($fp, 128);

			//Close Connection
			fputs($fp, "quit\r\n");
			fclose($fp);

			//Message
			if (strstr($returnvalue[0], "^250")&&strstr($returnvalue[1], "^250")&&strstr($returnvalue[2], "^250")){
				$sendmail_flag = true;
			}else {
				$sendmail_flag = false;
				print "NO :".$errno.", STR : ".$errstr;
			}
		}

		if (! $sendmail_flag){
			echo "메일 보내기 실패";
		}
		return $sendmail_flag;
	}

	function thumnail($file, $save_filename, $save_path, $max_width, $max_height) {
	   $img_info = getimagesize($file);
	   if($img_info[2] == 1)
	   {
			  $src_img = ImageCreateFromGif($file);
			  }elseif($img_info[2] == 2){
			  $src_img = ImageCreateFromJPEG($file);
			  }elseif($img_info[2] == 3){
			  $src_img = ImageCreateFromPNG($file);
			  }else{
			  return 0;
	   }
	   $img_width = $img_info[0];
	   $img_height = $img_info[1];

	   if($img_width > $max_width || $img_height > $max_height)
	   {
			  if($img_width == $img_height)
			  {
					 $dst_width = $max_width;
					 $dst_height = $max_height;
			  }elseif($img_width > $img_height){
					 $dst_width = $max_width;
					 $dst_height = ceil(($max_width / $img_width) * $img_height);
			  }else{
					 $dst_height = $max_height;
					 $dst_width = ceil(($max_height / $img_height) * $img_width);
			  }
	   }else{
			  $dst_width = $img_width;
			  $dst_height = $img_height;
	   }
	   if($dst_width < $max_width) $srcx = ceil(($max_width - $dst_width)/2); else $srcx = 0;
	   if($dst_height < $max_height) $srcy = ceil(($max_height - $dst_height)/2); else $srcy = 0;

	   if($img_info[2] == 1)
	   {
			  $dst_img = imagecreate($max_width, $max_height);
	   }else{
			  $dst_img = imagecreatetruecolor($max_width, $max_height);
	   }

	   $bgc = ImageColorAllocate($dst_img, 255, 255, 255);
	   ImageFilledRectangle($dst_img, 0, 0, $max_width, $max_height, $bgc);
	   ImageCopyResampled($dst_img, $src_img, $srcx, $srcy, 0, 0, $dst_width, $dst_height, ImageSX($src_img),ImageSY($src_img));

	   if($img_info[2] == 1)
	   {
			  ImageInterlace($dst_img);
			  ImageGif($dst_img, $save_path.$save_filename);
	   }elseif($img_info[2] == 2){
			  ImageInterlace($dst_img);
			  ImageJPEG($dst_img, $save_path.$save_filename);
	   }elseif($img_info[2] == 3){
			  ImagePNG($dst_img, $save_path.$save_filename);
	   }
	   @ImageDestroy($dst_img);
	   @ImageDestroy($src_img);
	}

	function thumnail_width($file, $save_filename, $save_path, $max_width) {
		$img_info = getimagesize($file);
		if($img_info[2] == 1) {
			$src_img = ImageCreateFromGif($file);
		} else if($img_info[2] == 2) {
			$src_img = ImageCreateFromJPEG($file);
		} else if($img_info[2] == 3) {
			$src_img = ImageCreateFromPNG($file);
		} else {
			return 0;
		}

		$img_width = $img_info[0];
		$img_height = $img_info[1];

		$dst_width = $max_width;
		$dst_height = round($dst_width*($img_height/$img_width));

		$srcx = 0;
		$srcy = 0;

		if($img_info[2] == 1) {
			$dst_img = imagecreate($dst_width, $dst_height);
		} else {
			$dst_img = imagecreatetruecolor($dst_width, $dst_height);
		}

		ImageCopyResampled($dst_img, $src_img, $srcx, $srcy, 0, 0, $dst_width, $dst_height, ImageSX($src_img),ImageSY($src_img));

		if($img_info[2] == 1) {
			ImageInterlace($dst_img);
			ImageGif($dst_img, $save_path.$save_filename);
		} else if($img_info[2] == 2) {
			ImageInterlace($dst_img);
			ImageJPEG($dst_img, $save_path.$save_filename);
		} else if($img_info[2] == 3) {
			ImagePNG($dst_img, $save_path.$save_filename);
		}
		@ImageDestroy($dst_img);
		@ImageDestroy($src_img);
	}

	function thumbnail_crop_center($file, $save_filename, $save_path, $max_width, $max_height) {
		//사이즈에 맞춰 채워 넣는 방식으로 수정, 아래 scale_image_fill 함수 참고 2015-04-21 이창민
		$img_info = getimagesize($file);

		if($img_info[2] == 1) {
			$src = ImageCreateFromGif($file);
		} else if($img_info[2] == 2) {
			$src = ImageCreateFromJPEG($file);
		} else if($img_info[2] == 3) {
			$src = ImageCreateFromPNG($file);
		} else {
			return 0;
		}

		$dst = imagecreatetruecolor($max_width, $max_height);
		imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));

		$src_width = imagesx($src);
		$src_height = imagesy($src);

		$dst_width = imagesx($dst);
		$dst_height = imagesy($dst);

		$new_width = $dst_width;
		$new_height = round($new_width*($src_height/$src_width));
		$new_x = 0;
		$new_y = round(($dst_height-$new_height)/2);

		$next = $new_height < $dst_height;

		if($next) {
			$new_height = $dst_height;
			$new_width = round($new_height*($src_width/$src_height));
			$new_x = round(($dst_width - $new_width)/2);
			$new_y = 0;
		}

		imagecopyresampled($dst, $src , $new_x, $new_y, 0, 0, $new_width, $new_height, $src_width, $src_height);

		if($img_info[2] == 1) {
			ImageInterlace($dst);
			ImageGif($dst, $save_path.$save_filename);
		} else if($img_info[2] == 2) {
			ImageInterlace($dst);
			ImageJPEG($dst, $save_path.$save_filename);
		} else if($img_info[2] == 3) {
			ImagePNG($dst, $save_path.$save_filename);
		}

		@ImageDestroy($dst_img);
		@ImageDestroy($src_img);
	}

	function scale_image_fill($src_image, $save_filename, $save_path, $max_width, $max_height) {
		$img_info = getimagesize($src_image);

		if($img_info[2] == 1) {
			$src = ImageCreateFromGif($src_image);
		} else if($img_info[2] == 2) {
			$src = ImageCreateFromJPEG($src_image);
		} else if($img_info[2] == 3) {
			$src = ImageCreateFromPNG($src_image);
		} else {
			return 0;
		}

		$dst = imagecreatetruecolor($max_width, $max_height);
		imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));

		$src_width = imagesx($src);
		$src_height = imagesy($src);

		$dst_width = imagesx($dst);
		$dst_height = imagesy($dst);

		$new_width = $dst_width;
		$new_height = round($new_width*($src_height/$src_width));
		$new_x = 0;
		$new_y = round(($dst_height-$new_height)/2);

		$next = $new_height < $dst_height;

		if($next) {
			$new_height = $dst_height;
			$new_width = round($new_height*($src_width/$src_height));
			$new_x = round(($dst_width - $new_width)/2);
			$new_y = 0;
		}

		imagecopyresampled($dst, $src , $new_x, $new_y, 0, 0, $new_width, $new_height, $src_width, $src_height);

		if($img_info[2] == 1) {
			ImageInterlace($dst);
			ImageGif($dst, $save_path.$save_filename);
		} else if($img_info[2] == 2) {
			ImageInterlace($dst);
			ImageJPEG($dst, $save_path.$save_filename);
		} else if($img_info[2] == 3) {
			ImagePNG($dst, $save_path.$save_filename);
		}

		@ImageDestroy($dst_img);
		@ImageDestroy($src_img);
	}

	function scale_image_fit($src_image, $save_filename, $save_path, $max_width, $max_height) {
		$img_info = getimagesize($src_image);

		if($img_info[2] == 1) {
			$src = ImageCreateFromGif($src_image);
		} else if($img_info[2] == 2) {
			$src = ImageCreateFromJPEG($src_image);
		} else if($img_info[2] == 3) {
			$src = ImageCreateFromPNG($src_image);
		} else {
			return 0;
		}

		$dst = imagecreatetruecolor($max_width, $max_height);
		imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));

		$src_width = imagesx($src);
		$src_height = imagesy($src);

		$dst_width = imagesx($dst);
		$dst_height = imagesy($dst);

		$new_width = $dst_width;
		$new_height = round($new_width*($src_height/$src_width));
		$new_x = 0;
		$new_y = round(($dst_height-$new_height)/2);

		$next = $new_height > $dst_height;

		if($next) {
			$new_height = $dst_height;
			$new_width = round($new_height*($src_width/$src_height));
			$new_x = round(($dst_width - $new_width)/2);
			$new_y = 0;
		}

		imagecopyresampled($dst, $src , $new_x, $new_y, 0, 0, $new_width, $new_height, $src_width, $src_height);

		if($img_info[2] == 1) {
			ImageInterlace($dst);
			ImageGif($dst, $save_path.$save_filename);
		} else if($img_info[2] == 2) {
			ImageInterlace($dst);
			ImageJPEG($dst, $save_path.$save_filename);
		} else if($img_info[2] == 3) {
			ImagePNG($dst, $save_path.$save_filename);
		}

		@ImageDestroy($dst_img);
		@ImageDestroy($src_img);
	}

	function encrypt($str, $key) {
		# Add PKCS7 padding.
		$block = mcrypt_get_block_size('des', 'ecb');
		if (($pad = $block - (strlen($str) % $block)) < $block) {
		  $str .= str_repeat(chr($pad), $pad);
		}

		return mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
	}

	function decrypt($str, $key) {
		$str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);

		# Strip padding out.
		$block = mcrypt_get_block_size('des', 'ecb');
		$pad = ord($str[($len = strlen($str)) - 1]);
		if ($pad && $pad < $block && preg_match(
			  '/' . chr($pad) . '{' . $pad . '}$/', $str
												)
		   ) {
		  return substr($str, 0, strlen($str) - $pad);
		}
		return $str;
	}

	function get_openssl_encrypt($data) {
		$pass = DECODEKEY;
		$iv = DECODEKEY;

		$endata = @openssl_encrypt($data , "aes-256-cbc", $pass, true, $iv);
		$endata = base64_encode($endata);

		return $endata;
	}

	function get_openssl_decrypt($endata) {
		$pass = DECODEKEY;
		$iv = DECODEKEY;

		$data = base64_decode($endata);
		$dedata = @openssl_decrypt($data , "aes-256-cbc", $pass, true, $iv);

		return $dedata;
	}

	function get_text($str) {
		$source[] = "/</";
		$target[] = "&lt;";
		$source[] = "/>/";
		$target[] = "&gt;";
		$source[] = "/\'/";
		$target[] = "&#039;";

		return preg_replace($source, $target, strip_tags($str));
	}

	function cal_remain_days($s_date, $e_date) {
		if($e_date=="") return "0";

		$s_date_ex = explode("-", $s_date);
		$e_date_ex = explode("-", $e_date);

		$s_time = mktime(0, 0, 0, $s_date_ex[1], $s_date_ex[2], $s_date_ex[0]);
		$e_time = mktime(23, 59, 59, $e_date_ex[1], $e_date_ex[2], $e_date_ex[0]);

		if($s_time > $e_time) {
			return 0;
		} else {
			$result_time = ($e_time - $s_time) / (60*60*24);

			if($result_time < 0) {
				return 0;
			} else {
				return round($result_time);
			}
		}
	}

	function quote2entities($string,$entities_type='number') {
		$search = array("\"","'");
		$replace_by_entities_name = array("&quot;","&apos;");
		$replace_by_entities_number = array("&#34;","&#39;");
		$do = null;
		if ($entities_type == 'number') {
			$do = str_replace($search,$replace_by_entities_number,$string);
		} else if ($entities_type == 'name') {
			$do = str_replace($search,$replace_by_entities_name,$string);
		} else {
			$do = addslashes($string);
		}

		return $do;
	}

	function printr($arr_val) {
		echo "<pre>";
		print_r($arr_val);
		echo "</pre>";
	}

	function fnc_Day_Name($strDate){
		$strDate = substr($strDate,0,10);
		$days = array("일","월","화","수","목","금","토");
		$temp_day = date("w", strtotime($strDate));
		return $days[$temp_day];
	}

	function DateType($strDate, $type="1"){
		if($strDate=="" || $strDate=="0000-00-00 00:00:00") {
			$strDate = "-";
		} else {
			if($type=="1") {
				$strDate = str_replace("-",".",substr($strDate,0,10));
			} else if($type=="2") {
				$strDate = str_replace("-",".",substr($strDate,0,16));
			} else if($type=="3") {
				$strDate = str_replace("-",".",substr($strDate,0,10))."&nbsp;(".fnc_Day_Name($strDate).")";
			} else if($type=="4") {
				$strDate = str_replace("-",".",substr($strDate,0,10))."&nbsp;(".fnc_Day_Name($strDate).")&nbsp;".substr($strDate,11,5);
			} else if($type=="5") {
				$strDate = str_replace("-",".",substr($strDate,2,8));
			} else if($type=="6") {
				$strDate = str_replace("-",".",substr($strDate,2,8))."&nbsp;(".fnc_Day_Name($strDate).")&nbsp;".substr($strDate,11,5);
			} else if($type=="7") {
				$strDate = substr($strDate,11,5);
			} else if($type=="8") {
				$strDate = str_replace("-",".",substr($strDate,2,8))."&nbsp;".substr($strDate,11,5);
			} else if($type=="9") {
				$strDate = str_replace("-",".",substr($strDate,2,8))."&nbsp;(".fnc_Day_Name($strDate).")<br/>".substr($strDate,11,5);
			} else if($type=="10") {
				$strDate = str_replace("-","년 ",substr($strDate,2,5))."월";
			} else if($type=="11") {
				$strDate_ex1 = explode(' ', $strDate);
				$strDate_ex2 = explode('-', $strDate_ex1[0]);

				$strDate = $strDate_ex2[0]."년 ".$strDate_ex2[1]."월 ".$strDate_ex2[2]."일";
			}
		}

		return $strDate;
	}

	function substr_star($str){
		$str_len = mb_strlen($str);
		$str_arr = str_split($str);

		$result = "";
		for($i=0 ; $i < $str_len ; $i++){
			if($i < 3){
				$result .= $str_arr[$i];
			}else{
				$result .= "*";
			}
		}
		return $result;
	}

	function mt_pw_make() {
		return substr(md5(time()), 0, 8);
	}

	function mt_sms_make() {
		return mt_rand(111111, 999999);
	}

	function save_remote_img_curl_fn($url, $dir, $tmpname) {
		$filename = '';

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_NOBODY, true);

		curl_exec ($ch);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($http_code == 200) {
			$filename = basename($url);
			if(preg_match("/\.(gif|jpg|jpeg|png)$/i", $filename)) {
				$filepath = $dir;
				@mkdir($filepath, '0755');
				@chmod($filepath, '0755');

				// 파일 다운로드
				$path = $filepath.'/'.$tmpname;
				$fp = fopen ($path, 'w');

				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, $url );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
				curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
				curl_setopt( $ch, CURLOPT_FILE, $fp );
				curl_exec( $ch );
				curl_close( $ch );

				fclose( $fp );

				// 다운로드 파일이 이미지인지 체크
				if(is_file($path)) {
					$size = @getimagesize($path);
					if($size[2] < 1 || $size[2] > 3) {
						@unlink($path);
						$filename = '';
					} else {
						$ext = array(1=>'gif', 2=>'jpg', 3=>'png');
						$filename = $tmpname.'.'.$ext[$size[2]];
						rename($path, $filepath.'/'.$filename);
						//@chmod($filepath.'/'.$filename, '0644');
					}
				}
			}
		}

		return $filename;
	}

	function save_remote_img_curl($url, $dir, $mt_idx) {
		$filename = '';

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_NOBODY, true);

		curl_exec ($ch);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($http_code == 200) {
			$filename = basename($url);
			if(preg_match("/\.(gif|jpg|jpeg|png)$/i", $filename)) {
				//$tmpname = date('YmdHis').(microtime(true) * 10000);
				$tmpname = "mt_img_".$mt_idx."_".date("YmdHis");
				$filepath = $dir;
				@mkdir($filepath, '0755');
				@chmod($filepath, '0755');

				// 파일 다운로드
				$path = $filepath.'/'.$tmpname;
				$fp = fopen ($path, 'w');

				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, $url );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
				curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
				curl_setopt( $ch, CURLOPT_FILE, $fp );
				curl_exec( $ch );
				curl_close( $ch );

				fclose( $fp );

				// 다운로드 파일이 이미지인지 체크
				if(is_file($path)) {
					$size = @getimagesize($path);
					if($size[2] < 1 || $size[2] > 3) {
						@unlink($path);
						$filename = '';
					} else {
						$ext = array(1=>'gif', 2=>'jpg', 3=>'png');
						$filename = $tmpname.'.'.$ext[$size[2]];
						rename($path, $filepath.'/'.$filename);
						@chmod($filepath.'/'.$filename, '0644');
					}
				}
			}
		}

		return $filename;
	}

	function save_remote_img_file($url, $dir, $mt_idx) {
		$filename = file_get_contents($url);
		$img_info = pathinfo($url);
		$tmpname = "mt_img_".$mt_idx."_".date("YmdHis").'.'.$img_info[extension];
		file_put_contents($dir."/".$tmpname, $filename);

		return $tmpname;
	}

	function save_facebook_profile_img($url, $dir, $mt_idx) {
		$filename = '';

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_NOBODY, true);

		curl_exec ($ch);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($http_code == 200) {
			$filename = basename($url);
			$filename_ex = explode("?", $filename);
			$filename = $filename_ex[0];
			if(preg_match("/\.(gif|jpg|jpeg|png)$/i", $filename)) {
				//$tmpname = date('YmdHis').(microtime(true) * 10000);
				$tmpname = "mt_img_".$mt_idx."_".date("YmdHis");
				$filepath = $dir;
				@mkdir($filepath, '0755');
				@chmod($filepath, '0755');

				// 파일 다운로드
				$path = $filepath.'/'.$tmpname;
				$fp = fopen ($path, 'w');

				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, $url );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
				curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
				curl_setopt( $ch, CURLOPT_FILE, $fp );
				curl_exec( $ch );
				curl_close( $ch );

				fclose( $fp );

				// 다운로드 파일이 이미지인지 체크
				if(is_file($path)) {
					$size = @getimagesize($path);
					if($size[2] < 1 || $size[2] > 3) {
						@unlink($path);
						$filename = '';
					} else {
						$ext = array(1=>'gif', 2=>'jpg', 3=>'png');
						$filename = $tmpname.'.'.$ext[$size[2]];
						rename($path, $filepath.'/'.$filename);
						//@chmod($filepath.'/'.$filename, '0644');
					}
				}
			}
		}

		return $filename;
	}

	function inconv_post($s1, $s2, $arr) {
		foreach($arr as $key => $val) {
			$arr[$key] = iconv($s1, $s2, $val);
		}

		return $arr;
	}

	function date_diffrent($sdate, $edate) {
		$date1 = new DateTime($sdate);
		$date2 = new DateTime($edate);
		$diff = date_diff($date1, $date2);

		$return = "";
		if($diff->days==0) {
			if($diff->d==0) {
				if($diff->h==0) {
					if($diff->i==0) {
						$return = $diff->s."초";
					} else {
						$return = $diff->i."분";
					}
				} else {
					$return = $diff->h."시";
				}
			}
		} else {
			if($diff->days>7) {
				$return = round($diff->days/7)."주";
			} else {
				$return = $diff->days."일";
			}
		}

		return $return;
	}

	function save_parsing_img($url, $dir, $pt_size, $bt_idx, $img_num) {
		$filename = '';

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_NOBODY, true);

		curl_exec ($ch);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($http_code == 200) {
			$filename = basename($url);
			$filename_ex = explode("?", $filename);
			$filename = $filename_ex[0];
			if(preg_match("/\.(gif|jpg|jpeg|png)$/i", $filename)) {
				//$tmpname = date('YmdHis').(microtime(true) * 10000);
				$tmpname = "pt_img_".$pt_size."_".$bt_idx."_".$img_num;
				$filepath = $dir;
				@mkdir($filepath, '0755');
				@chmod($filepath, '0755');

				// 파일 다운로드
				$path = $filepath.'/'.$tmpname;
				$fp = fopen ($path, 'w');

				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, $url );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
				curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
				curl_setopt( $ch, CURLOPT_FILE, $fp );
				curl_exec( $ch );
				curl_close( $ch );

				fclose( $fp );

				// 다운로드 파일이 이미지인지 체크
				if(is_file($path)) {
					$size = @getimagesize($path);
					if($size[2] < 1 || $size[2] > 3) {
						@unlink($path);
						$filename = '';
					} else {
						$ext = array(1=>'gif', 2=>'jpg', 3=>'png');
						$filename = $tmpname.'.'.$ext[$size[2]];
						rename($path, $filepath.'/'.$filename);
						//@chmod($filepath.'/'.$filename, '0644');
					}
				}
			}
		}

		return $filename;
	}

	function save_owner_img($url, $dir, $pt_barcode, $pt_idx) {
		$rtn_filename = '';

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_NOBODY, true);

		curl_exec ($ch);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($http_code == 200) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_HEADER, false );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 3 );
			$raw = curl_exec( $ch );
			curl_close( $ch );

			if(stristr($url, 'product_image.php')) {
				$url_ex = explode("?img=", $url);
				$filename = $url_ex[1];
			} else {
				$url_info = pathinfo($url);
				$filename = $url_info[basename];
			}

			$path = $dir."/".$filename;

			$fp = fopen ($path, 'w');
			fwrite($fp, $raw);
			fclose( $fp );

			if(is_file($path)) {
				$size = @getimagesize($path);
				if($size[2] < 1 || $size[2] > 3) {
					@unlink($path);
					$rtn_filename = '';
				} else {
					$ext = array(1=>'gif', 2=>'jpg', 3=>'png');
					$rtn_filename = $pt_barcode."_".$pt_idx.'.'.$ext[$size[2]];
					rename($path, $dir.'/'.$rtn_filename);
				}
			}
		}

		return $rtn_filename;
	}

	function get_pt_file_url($pt_file, $mng_chk="") {
		global $ct_noimg_url, $ct_product_url, $ct_product_dir_a, $ct_product_dir_r;

		$pt_file_ex = explode("|", $pt_file);

		if($pt_file_ex[0]=="http") {
			$pt_file_ex_txt = strip_tags($pt_file_ex[1]);
		} else {
			if($mng_chk=="Y") {
				$pt_dir = $ct_product_dir_a;
			} else {
				$pt_dir = $ct_product_dir_r;
			}

			if(is_file($pt_dir."/".$pt_file_ex[0])) {
				$pt_file_ex_txt = $ct_product_url."/".$pt_file_ex[0];
			} else {
				$pt_file_ex_txt = $ct_noimg_url;
			}
		}

		return $pt_file_ex_txt;
	}

	function save_url_img($url, $dir, $tmp_nm) {
		$filename = '';

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_NOBODY, true);

		curl_exec ($ch);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($http_code == 200) {
			$filename = basename($url);
			$filename_ex = explode("?", $filename);
			$filename = $filename_ex[0];
			if(preg_match("/\.(gif|jpg|jpeg|png)$/i", $filename)) {
				$tmpname = $tmp_nm;
				$filepath = $dir;
//				@mkdir($filepath, '0755');
//				@chmod($filepath, '0755');

				// 파일 다운로드
				$path = $filepath.'/'.$tmpname;
				$fp = fopen ($path, 'w');

				$ch = curl_init();
				curl_setopt( $ch, CURLOPT_URL, $url );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
				curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 3 );
				curl_setopt( $ch, CURLOPT_FILE, $fp );
				curl_exec( $ch );
				curl_close( $ch );

				fclose( $fp );

				// 다운로드 파일이 이미지인지 체크
				if(is_file($path)) {
					$size = @getimagesize($path);
					if($size[2] < 1 || $size[2] > 3) {
						@unlink($path);
						$filename = '';
					} else {
						$ext = array(1=>'gif', 2=>'jpg', 3=>'png');
						$filename = $tmpname.'.'.$ext[$size[2]];
						rename($path, $filepath.'/'.$filename);
						//@chmod($filepath.'/'.$filename, '0644');
					}
				}
			}
		}

		return $filename;
	}

	function f_curl_post($url, $code) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "selfcode=".$code);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$rtn = curl_exec($ch);
		curl_close($ch);

		return $rtn;
	}

	function f_curl_post_field($url, $field) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $field);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		$rtn = curl_exec($ch);
		curl_close($ch);

		return $rtn;
	}

	function ex_title_chk($title) {
		global $arr_ex_title;

		$q = 0;
		foreach($arr_ex_title as $key => $val) {
			if(strstr($title, $val)) {
				$q++;
			}
		}

		if($q>0) {
			return "";
		} else {
			return $title;
		}
	}

	function get_time() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	function format_phone($phone) {
		$phone = preg_replace("/[^0-9]/", "", $phone);
		$length = strlen($phone);

		switch($length){
			case 11 :
				return preg_replace("/([0-9]{3})([0-9]{4})([0-9]{4})/", "$1-$2-$3", $phone);
			break;
			case 10:
				return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $phone);
			break;
			case 9:
				return preg_replace("/([0-9]{2})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $phone);
			break;
			default :
				return $phone;
			break;
		}
	}

	function delete_all($dir) {
		$d = @dir($dir);
		while ($entry = $d->read()) {
			if ($entry == "." || $entry == "..") continue;
			if (is_dir($entry)) delete_all($entry);
			else unlink($dir."/".$entry);
		}
	}

	function f_sms_send($receiver, $msg, $subject="", $rdate="", $rtime="") {
		$sms_url = "https://apis.aligo.in/send/";
		$sms['user_id'] = ALIGO_USER_ID;
		$sms['key'] = ALIGO_KEY;

		$host_info = explode("/", $sms_url);
		$port = $host_info[0] == 'https:' ? 443 : 80;

		$sms['msg'] = stripslashes($msg);
		$sms['receiver'] = $receiver;
		$sms['destination'] = '';
		$sms['sender'] = ALIGO_SENDER;
		$sms['rdate'] = $rdate;
		$sms['rtime'] = $rtime;
		$sms['testmode_yn'] = 'N';
		$sms['title'] = $subject;
		$sms['msg_type'] = 'SMS';

		$oCurl = curl_init();
		curl_setopt($oCurl, CURLOPT_PORT, $port);
		curl_setopt($oCurl, CURLOPT_URL, $sms_url);
		curl_setopt($oCurl, CURLOPT_POST, 1);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sms);
		curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
		$ret = curl_exec($oCurl);
		curl_close($oCurl);

		return $ret;
	}

	//$result 성공: true, 실패: false  $msg 전달 문구  $data 배열 데이터
	function result_data($result, $msg, $data) {
		$arr = array();

		$arr['result'] = $result;
		$arr['msg'] = $msg;
//		if($data) {
//			$arr['data'] = $data;
//		} else {
//			$arr['data'] = '';
//		}
        $arr['data'] = $data;

		$obj = json_encode($arr, JSON_UNESCAPED_UNICODE);

		return $obj;
	}

	function recusive_category($_level, $_pid) {
		global $DB;

		unset($list);
		$query = "select * from category_t where ct_level = '".$_level."' and ct_pid = '".$_pid."' order by ct_rank asc, ct_id asc, ct_name asc";
		$list = $DB->select_query($query);

		if($list) {
			foreach($list as $row) {
				$s_level = "";
				if($row['ct_level']) {
					$s_level = "&nbsp;&nbsp;&nbsp;┗";
					for($i=1; $i<$row['ct_level']; $i++) $s_level = "&nbsp;&nbsp;&nbsp;".$s_level;
				}

				$ct_name_t = get_text($row['ct_name']);

				$s_add = "<a href='./category_form.php?act=add&ct_idx=".$row['ct_id']."&ct_level=".$row['ct_level']."' class='btn btn-outline-secondary btn-sm mx-sm-1'>추가</a>";
				$s_mod = "<a href='./category_form.php?act=update&ct_idx=".$row['ct_id']."' class='btn btn-outline-primary btn-sm mx-sm-1'>수정</a>";
				$s_del = "<a href='javascript:;' onclick=\"del('./category_update.php?act=del&ct_idx=".$row['ct_id']."')\" class='btn btn-outline-danger btn-sm mx-sm-1'>삭제</a>";

				echo "<tr>
						<td>".$s_level." ".$ct_name_t."</td>
						<td>".$row['ct_level']."</td>
						<td>".$row['ct_rank']."</td>
						<td>".$s_add."&nbsp;".$s_mod."&nbsp;".$s_del."</td>
					</tr>";

				recusive_category($row['ct_level']+1, $row['ct_id']);
			}
		}

		return false;
	}

	function recusive_ca_name($pct_id) {
		global $DB;

		$arr_ca_name = array();

		$query = "select * from product_category_t where idx = '".$pct_id."'";
		$row = $DB->fetch_query($query);


		return $row['pc_name'];

	}

	function recusive_ca_id($ct_id) {
		global $DB;

		$arr_ca_name = array();

		$query = "select * from product_category_t where ct_id = '".$ct_id."'";
		$row = $DB->fetch_query($query);

		if($row['ct_pid']=='0') {
			return $row['ct_id'];
		} else {
			return $row['ct_id']."|".recusive_ca_id($row['ct_pid']);
		}
	}

	function get_ca_name_breadcrumb($pct_id) {
		
		$pct_id_arr = explode(',', $pct_id);
		$ca_name_t = array();
		foreach($pct_id_arr as $val){
			$ca_name_t[] = recusive_ca_name($val);
		}
		return implode(',', $ca_name_t);
		/*$ca_name_t = recusive_ca_name($ct_id);
		$ca_name_t_ex = explode('|', $ca_name_t);
		krsort($ca_name_t_ex);
		$ca_name_t_ex_im = implode(' > ', $ca_name_t_ex);
		return $ca_name_t_ex_im;*/
	}

	function get_product_info($pt_idx) {
		global $DB, $ct_img_url, $pt_image_num;

		unset($arr_rtn);

		//상품기본정보
		$query1 = "
			select *, a1.idx as pt_idx from product_t a1
			where a1.idx = '".$pt_idx."'
		";
		$row1 = $DB->fetch_query($query1);

		for($q=1;$q<=$pt_image_num;$q++) {
			if($row1['pt_image'.$q]!='') {
				$row1['pt_image'.$q.'_url'] = $ct_img_url.'/'.$row1['pt_image'.$q];
				$row1['pt_image'.$q.'_on'] = $row1['pt_image'.$q];
			}
		}

//		if($row1['pt_sale_type_chk']=='Y') {
//			$row1['pt_discount'] = $row1['pt_discount_price'];
//		} else {
//			$row1['pt_discount'] = $row1['pt_discount_per'];
//		}

		$pt_option_name_cnt = 0;
		if($row1['pt_option_name1']!='') {
			$pt_option_name_cnt++;
		}
		if($row1['pt_option_name2']!='') {
			$pt_option_name_cnt++;
		}
		if($row1['pt_option_name3']!='') {
			$pt_option_name_cnt++;
		}
		$row1['pt_option_name_cnt'] = $pt_option_name_cnt;

		$pt_option_direct_val_ex = explode('|:|', $row1['pt_option_direct_val']);
		$pt_option_direct_cnt = count($pt_option_direct_val_ex);
		$row1['pt_option_direct_cnt'] = $pt_option_direct_cnt;
		$row1['pt_option_direct_val1'] = $pt_option_direct_val_ex[0];
		$row1['pt_option_direct_val2'] = $pt_option_direct_val_ex[1];
		$row1['pt_option_direct_val3'] = $pt_option_direct_val_ex[2];
		$row1['pt_option_direct_val4'] = $pt_option_direct_val_ex[3];
		$row1['pt_option_direct_val5'] = $pt_option_direct_val_ex[4];

		if($row1=='') {
			$row1 = array();
		}

		$arr_rtn['product_t'] = $row1;

		//상품옵션
		$query5 = "
			select * from product_option_t
			where pt_idx = '".$pt_idx."'
		";
		$row5 = $DB->fetch_query($query5);

		if($row5=='') {
			$row5 = array();
		}

		$arr_rtn['product_option_t'] = $row5;

		return $arr_rtn;
	}

	function get_pt_code() {
		global $DB;

		$unique = false;
		do {
			$pt_code = substr("B".strtoupper(md5(time())), 0, 12);
			$query = "select * from product_t where pt_code = '".$pt_code."'";
			$cnt = $DB->count_query($query);
			if ($cnt < 1) {
				$unique = true;
				break;
			}
		}
		while ($unique == false);

		return $pt_code;
	}

	function get_mem_info($mt_idx) {
		global $DB;

		$query = "
			select * from member_t
			where idx = '".$mt_idx."'
		";
		$row = $DB->fetch_query($query);

		return $row;
	}

	function get_seller_info($mt_idx) {
		global $DB;

		$query = "
			select * from seller_t
			where mt_idx = '".$mt_idx."'
		";
		$row = $DB->fetch_query($query);

		return $row;
	}

	function get_product_t_info($pt_idx) {
		global $DB;

		$query = "
			select * from product_t
			where idx = '".$pt_idx."'
		";
		$row = $DB->fetch_query($query);

		return $row;
	}

	function get_setup_t_info() {
		global $DB;

		$query = "select * from setup_t where idx = '1'";
		$row = $DB->fetch_query($query);

		return $row;
	}

	function get_bootom_ct_id($ct_id) {
		global $DB;

		$query = "
			select * from category_bottom_all
			where ct_id = '".$ct_id."'
		";
		$row = $DB->fetch_query($query);

		return $row['ct_id_txt'];
	}

	function get_bottom_all($ct_id) {
		global $DB;

		unset($list);
		$query = "select * from category_t where ct_pid = '".$ct_id."'";
		$list = $DB->select_query($query);

		$arr_ct_id_txt = array();
		$arr_ct_id_txt[] = $ct_id;
		if($list) {
			foreach($list as $row) {
				if($row['ct_id']) {
					$arr_ct_id_txt[] = $row['ct_id'];

					unset($list2);
					$query2 = "select * from category_t where ct_pid = '".$row['ct_id']."'";
					$list2 = $DB->select_query($query2);

					if($list2) {
						foreach($list2 as $row2) {
							if($row2['ct_id']) {
								$arr_ct_id_txt[] = $row2['ct_id'];

								unset($list3);
								$query3 = "select * from category_t where ct_pid = '".$row2['ct_id']."'";
								$list3 = $DB->select_query($query3);

								if($list3) {
									foreach($list3 as $row3) {
										if($row3['ct_id']) {
											$arr_ct_id_txt[] = $row3['ct_id'];

											unset($list4);
											$query4 = "select * from category_t where ct_pid = '".$row3['ct_id']."'";
											$list4 = $DB->select_query($query4);

											if($list4) {
												foreach($list4 as $row4) {
													if($row4['ct_id']) {
														$arr_ct_id_txt[] = $row4['ct_id'];
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return $arr_ct_id_txt;
	}

	function send_notification($token_list, $title, $message, $clickAction="", $content_idx="") {
		//FCM 인증키
		$FCM_KEY = 'FCM_KEY';
        //$FCM_KEY = SECRETKEY;
		//FCM 전송 URL
		$FCM_URL = 'https://fcm.googleapis.com/fcm/send';

		//전송 데이터
		$fields = array (
			'registration_ids' => $token_list,
			'data' => array (
				'title' => $title,
				'message' => $message,
				'intent' => $clickAction,
				'content_idx' => $content_idx,
			),
			'notification' => array (
				'title' => $title,
				'body' => $message,
				'content_idx' => $content_idx,
				'badge' => 1,
			),
		);

		//설정
		$headers = array( 'Authorization:key='. $FCM_KEY, 'Content-Type:application/json' );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $FCM_URL);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch);        
		if($result === false) {
			die('Curl failed: ' . curl_error($ch));
		}
		curl_close($ch);
		//$obj = json_decode($result);
        $obj = $result;
        //print_R($obj);
        //print_R($result);
        //exit;
		return $obj;
	}

	function send_notification2($token_list, $title, $message, $link1, $link2, $chk) {
		//$link1 : 페이지 이름, $link2 : 보내는 사람 아이디
		//FCM 인증키
		$FCM_KEY = 'FCM_KEY';
		//$FCM_KEY = SECRETKEY;
		//FCM 전송 URL
		$FCM_URL = 'https://fcm.googleapis.com/fcm/send';

		//푸시 알림 설정 꺼져있으면
		if($chk == "N") {
			//전송 데이터
			$fields = array (
				'registration_ids' => $token_list,
				'data' => array (
					'title' => $title,
					'message' => $message,
					'link1' => $link1,
					'link2' => $link2,
				),
			);
		} else {
			//전송 데이터
			$fields = array (
				'registration_ids' => $token_list,
				'data' => array (
					'title' => $title,
					'message' => $message,
					'link1' => $link1,
					'link2' => $link2,
				),
				'notification' => array (
					'title' => $title,
					'body' => $message,
					'link1' => $link1,
					'link2' => $link2,
					'badge' => 1,
				),
			);
		}

		//설정
		$headers = array( 'Authorization:key='. $FCM_KEY, 'Content-Type:application/json' );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $FCM_URL);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch);
		if($result === false) {
			die('Curl failed: ' . curl_error($ch));
		}
		curl_close($ch);
		//$obj = json_decode($result);
		$obj = $result;
		//print_R($obj);
		//print_R($result);
		//exit;
		return $obj;
	}

	function get_template_info($ptl_idx) {
		global $DB;

		unset($arr_rtn);

		//상품 배송
		$query4 = "
			select * from template_t
			where idx = '".$ptl_idx."'
		";
		$row4 = $DB->fetch_query($query4);

		if($row4['pdt_add_section_price_chk']=='Y') {
			$row4['pdt_add_section_price_chk_t'] = '1';
		} else {
			$row4['pdt_add_section_price_chk_t'] = '2';
		}
		if($row4['pdt_add_section_price_type_chk']=='2') {
			$row4['pdt_add_section_price_type_chk_t'] = '1';
		} else {
			$row4['pdt_add_section_price_type_chk_t'] = '2';
		}

		if($row4=='') {
			$row4 = array();
		}

		$arr_rtn['product_deliveryInfo_t'] = $row4;

		return $arr_rtn;
	}

	function get_ot_code() {
		global $DB;

		$unique = false;
		do {
			$uid = substr("B".date("ymdHis", time()).strtoupper(md5(mt_rand())), 0, 16);
			$query = "select * from cart_t where ot_code = '".$uid."'";
			$cnt = $DB->count_query($query);
			$query2 = "select * from order_t where ot_code = '".$uid."'";
			$cnt2 = $DB->count_query($query2);
			$query3 = "select * from coin_t where ct_code = '".$uid."'";
			$cnt3 = $DB->count_query($query3);
			if ($cnt < 1 && $cnt2 < 1 && $cnt3 < 1) {
				$unique = true;
				break;
			}
		}
		while ($unique == false);

		return $uid;
	}

	function get_ot_pcode() {
		global $DB;

		$unique = false;
		do {
			$uid = substr("BP".date("ymdHis", time()).strtoupper(md5(mt_rand())), 0, 16);
			$query = "select * from cart_t where ot_pcode = '".$uid."'";
			$cnt = $DB->count_query($query);
			if ($cnt < 1) {
				$unique = true;
				break;
			}
		}
		while ($unique == false);

		return $uid;
	}

	function get_ct_code() {
		global $DB;

		$unique = false;
		do {
			$uid = substr("C".date("ymdHis", time()).strtoupper(md5(mt_rand())), 0, 16);
			$query = "select * from coupon_t where ct_code = '".$uid."'";
			$cnt = $DB->count_query($query);
			if ($cnt < 1) {
				$unique = true;
				break;
			}
		}
		while ($unique == false);

		return $uid;
	}

	function mt_id_pad($mt_id) {
		return str_pad(cut_str($mt_id, 0, 3, ''), 7, '****');
	}

	function f_get_kakao_regioncode2coord($address_name) {
		$url = "https://dapi.kakao.com/v2/local/search/address.json?analyze_type=similar&page=1&size=10&query=".urlencode(trim($address_name));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json', 'Authorization: KakaoAK 9f008f4344afa8d23d31341679dd7b82'));
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$rtn = curl_exec($ch);
		curl_close($ch);

		$rtn = json_decode($rtn, true);
		$rtn = $rtn['documents']['0']['road_address'];

		return $rtn;
	}

	function policy_point(){
		global $DB;
		$row = $DB->fetch_assoc("select * from policy_t where idx = 1");
		return $row['pt_point'];
	}

    //알림 등록 (구분, 알림타입, 대상아이디, 관련테이블, 해당인덱스, bo_table, point) // 읽음여부(Y,N)
	function proc_noti($push_type, $send_to='', $ref_table='', $ref_idx='', $ref_param=array(), $push_board='', $point='', $push_link="", $send_msg="") {
		global $DB;
		global $arr_grade, $ct_img_url, $arr_ct_status, $config;

        if($send_to==''){
            $member_arr = $DB->select_query("select mt_id from member_t where mt_pushing='Y' and mt_level=2 and mt_fcm is not null");
            foreach($member_arr as $row){
                $send_to .= ','.$row['mt_id'];
            }
        }
		$send_to_arr = explode(',',$send_to); //개별유저 아이디
		$send_to_arr = array_values(array_filter(array_map('trim',$send_to_arr)));
        
		$send_check = false;

		if (count($send_to_arr) > 0) { $send_check = true; }

		if ($push_board==='notice' || ($push_type==='admin' && $send_to==='')) { $send_check = true; }

		if ($send_check===true) {

            $pst_type = $push_type;

            $pst_category = '';

            $pst_level = '1';

            $shot_title = '';

            $point_str = $point ? str_replace('-','',$point) : '';

			switch($push_type) {

				case "push":

					break;
                
				case "content": // 콘텐츠등록, 추천
                    $row_ct = $DB->fetch_assoc(" SELECT * FROM contents_t WHERE idx='{$ref_idx}' ");
					$title 		= $send_msg.'<'.$row_ct['ct_title'].'>';
					$content 	= $row_ct['ct_contents'];
					break;

				case "notice": // 공지사항

					$target_name = "";

					$title 		= "공지알림";

					$content 	= get_text($target_name);

					break;


				case "qa":

                case "memo":

                case "memo_answer":

				case "chat":

					$title = '1:1문의 알림';

					$content = "문의답변이 도착헸습니다.";

                	$message01 = $ref_param;

					break;

                case "admin":

                    $wr = $DB->fetch_assoc(" SELECT * FROM pushnotification_t WHERE idx='{$ref_idx}' ");

                    $title 		= $wr['pst_title'];

                    $content 	= $wr['pst_content'];

                    break;

				default:

					$title      = '알림';

					$content    = $send_msg ? $send_msg : "";

					break;

			}

			$title  = $title ? $title : '알림';

			$content = $content ? $content : "새소식이 도착했습니다";

			$push_link = $push_link ? $push_link : "";


			$send_title = $title;

			$send_msg = get_text(stripslashes($content));


			$push_qry = " FROM member_t A WHERE mt_level=2 AND (mt_rdate='' or mt_rdate is null) AND (mt_ldate>mt_lgdate OR mt_lgdate IS NULL)";

			for ($j=0;$j<count($send_to_arr);$j++) {

				if ($j===0) {

					$push_qry .= " AND ( ";

				}

				$push_qry .= " mt_id='{$send_to_arr[$j]}' ";

				if ($j<count($send_to_arr)-1) {

					$push_qry .= " OR ";

				}

				if ($j===count($send_to_arr)-1) {

					$push_qry .= " ) ";

				}

			}

			//$result_cnt = $DB->fetch_assoc(" SELECT count(DISTINCT mt_app_token) as cnt $push_qry ");
			$result_cnt = $DB->fetch_assoc(" SELECT count(DISTINCT mt_fcm) as cnt $push_qry ");
			$result 	= $DB->select_query(" SELECT mt_id, mt_fcm, mt_pushing $push_qry ");

			//------------------------------------------------------------------------------------------

			$total = $result_cnt['cnt'];

			$total_page = ceil($total/1000);//총 페이지

			$ii = 0;

			$data = array();

			foreach ($result as $row) {

				$data[$ii] = $row;

				$ii++;

			}

			//------------------------------------------------------------------------------------------

			$i = 0;

			for($p=1; $p<=$total_page; $p++){

				$tokens = array();



				if($total-(1000 * $p) > 0){//다음페이지가 있는지(남은게 천개보다 큰지) 확인.

					$max = 1000;

				}else{

					$max = $total-(1000 * ($p-1));//$total;

				}



				$send_to_id 	= ',';

				for($j=0; $j<$max; $j++){

					if ($data[$i]['mt_pushing']==='Y') {

						$tokens[] = $data[$i]["mt_fcm"];

					}

					$send_to_id .= $data[$i]["mt_id"];

					$send_to_id .= ',';



					$i++;

				}



                $sound = "default";

                $channel_id = "";


				$insert_check = 0;

				if ($push_board==='notice') { $send_to_id = ''; }

				if ($push_type==="admin" || $push_type==="chat") {

                    $_last_idx = $ref_idx;

				} else {

					$send_msg = addslashes($send_msg);

                    $send_msg = str_replace('&gt;','>',$send_msg);

                    $send_msg = str_replace('&lt;','<',$send_msg);

                    $send_msg = str_replace('&nbsp;',' ',$send_msg);

                    $send_msg = str_replace('<br />',"\n",$send_msg);



                    unset($arr_query);

                    $arr_query = array(
						'pst_table'=> $ref_table,

                        'pst_type' => $pst_type,

                        'pst_index' => $ref_idx,

                       // 'pst_category' => $pst_category,

                        //'pst_level' => $pst_level,

                        'send_to' => $send_to_id,

                        'pst_title' => $send_title,

                        'pst_shot_memo' => $shot_title,

                        'pst_content' => $send_msg,

                        'pst_wdate' => date('Y-m-d H:i:s'),

                    );

                    $DB->insert_query('pushnotification_t', $arr_query);
                    $_last_idx = $DB->insert_id();

                    if ($_last_idx) {

                        $insert_check = 1;

					}

				}
                
				if ($tokens) {

                    $send_msg = cut_str($send_msg, 0, 100, '..');

					$send_msg = stripslashes($send_msg);

                    $send_msg = str_replace('&gt;','>',$send_msg);

                    $send_msg = str_replace('&lt;','<',$send_msg);

                    $send_msg = str_replace('&nbsp;',' ',$send_msg);

                    $send_msg = str_replace('<br />',"\n",$send_msg);



					$send_to_id_arr = explode(',', $send_to_id);

					$send_to_id_arr = array_values(array_filter(array_map('trim',$send_to_id_arr)));



					$message = array("title" => $send_title, "message" => $send_msg, "push_type" => $push_type, "push_type2" => $pst_type, "ref_idx" => $ref_idx

					, "ref_param" => $ref_param

					, "push_id" => $_last_idx, "push_link" => $push_link

					, "send_to" => $send_to_id_arr

					, "body" => $send_msg, "icon" => "", "sound" => $sound, "android_channel_id" => $channel_id);

					if ($message01) { $message = array_merge($message, $message01); }
                                        
                    $message_status = send_notification($tokens, $send_title, $send_msg);                    
					//$message_status = send_notification($tokens, $message, SECRETKEY);

					//if ($message_status && $insert_check>0) {
                    if ($message_status) {

						unset($arr_query);

						$arr_query = array(

							'message_status' => $message_status,
                            'send_to' => $send_to_id,
                            'pst_sdate' => 'now()'

						);

						$DB->update_query('pushnotification_t', $arr_query, "idx = '".$_last_idx."'");

						foreach($send_to_id_arr as $row){
							$plt_set = array(
								'plt_title'=>$send_title,
								'plt_shot_memo'=>'',
								'plt_content'=>$send_msg,
								'plt_type'=>$push_type,
								'plt_table'=>$ref_table,
								'plt_index'=>$ref_idx,
								'mt_id'=>$row,
								'plt_wdate'=>'now()'
								);
							$DB->insert_query("pushnotification_log_t", $plt_set);
						}

                    }
				}

				sleep(1);//천개 보내고 휴식

			}



			$retArr = array();

			$retArr['send_to'] = $send_to;

			$retArr['tokens'] = $tokens;

			$retArr['message'] = $message;

			$retArr['response'] = $message_status;

			$retArr['wr'] = $wr;

			$retArr['wr_data'] = $wr_data;



			return $retArr;

		} else {

			$retArr = array();

			$retArr['send_to'] = $send_to;

			$retArr['message'] = "";

			$retArr['response'] = "";



			return $retArr;

		}

	}

	function thumnail_width2($file, $save_filename, $save_path, $max_width) {
		/**
		 * @description 스마트폰, 카메라 등에서 사진 jpg 저장시에
		회전값이 들어갈 수 있다. 그럴 경우 변환해 주는 소스 이다.
		 */
		if(!function_exists('exif_read_data')){
			echo 'not defined exif_read_data. requires exif module';
			exit;
		}
		if(!function_exists('imagecreatefromjpeg')){
			echo 'not defined imagecreatefromjpeg.';
			exit;
		}
		if(!function_exists('imagerotate')){
			echo 'not defined imagerotate.';
			exit;
		}

		$source_path = $file;
		$temp_ext = strrchr($source_path, ".");
		$temp_ext = strtolower($temp_ext);// 확장자
		if(preg_match('/(jpg|jpeg)$/i',$temp_ext)
			&& function_exists('exif_read_data')
			&& function_exists('imagecreatefromjpeg')
			&& function_exists('imagerotate')
		)
		{
			$exif = exif_read_data($source_path);//<get exif data. jpeg 나 tiff 의 경우에만 갖고 있음
			$source = imagecreatefromjpeg($source_path);//<임시 리소스 생성

			//값에 따라 회전
			switch($exif['Orientation']){
				case 8 : $source = imagerotate($source,90,0); break;
				case 3 : $source = imagerotate($source,180,0); break;
				case 6 : $source = imagerotate($source,-90,0); break;
			}

			//결과 처리
			header('Content-Type: image/jpeg');
			@ImageDestroy($source);
			@ImageDestroy($source);
		} else {
			$img_info = getimagesize($file);
			if($img_info[2] == 1) {
				$src_img = ImageCreateFromGif($file);
			} else if($img_info[2] == 2) {
				$src_img = ImageCreateFromJPEG($file);
			} else if($img_info[2] == 3) {
				$src_img = ImageCreateFromPNG($file);
			} else {
				return 0;
			}

			$img_width = $img_info[0];
			$img_height = $img_info[1];

			$dst_width = $max_width;
			$dst_height = round($dst_width*($img_height/$img_width));

			$srcx = 0;
			$srcy = 0;

			if($img_info[2] == 1) {
				$dst_img = imagecreate($dst_width, $dst_height);
			} else {
				$dst_img = imagecreatetruecolor($dst_width, $dst_height);
			}

			ImageCopyResampled($dst_img, $src_img, $srcx, $srcy, 0, 0, $dst_width, $dst_height, ImageSX($src_img),ImageSY($src_img));

			if($img_info[2] == 1) {
				ImageInterlace($dst_img);
				ImageGif($dst_img, $save_path.$save_filename);
			} else if($img_info[2] == 2) {
				ImageInterlace($dst_img);
				ImageJPEG($dst_img, $save_path.$save_filename);
			} else if($img_info[2] == 3) {
				ImagePNG($dst_img, $save_path.$save_filename);
			}
			@ImageDestroy($dst_img);
			@ImageDestroy($src_img);
		}
	}


?>
