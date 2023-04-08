<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	if($_POST['act']=='main') {

        unset($arr_query_url);
        $arr_query_url = array();
        for($q=1;$q<=6;$q++) {
            $arr_query_url['mvt_url'.$q] = $_POST['mvt_url'.$q];
        }
        if($arr_query_url) {
            $DB->update_query('main_visual_t', $arr_query_url, "idx = '1'");
        }

		unset($arr_query_img);
		$arr_query_img = array();
		for($q=1;$q<=6;$q++) {
			$temp_img_txt = "mvt_img".$q;
			$temp_img_on_txt = "mvt_img".$q."_on";
			$temp_img_temp_on_txt = "mvt_img".$q."_temp_on";
			$temp_img_del_txt = "mvt_img".$q."_del";

			if($_FILES[$temp_img_txt]['name']) {
				$mvt_image = $_FILES[$temp_img_txt]['tmp_name'];
				$mvt_image_name = $_FILES[$temp_img_txt]['name'];
				$mvt_image_size = $_FILES[$temp_img_txt]['size'];
				$mvt_image_type = $_FILES[$temp_img_txt]['type'];

				if($mvt_image_name!="") {
					@unlink($ct_img_dir_a."/".$_POST[$temp_img_on_txt]);
					$_POST[$temp_img_on_txt] = "mvt_img_".$q.".".get_file_ext($mvt_image_name);
					upload_file($mvt_image, $_POST[$temp_img_on_txt], $ct_img_dir_a."/");
					//thumnail($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
					//scale_image_fit($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
					thumnail_width($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000");
				}
			} else {
				if($_POST[$temp_img_del_txt]) {
					unlink($ct_img_dir_a."/".$_POST[$temp_img_del_txt]);
				}
			}

			$arr_query_img['mvt_img'.$q] = $_POST['mvt_img'.$q.'_on'];
		}

		if($arr_query_img) {
			$DB->update_query('main_visual_t', $arr_query_img, "idx = '1'");
		}

        $DB->update_query('main_visual_t', array("mvt_udate" => "now()"), "idx = '1'");

		p_alert('처리되었습니다.');
	} else if($_POST['act']=='movies') {

        unset($arr_query_url);
        $arr_query_url = array();
        for($q=1;$q<=3;$q++) {
            $arr_query_url['mvt_movies_url'.$q] = $_POST['mvt_movies_url'.$q];
        }
        if($arr_query_url) {
            $DB->update_query('main_visual_t', $arr_query_url, "idx = '1'");
        }

        unset($arr_query_img);
        $arr_query_img = array();
        for($q=1;$q<=3;$q++) {
            $temp_img_txt = "mvt_movies_img".$q;
            $temp_img_on_txt = "mvt_movies_img".$q."_on";
            $temp_img_temp_on_txt = "mvt_movies_img".$q."_temp_on";
            $temp_img_del_txt = "mvt_movies_img".$q."_del";

            if($_FILES[$temp_img_txt]['name']) {
                $mvt_image = $_FILES[$temp_img_txt]['tmp_name'];
                $mvt_image_name = $_FILES[$temp_img_txt]['name'];
                $mvt_image_size = $_FILES[$temp_img_txt]['size'];
                $mvt_image_type = $_FILES[$temp_img_txt]['type'];

                if($mvt_image_name!="") {
                    @unlink($ct_img_dir_a."/".$_POST[$temp_img_on_txt]);
                    $_POST[$temp_img_on_txt] = "mvt_movies_img_".$q.".".get_file_ext($mvt_image_name);
                    upload_file($mvt_image, $_POST[$temp_img_on_txt], $ct_img_dir_a."/");
                    //thumnail($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
                    //scale_image_fit($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
                    thumnail_width($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000");
                }
            } else {
                if($_POST[$temp_img_del_txt]) {
                    unlink($ct_img_dir_a."/".$_POST[$temp_img_del_txt]);
                }
            }
            $arr_query_img['mvt_movies_img'.$q] = $_POST['mvt_movies_img'.$q.'_on'];
        }

        if($arr_query_img) {
            $DB->update_query('main_visual_t', $arr_query_img, "idx = '1'");
        }

        $DB->update_query('main_visual_t', array("mvt_udate" => "now()"), "idx = '1'");
        p_alert('처리되었습니다.');
    } else if($_POST['act']=='main_img_delete') {
        $num = substr($_POST['column'], -1, 1);
        unset($arr_query);
        $arr_query = array();
        $arr_query[$_POST['column']] = null;
        @unlink($ct_img_dir_a . "/" . $_POST['img']);

        if($_POST['type'] == "main") {
            $arr_query['mvt_url'.$num] = null;
        } else {
            $arr_query['mvt_movies_url'.$num] = null;
        }

        $DB->update_query("main_visual_t", $arr_query, " idx = 1");

        echo json_encode(array('result' => '_ok', 'msg' => "삭제되었습니다."));
    } else if($_POST['act']=='content_view') {
        $n_limit = 5;
        $pg = $_GET['pg'];
        $_get_txt = "modal?pg=";

        $category1 = $DB->select_query("select idx, pc_name from product_category_t where pc_depth = 0");
        $category2 = $DB->select_query("select idx, pc_name from product_category_t  where pc_depth = 1");
        ?>
        <script>$('#modal-product-size').css('max-width', '900px');</script>
        <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">HOT한 아이템 추가</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form method="post" name="frm_form" id="frm_form" action="./product_random_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm">
                <input type="hidden" name="act" id="act" value="get_list" />
                <table class="table">
                    <tbody>
                    <tr>
                        <td class="text-center" style="width: 150px;background-color: #fafafa;">
                            카테고리
                        </td>
                        <td>
                            <div class="custom-control custom-check custom-control-inline">
                                <select class="form-control form-control-sm" name="pct_idx" id="pct_idx" onchange="get_pct_m(this)">
                                    <option value="">대분류 선택</option>
                                    <?php foreach($category1 as $val):?>
                                        <option value="<?=$val['idx']?>"><?=$val['pc_name']?></option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                            <div class="custom-control custom-check custom-control-inline">
                                <select class="form-control form-control-sm" name="pct_m_idx" id="pct_m_idx">
                                    <option value="">중분류 선택</option>
                                </select>
                            </div>
                            <input type="button" class="btn btn-primary" value="검색" onclick="p_search()">
                        </td>
                    </tr>
                    <tr>
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="text-center" style="width: 400px;">
                                    상품명
                                </th>
                                <th class="text-center">
                                    판매가
                                </th>
                                <th class="text-center">
                                    선택
                                </th>
                            </tr>
                            </thead>
                            <tbody id="list">
                            </tbody>
                        </table>
                        <nav id="page" class="m-3" aria-label="Page navigation">
                        </nav>
                    </tr>
                    </tbody>
                </table>
            </form>
        </div>
        <script>
            function get_list(url, page) {
                if(!page){
                    page = 1;
                }
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: './product_random_update.php',
                    data: {
                        act: "get_list",
                        url: url,
                        page: page
                    },
                    success: function (data) {
                        $("#list").html(data.html);
                        $("#page").html(data.page);
                    },
                    error: function (request, status, error) {
                        console.log('code: '+request.status+"\n"+'message: '+request.responseText+"\n"+'error: '+error);
                    }
                });
            }

            function p_search() {
                if(!page){
                    page = 1;
                }
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: './product_random_update.php',
                    data: {
                        act: "get_list",
                        type: "search",
                        pct_idx: $("#pct_idx").val(),
                        pct_m_idx: $("#pct_m_idx").val(),
                    },
                    success: function (data) {
                        $("#list").html(data.html);
                        $("#page").html(data.page);
                    },
                    error: function (request, status, error) {
                        console.log('code: '+request.status+"\n"+'message: '+request.responseText+"\n"+'error: '+error);
                    }
                });
            }

            function add_product(idx) {
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: './main_update.php',
                    data: {
                        act: "add_product",
                        pt_idx: idx
                    },
                    success: function (data) {
                        if(data['result'] == "_ok") {
                            alert(data['msg']);
                            location.reload();
                        } else {
                            alert(data['msg']);
                        }
                    },
                    error: function (request, status, error) {
                        // console.log('code: '+request.status+"\n"+'message: '+request.responseText+"\n"+'error: '+error);
                    }
                });
            }
            function get_pct_m(e) {
                $.ajax({
                    type: 'post',
                    url: './product_update.php',
                    dataType: 'json',
                    data: {act: 'get_pct_m', pc_m_idx: $("#"+e.id+" option:selected").val()},
                    success: function (d, s) {
                        if(d['result'] == "_ok") {
                            var html = "";
                            html += '<option value="" hidden="">중분류 선택</option>';
                            html += d['data'];
                            $("#pct_m_idx").html(html);
                        }
                    },
                    cache: false
                });
            }
        </script>
