<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	if($_POST['act']=='get_list') {
        for ($i = 0; $i < $_POST['count']; $i++) {
            unset($arr_query_url);
            $arr_query_url = array();
            $arr_query_url['ct_orderby'] = $_POST['ct_orderby' . $i];
            $arr_query_url['ct_title'] = $_POST['ct_title' . $i];
            $arr_query_url['ct_effect'] = $_POST['ct_effect' . $i];
            $arr_query_url['ct_udate'] = "now()";
            if ($_POST['ct_idx' . $i]) {
                $DB->update_query('catalog_t', $arr_query_url, "idx = " . $_POST['ct_idx' . $i]);
                $idx = $_POST['ct_idx' . $i];
            } else {
                $DB->insert_query('catalog_t', $arr_query_url);
                $_last_idx = $DB->insert_id();
                $idx = $_last_idx;
            }

            unset($arr_query_img);
            $arr_query_img = array();

            for($q=1;$q<=2;$q++) {
                $temp_img_txt = "catalog_img".$q."_". $i;
                $temp_img_on_txt = "catalog_img".$q."_" . $i . "_on";
                $temp_img_temp_on_txt = "catalog_img".$q."_" . $i . "_temp_on";
                $temp_img_del_txt = "catalog_img".$q."_" . $i . "_del";

                if ($_FILES[$temp_img_txt]['name']) {
                    $ct_image = $_FILES[$temp_img_txt]['tmp_name'];
                    $ct_image_name = $_FILES[$temp_img_txt]['name'];
                    $ct_image_size = $_FILES[$temp_img_txt]['size'];
                    $ct_image_type = $_FILES[$temp_img_txt]['type'];


                    if ($ct_image_name != "") {
                        @unlink($ct_img_dir_a . "/" . $_POST[$temp_img_on_txt]);
                        $_POST[$temp_img_on_txt] = "catalog_img".$q."_". $idx . "." . get_file_ext($ct_image_name);
                        upload_file($ct_image, $_POST[$temp_img_on_txt], $ct_img_dir_a . "/");
                        //thumnail($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
                        //scale_image_fit($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
                        thumnail_width($ct_img_dir_a . "/" . $_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a . "/", "1000");
                    }
                } else {
                    if ($_POST[$temp_img_del_txt]) {
                        unlink($ct_img_dir_a . "/" . $_POST[$temp_img_del_txt]);
                    }
                }
                $arr_query_img['ct_img'.$q] = $_POST['catalog_img'.$q."_" . $i . '_on'];
            }

            if ($arr_query_img) {
                if ($_POST['ct_idx' . $i]) {
                    $DB->update_query('catalog_t', $arr_query_img, "idx = " . $_POST['ct_idx' . $i]);
                } else {
                    $DB->update_query('catalog_t', $arr_query_img, "idx = " . $_last_idx);
                }
            }
        }
		p_alert('저장되었습니다.');
	}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>