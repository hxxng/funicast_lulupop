<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	if($_POST['act']=='input' || $_POST['act']=='update') {
		unset($arr_query);
		$arr_query = array(
            "et_status" => $_POST['et_status'],
            "et_title" => $_POST['et_title'],
			"et_sdate" => $_POST['et_sdate'],
			"et_edate" => $_POST['et_edate'],
			"et_content" => $_POST['et_content'],
            "et_wdate" => "now()",
		);
        if($_POST['et_idx']) {
            $query_ptc = "select * from event_t where idx = '".$_POST['et_idx']."'";
            $row_etc = $DB->fetch_query($query_ptc);
        }

		if($_POST['et_idx']=='') {
			$DB->insert_query('event_t', $arr_query);
			$_last_pt_idx = $DB->insert_id();
		} else {
			$where_query = "idx = '".$row_etc['idx']."'";
			unset($arr_query['et_wdate']);
			$arr_query['et_udate'] = date('Y-m-d H:i:s');
			$DB->update_query('event_t', $arr_query, $where_query);
			$_last_pt_idx = $row_etc['idx'];
		}

		unset($arr_query_img);
		$arr_query_img = array();
        $temp_img_txt = "et_img";
        $temp_img_on_txt = "et_img_on";
        $temp_img_temp_on_txt = "et_img_temp_on";
        $temp_img_del_txt = "et_img_del";

        if($_FILES[$temp_img_txt]['name']) {
            $et_image = $_FILES[$temp_img_txt]['tmp_name'];
            $et_image_name = $_FILES[$temp_img_txt]['name'];
            $et_image_size = $_FILES[$temp_img_txt]['size'];
            $et_image_type = $_FILES[$temp_img_txt]['type'];

            if($et_image_name!="") {
                @unlink($ct_img_dir_a."/".$_POST[$temp_img_on_txt]);
                $_POST[$temp_img_on_txt] = "et_image_".$_last_pt_idx.".".get_file_ext($et_image_name);
                upload_file($et_image, $_POST[$temp_img_on_txt], $ct_img_dir_a."/");
                //thumnail($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
                //scale_image_fit($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
                thumnail_width($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000");
            }
        } else {
            if($_POST[$temp_img_del_txt]) {
                unlink($ct_img_dir_a."/".$_POST[$temp_img_del_txt]);
            }
        }
        $arr_query_img['et_img'] = $_POST['et_img_on'];

		if($arr_query_img) {
			$where_query = "idx = '".$_last_pt_idx."'";

			$DB->update_query('event_t', $arr_query_img, $where_query);
		}

		p_alert('처리되었습니다.');
    } else if($_POST['act']=='select_delete') {
        if(count($_POST['et_idx'])>0) {
            for ($i = 0; $i < count($_POST['et_idx']); $i++) {

                $query_ptc = "select * from event_t where idx = '" . $_POST['et_idx'][$i] . "'";
                $row_ptc = $DB->fetch_query($query_ptc);
                if(is_file($ct_img_dir_a . "/" . $row_ptc['et_img'])){
                    echo $ct_img_dir_a . "/" . $row_ptc['et_img'];
                    @unlink($ct_img_dir_a . "/" . $row_ptc['et_img']);
                }

                preg_match_all("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i", $row_ptc['et_content'], $pt_content_img);

                foreach ($pt_content_img[1] as $key => $val) {
                    $val = str_replace($ct_img_url, '', $val);					
                    if (is_file($ct_img_dir_a . "/" . $val)) {
                        @unlink($ct_img_dir_a . "/" . $val);
                    }
                }

                $DB->del_query('event_t', " idx = '" . $_POST['et_idx'][$i] . "'");
            }
            echo json_encode(array('result' => '_ok', 'msg'=>'삭제 되었습니다.'));
        }
        else{
            echo json_encode(array('result' => 'false', 'msg'=>'삭제 실패'));
        }
    }

	include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>