<?
    } else if($_POST['act'] == 'add_product') {
        $count = $DB->count_query("select * from product_t where pt_best_chk = 'Y' and pt_show = 'Y' and pt_sale_now = 'Y'");
        if($count > 5) {
            echo json_encode(array('result' => 'false', 'msg' => "HOT한 아이템은 최대 6개까지 등록할 수 있습니다."));
            exit;
        } else {
            $chk = $DB->fetch_assoc("select pt_best_chk from product_t where idx = ".$_POST['pt_idx']);
            if($chk['pt_best_chk'] == "Y") {
                echo json_encode(array('result' => 'false', 'msg' => "이미 추가된 아이템입니다."));
            } else {
                $DB->update_query("product_t", array("pt_best_chk" => "Y"), "idx = ".$_POST['pt_idx']);
                echo json_encode(array('result' => '_ok', 'msg' => "추가되었습니다."));
            }
        }
    } else if($_POST['act'] == 'delete_product') {
        $DB->update_query('product_t', array("pt_best_chk" => "N"), "idx = ".$_POST['pt_idx']);
        echo json_encode(array('result' => '_ok', 'msg'=>'삭제되었습니다.'));
    } else if($_POST['act']=='content_view2') {
        $n_limit = 5;

        $category1 = $DB->select_query("select idx, pc_name from product_category_t where pc_depth = 0");
        $category2 = $DB->select_query("select idx, pc_name from product_category_t  where pc_depth = 1");
        ?>
        <script>$('#modal-product-size').css('max-width', '900px');</script>
        <div class="modal-header">
            <h5 class="modal-title" id="staticBackdropLabel">NEW 룰루팝 추가</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form method="post" name="frm_form" id="frm_form" action="./product_random_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm">
                <input type="hidden" name="act" id="act" value="get_list2" />
                <input type="hidden" name="seq" id="seq" value="<?=$_POST['seq']?>" />
                <table class="table">
                    <tbody>
                    <tr>
                        <td class="text-center" style="width: 150px;background-color: #fafafa;">
                            카테고리
                        </td>
                        <td>
                            <div class="custom-control custom-check custom-control-inline">
                                <select class="form-control form-control-sm" name="pct_idx" id="pct_idx" onchange="get_pct_m(this)">
                                    <option value="">대분류 선택</option>
                                    <?php foreach($category1 as $val):?>
                                        <option value="<?=$val['idx']?>"><?=$val['pc_name']?></option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                            <div class="custom-control custom-check custom-control-inline">
                                <select class="form-control form-control-sm" name="pct_m_idx" id="pct_m_idx">
                                    <option value="">중분류 선택</option>
<!--                                    --><?php //foreach($category2 as $val):?>
<!--                                        <option value="--><?//=$val['idx']?><!--">--><?//=$val['pc_name']?><!--</option>-->
<!--                                    --><?php //endforeach;?>
                                </select>
                            </div>
                            <input type="button" class="btn btn-primary" value="검색" onclick="p_search()">
                        </td>
                    </tr>
                    <tr>
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="text-center" style="width: 400px;">
                                    상품명
                                </th>
                                <th class="text-center">
                                    판매가
                                </th>
                                <th class="text-center">
                                    선택
                                </th>
                            </tr>
                            </thead>
                            <tbody id="list">
                            </tbody>
                        </table>
                        <nav id="page" class="m-3" aria-label="Page navigation">
                        </nav>
                    </tr>
                    </tbody>
                </table>
            </form>
        </div>
        <script>
            function get_list2(url, page) {
                if(!page){
                    page = 1;
                }
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: './main_update.php',
                    data: {
                        act: "get_list2",
                        url: url,
                        page: page,
                        seq: $("#seq").val(),
                    },
                    success: function (data) {
                        $("#list").html(data.html);
                        $("#page").html(data.page);
                    },
                    error: function (request, status, error) {
                        console.log('code: '+request.status+"\n"+'message: '+request.responseText+"\n"+'error: '+error);
                    }
                });
            }

            function p_search() {
                if(!page){
                    page = 1;
                }
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: './main_update.php',
                    data: {
                        act: "get_list2",
                        type: "search",
                        pct_idx: $("#pct_idx").val(),
                        pct_m_idx: $("#pct_m_idx").val(),
                        seq: $("#seq").val(),
                    },
                    success: function (data) {
                        $("#list").html(data.html);
                        $("#page").html(data.page);
                    },
                    error: function (request, status, error) {
                        console.log('code: '+request.status+"\n"+'message: '+request.responseText+"\n"+'error: '+error);
                    }
                });
            }

            function add_product_new(idx, seq) {
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: './main_update.php',
                    data: {
                        act: "add_product_new",
                        pt_idx: idx
                    },
                    success: function (data) {
                        if(data['result'] == "_ok") {
                            $("#pt_title"+seq).val(data['data']['pt_title']);
                            $("#pt_idx"+seq).val(data['data']['idx']);
                            $('#product_modal').modal('hide');
                        } else {
                            alert(data['msg']);
                        }
                    },
                    error: function (request, status, error) {
                        // console.log('code: '+request.status+"\n"+'message: '+request.responseText+"\n"+'error: '+error);
                    }
                });
            }
            function get_pct_m(e) {
                $.ajax({
                    type: 'post',
                    url: './product_update.php',
                    dataType: 'json',
                    data: {act: 'get_pct_m', pc_m_idx: $("#"+e.id+" option:selected").val()},
                    success: function (d, s) {
                        if(d['result'] == "_ok") {
                            var html = "";
                            html += '<option value="" hidden="">중분류 선택</option>';
                            html += d['data'];
                            $("#pct_m_idx").html(html);
                        }
                    },
                    cache: false
                });
            }
        </script>
