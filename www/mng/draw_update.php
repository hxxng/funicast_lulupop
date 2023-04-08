<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

    if($_POST['act'] == "random") {
        unset($arr_query_img);
        $arr_query_img = array();
        for ($q = 1; $q <= 2; $q++) {
            $temp_img_txt = "dt_effect" . $q;
            $temp_img_on_txt = "dt_effect" . $q . "_on";
            $temp_img_temp_on_txt = "dt_effect" . $q . "_temp_on";
            $temp_img_del_txt = "dt_effect" . $q . "_del";

            if ($_FILES[$temp_img_txt]['name']) {
                $dt_effect = $_FILES[$temp_img_txt]['tmp_name'];
                $dt_effect_name = $_FILES[$temp_img_txt]['name'];
                $dt_effect_size = $_FILES[$temp_img_txt]['size'];
                $dt_effect_type = $_FILES[$temp_img_txt]['type'];

                if ($dt_effect_name != "") {
                    @unlink($ct_img_dir_a . "/" . $_POST[$temp_img_on_txt]);
                    $_POST[$temp_img_on_txt] = "dt_effect_" . $q . "." . get_file_ext($dt_effect_name);
                    upload_file($dt_effect, $_POST[$temp_img_on_txt], $ct_img_dir_a . "/");
                    //thumnail($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
                    //scale_image_fit($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
                    thumnail_width($ct_img_dir_a . "/" . $_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a . "/", "1000");
                }
            } else {
                if ($_POST[$temp_img_del_txt]) {
                    unlink($ct_img_dir_a . "/" . $_POST[$temp_img_del_txt]);
                }
            }
            $DB->update_query('draw_t', array("dt_effect" => $_POST['dt_effect' . $q . '_on'], "dt_type" => 1,"dt_edate" => "now()"), "idx = ".$q);
        }
        p_alert('저장되었습니다.');
    } else if($_POST['act'] == "catalog") {
        for($q=1; $q<=$_POST['count']; $q++) {
            $temp_img_txt = "dt_effect_" . $q;
            $temp_img_on_txt = "dt_effect_" . $q . "_on";
            $temp_img_temp_on_txt = "dt_effect_" . $q . "_temp_on";
            $temp_img_del_txt = "dt_effect_" . $q . "_del";

            if ($_FILES[$temp_img_txt]['name']) {
                $dt_effect = $_FILES[$temp_img_txt]['tmp_name'];
                $dt_effect_name = $_FILES[$temp_img_txt]['name'];
                $dt_effect_size = $_FILES[$temp_img_txt]['size'];
                $dt_effect_type = $_FILES[$temp_img_txt]['type'];

                if ($dt_effect_name != "") {
                    @unlink($ct_img_dir_a . "/" . $_POST[$temp_img_on_txt]);
                    $_POST[$temp_img_on_txt] = "dt_effect_catalog_" . $_POST['dt_idx_'.$q] . "." . get_file_ext($dt_effect_name);
                    upload_file($dt_effect, $_POST[$temp_img_on_txt], $ct_img_dir_a . "/");
                    //thumnail($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
                    //scale_image_fit($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
                    thumnail_width($ct_img_dir_a . "/" . $_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a . "/", "1000");
                }
            } else {
                if ($_POST[$temp_img_del_txt]) {
                    unlink($ct_img_dir_a . "/" . $_POST[$temp_img_del_txt]);
                }
            }

            if($_POST['dt_idx_'.$q]) {
                $DB->update_query('draw_t', array("dt_effect" => $_POST['dt_effect_' . $q . '_on'], "dt_type" => 2,"dt_edate" => "now()"), "idx = ".$_POST['dt_idx_'.$q]);
            } else {
                $DB->insert_query('draw_t', array("dt_effect" => $_POST['dt_effect_' . $q . '_on']));
                $_last_idx = $DB->insert_id();
                $DB->update_query('draw_t', array("dt_type" => 2,"dt_edate" => "now()"), "idx = ".$_last_idx);
            }
        }
        p_alert('저장되었습니다.');
    }
	include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>