<?
    } else if($_POST['act'] == 'add_product_new') {
        $count = $DB->count_query("select * from product_t where pt_new_chk = 'Y' and pt_show = 'Y' and (pt_sale_now = 'Y' or pt_sale_now = 'N')");
        if($count > 6) {
            echo json_encode(array('result' => 'false', 'msg' => "NEW 룰루팝은 최대 6개까지 등록할 수 있습니다."));
            exit;
        } else {
            $chk = $DB->fetch_assoc("select pt_new_chk from product_t where idx = ".$_POST['pt_idx']);
            if($chk['pt_new_chk'] == "Y") {
                echo json_encode(array('result' => 'false', 'msg' => "이미 추가된 아이템입니다."));
            } else {
                $row = $DB->fetch_assoc("select * from product_t where idx = ".$_POST['pt_idx']);
                echo json_encode(array('result' => '_ok', 'data' => $row));
            }
        }
    } else if($_POST['act']=='get_list2') {
        $where = " where pt_show = 'Y' and (pt_sale_now = 'Y' or pt_sale_now = 'N') ";
        if($_POST['type'] == "search") {
            $where .= " and";
            if($_POST['pct_idx'] && !$_POST['pct_m_idx']) {
                $where_query = $where." pct_idx = ".$_POST['pct_idx'];
            } if(!$_POST['pct_idx'] && $_POST['pct_m_idx']) {
                $where_query = $where." pct_m_idx = ".$_POST['pct_m_idx'];
            } if($_POST['pct_idx'] && $_POST['pct_m_idx']) {
                $where_query = $where." pct_idx = ".$_POST['pct_idx']." and pct_m_idx = ".$_POST['pct_m_idx'];
            } if(!$_POST['pct_idx']) {
                $where_query = "";
            }
        } else {
            $where_query = "";
        }
        $n_limit_num = 5;
        $n_limit = $n_limit_num;
        $pg = $_POST['page'];
        $result['pages'] = $_POST['page'];

        unset($list);
        $query = "select *, idx as pt_idx from product_t a1 ";
        $query_count = "select count(*) from product_t a1 ";

        $row_cnt = $DB->fetch_query($query_count.$where_query);
        $count_query = $row_cnt[0];
        $counts = $count_query;
        $n_page = ceil($count_query / $n_limit_num);
        if($pg=="") $pg = 1;
        $n_from = ($pg - 1) * $n_limit;
        $counts = $counts - (($pg - 1) * $n_limit_num);

        unset($list);
        $sql_query = $query.$where_query." order by a1.idx desc limit ".$n_from.", ".$n_limit;
        $list = $DB->select_query($sql_query);
        $result['sql'] = $sql_query;
        $result['n_page'] = $n_page;

        if($list) {
            foreach($list as $row) {
                $result['html'] .= '<tr>';
                $result['html'] .= '<td class="text-center">';
                $result['html'] .= $row['pt_title'];
                $result['html'] .= '</td>';
                $result['html'] .= '<td class="text-center">';
                $result['html'] .= number_format($row['pt_price'])."원";
                $result['html'] .= '</td>';
                $result['html'] .= '<td class="text-center">';
                $result['html'] .= '   <input type="button" class="btn btn-outline-primary btn-sm" value="추가" onclick="add_product_new('.$row['pt_idx'].', '.$_POST['seq'].')">';
                $result['html'] .= '</td>';
                $result['html'] .= '</tr>';
            }
        } else {
            $result['html'] ="<tr><td colspan='3' class=\"text-center\"><b>자료가 없습니다.</b></td></tr>";
        }

        if($n_page>1) {
            $result['page'] = pageing_list_ajax($pg, $n_page, $_SERVER['PHP_SELF'],"get_list2");
        }else{
            $result['page'] = "";
        }

        $result['result'] = "ok";

        echo json_encode($result);
    } else if($_POST['act'] == "new") {
        for($i=1; $i<=6; $i++) {
            if ($_POST['pt_idx' . $i]) {
                $date = $_POST['YEAR' . $i] . "-" . $_POST['MONTH' . $i] . "-" . $_POST['DAY' . $i];
                $arr = array(
                    "pt_new_chk" => "Y",
                    "pt_new_date" => $date,
                    "pt_new_url" => $_POST['pt_new_url' . $i],
                );
                $DB->update_query("product_t", $arr, "idx = " . $_POST['pt_idx' . $i]);

                unset($arr_query_img);
                $arr_query_img = array();
                $temp_img_txt = "pt_new_img" . $i;
                $temp_img_on_txt = "pt_new_img" . $i . "_on";
                $temp_img_temp_on_txt = "pt_new_img" . $i . "_temp_on";
                $temp_img_del_txt = "pt_new_img" . $i . "_del";

                if ($_FILES[$temp_img_txt]['name']) {
                    $pt_new_image = $_FILES[$temp_img_txt]['tmp_name'];
                    $pt_new_image_name = $_FILES[$temp_img_txt]['name'];
                    $pt_new_image_size = $_FILES[$temp_img_txt]['size'];
                    $pt_new_image_type = $_FILES[$temp_img_txt]['type'];

                    if ($pt_new_image_name != "") {
                        @unlink($ct_img_dir_a . "/" . $_POST[$temp_img_on_txt]);
                        $_POST[$temp_img_on_txt] = "pt_new_img_" . $i . "." . get_file_ext($pt_new_image_name);
                        upload_file($pt_new_image, $_POST[$temp_img_on_txt], $ct_img_dir_a . "/");
                        //thumnail($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
                        //scale_image_fit($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
                        thumnail_width($ct_img_dir_a . "/" . $_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a . "/", "1000");
                    }
                } else {
                    if ($_POST[$temp_img_del_txt]) {
                        unlink($ct_img_dir_a . "/" . $_POST[$temp_img_del_txt]);
                    }
                }

                $arr_query_img['pt_new_img'] = $_POST['pt_new_img' . $i . '_on'];

                if ($arr_query_img) {
                    $DB->update_query('product_t', $arr_query_img, "idx = " . $_POST['pt_idx' . $i]);
                }
            }
        }
        p_alert("저장되었습니다.");
    } else if($_POST['act']=='new_img_delete') {
        unset($arr_query);

        @unlink($ct_img_dir_a . "/" . $_POST['img']);

        $arr_query = array(
            "pt_new_chk" => "N",
            "pt_new_img" => null,
            "pt_new_date" => null,
            "pt_new_url" => null,
        );

        $DB->update_query("product_t", $arr_query, " idx = ".$_POST['idx']);

        echo json_encode(array('result' => '_ok', 'msg' => "삭제되었습니다."));
    }

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>