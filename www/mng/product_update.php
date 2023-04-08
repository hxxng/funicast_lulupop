<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	if($_POST['act']=='select_product') {
		if($_POST['cc_c']=='1') {
			?>
			<input type="text" name="serach_product_txt" id="serach_product_txt" value="" onkeyup="f_search_product(this.value);" autocomplete="off" class="form-control form-control-sm" placeholder="검색어를 입력바랍니다." />
			<div class="pt-2" id="serach_product_box"></div>
			<?
					} else {
			?>
			<div class="cate-drill-down row no-gutters">
				<div class="col-sm-12">
					<ul class="list-group" id="product_selectecd0">
						<?
							unset($list);
							$query = "select * from product_t where pt_show = 'Y' and pt_sale_now = 'Y' order by idx desc";
							$list = $DB->select_query($query);
			
							if($list) {
								foreach($list as $row) {									
						?>
						<li class="list-group-flush d-flex justify-content-between align-items-center product_category_list_group" onclick="f_product_selected('<?=$row['idx']?>');">
							<span class="pl-2 pb-2 category_selected" id="product_selected_<?=$row['idx']?>"><?=$row['pt_title']?> / 상품 번호 : <?php echo $row['idx'];?></span>
						</li>
						<?
								}
							}
						?>
					</ul>
				</div>				
			</div>
			<?
					}
	} else if($_POST['act']=='search_product') {
		if($_POST['stxt']) {
			unset($list);
			$query = "select * from product_t where instr(pt_title, '".$_POST['stxt']."') order by idx desc";
			$list = $DB->select_query($query);
			$arr_data = array();

			if($list) {
				foreach($list as $row) {
					$ca_name_breadcrumb_t = str_replace($_POST['stxt'], '<b class="text-info">'.$_POST['stxt'].'</b>', $row['pt_title']);
?>
		<div class="list-group">
			<li class="list-group-flush d-flex justify-content-between align-items-center product_category_list_group" onclick="f_product_selected('<?=$row['idx']?>');">
				<span class="pl-2 pb-2"><?=$ca_name_breadcrumb_t?> / 상품 번호 : <?php echo $row['idx'];?></span>
			</li>
		</div>
<?
				}
			}
		} else {
?>
		<div class="list-group">
			<li class="list-group-flush d-flex justify-content-between align-items-center product_category_list_group">
				<span class="pl-2 pb-2">검색어를 입력바랍니다.</span>
			</li>
		</div>
<?
		}
	}else if($_POST['act']=='product_selectecd') {
		
		$query = "select * from product_t where idx='".$_POST['idx']."'";
		$row = $DB->fetch_query($query);

		$arr_data = array();

		$arr_data['title'] = "<span class=\"text-info\">선택한 상품 : <b>".$row['pt_title']." / 상품 번호 : ".$row['idx']."</b></span>";
		$arr_data['idx'] = $_POST['idx'];

		echo json_encode($arr_data);
	}else if($_POST['act']=='select_contents') {
		if($_POST['cc_c']=='1') {
			?>
			<input type="text" name="serach_contents_txt" id="serach_contents_txt" value="" onkeyup="f_search_contents(this.value);" autocomplete="off" class="form-control form-control-sm" placeholder="검색어를 입력바랍니다." />
			<div class="pt-2" id="serach_contents_box"></div>
			<?
					} else {
			?>
			<div class="cate-drill-down row no-gutters">
				<div class="col-sm-12">
					<ul class="list-group" id="contents_selectecd0">
						<?
							unset($list);
							$query = "select * from contents_t where ct_type = '2' order by idx desc";
							$list = $DB->select_query($query);
			
							if($list) {
								foreach($list as $row) {									
						?>
						<li class="list-group-flush d-flex justify-content-between align-items-center product_category_list_group" onclick="f_contents_selected('<?=$row['idx']?>');">
							<span class="pl-2 pb-2 category_selected" id="contents_selected_<?=$row['idx']?>"><?=$row['ct_title']?> / 콘텐츠 번호 : <?php echo $row['idx'];?></span>
						</li>
						<?
								}
							}
						?>
					</ul>
				</div>				
			</div>
			<?
					}
	} else if($_POST['act']=='select_category') {
		if($_POST['tt_t']=='1') {
?>
<input type="text" name="serach_category_txt" id="serach_category_txt" value="" onkeyup="f_search_category(this.value);" autocomplete="off" class="form-control form-control-sm" placeholder="검색어를 입력바랍니다." />
<div class="pt-2" id="serach_category_box"></div>
<?
		} else {
?>
<div class="cate-drill-down row no-gutters">
	<div class="col-sm-3">
		<ul class="list-group" id="category_selectecd0">
			<?
				unset($list);
				$query = "select * from category_t where ct_level = '0' order by ct_rank asc, ct_name asc, ct_id asc";
				$list = $DB->select_query($query);

				if($list) {
					foreach($list as $row) {
						$query_chk = "select ct_id from category_t where ct_pid = '".$row['ct_id']."'";
						$row_chk = $DB->fetch_query($query_chk);
			?>
			<li class="list-group-flush d-flex justify-content-between align-items-center product_category_list_group" onclick="<? if($row_chk['ct_id']) { ?>f_category_select('1', '<?=$row['ct_id']?>');<? } else { ?>f_category_selected('<?=$row['ct_level']?>', '<?=$row['ct_id']?>');<? } ?>">
				<span class="pl-2 pb-2 category_selected" id="category_selected_<?=$row['ct_id']?>"><?=$row['ct_name']?></span>
				<?
					if($row_chk['ct_id']) {
				?>
				<i class="mdi mdi-chevron-right pr-2"></i>
				<?
					}
				?>
			</li>
			<?
					}
				}
			?>
		</ul>
	</div>
	<div class="col-sm-3">
		<ul class="list-group" id="category_selectecd1">
			<li class="list-group-flush d-flex justify-content-between align-items-center product_category_list_group">
				<span class="pl-2 pb-2">중분류</span>
			</li>
		</ul>
	</div>
	<div class="col-sm-3">
		<ul class="list-group" id="category_selectecd2">
			<li class="list-group-flush d-flex justify-content-between align-items-center product_category_list_group">
				<span class="pl-2 pb-2">소분류</span>
			</li>
		</ul>
	</div>
	<div class="col-sm-3">
		<ul class="list-group" id="category_selectecd3">
			<li class="list-group-flush d-flex justify-content-between align-items-center product_category_list_group">
				<span class="pl-2 pb-2">세분류</span>
			</li>
		</ul>
	</div>
</div>
<?
		}
	} else if($_POST['act']=='category_rlevel') {
		$ct_rlevel_t = ($_POST['ct_rlevel']+1);

		unset($list);
		$query = "select * from category_t where ct_level = '".$_POST['ct_rlevel']."' and ct_pid = '".$_POST['ct_id']."' order by ct_rank asc, ct_name asc, ct_id asc";
		$list = $DB->select_query($query);

		if($list) {
			foreach($list as $row) {
				$query_chk = "select ct_id from category_t where ct_pid = '".$row['ct_id']."'";
				$row_chk = $DB->fetch_query($query_chk);
?>
<li class="list-group-flush d-flex justify-content-between align-items-center product_category_list_group" onclick="<? if($row_chk['ct_id']) { ?>f_category_select('<?=$ct_rlevel_t?>', '<?=$row['ct_id']?>');<? } else { ?>f_category_selected('<?=$row['ct_level']?>', '<?=$row['ct_id']?>');<? } ?>">
	<span class="pl-2 pb-2 category_selected" id="category_selected_<?=$row['ct_id']?>"><?=$row['ct_name']?></span>
	<?
		if($row_chk['ct_id']) {
	?>
	<i class="mdi mdi-chevron-right pr-2"></i>
	<?
		}
	?>
</li>
<?
			}
		}
	} else if($_POST['act']=='category_selectecd') {
		$ca_name_breadcrumb_t = get_ca_name_breadcrumb($_POST['ct_id']);

		$arr_data = array();

		if($ca_name_breadcrumb_t) {
			$arr_data['ca_name_breadcrumb_t'] = "<span class=\"text-info\">선택한 카테고리 : <b>".$ca_name_breadcrumb_t."</b></span>";
			$arr_data['ct_id'] = $_POST['ct_id'];
		}

		$query_chk = "select ct_id, ct_level from category_t where ct_pid = '".$_POST['ct_id']."'";
		$row_chk = $DB->fetch_query($query_chk);

		if($row_chk['ct_id']) {
			$arr_data['ct_level'] = $row_chk['ct_level'];
		} else {
			$arr_data['ct_level'] = '';
		}

		$query_chk2 = "select ct_pid from category_t where ct_id = '".$_POST['ct_id']."'";
		$row_chk2 = $DB->fetch_query($query_chk2);

		$arr_data['ct_pid'] = $row_chk2['ct_pid'];

		echo json_encode($arr_data);
	} else if($_POST['act']=='search_category') {
		if($_POST['stxt']) {
			unset($list);
			$query = "select * from category_t where instr(ct_name, '".$_POST['stxt']."') order by ct_rank asc, ct_name asc, ct_id asc";
			$list = $DB->select_query($query);

			$arr_data = array();

			if($list) {
				foreach($list as $row) {
					$ca_name_breadcrumb_t = str_replace($_POST['stxt'], '<b class="text-info">'.$_POST['stxt'].'</b>', get_ca_name_breadcrumb($row['ct_id']));
?>
		<div class="list-group">
			<li class="list-group-flush d-flex justify-content-between align-items-center product_category_list_group" onclick="f_category_selected('<?=$row['ct_level']?>', '<?=$row['ct_id']?>');">
				<span class="pl-2 pb-2"><?=$ca_name_breadcrumb_t?></span>
			</li>
		</div>
<?
				}
			}
		} else {
?>
		<div class="list-group">
			<li class="list-group-flush d-flex justify-content-between align-items-center product_category_list_group">
				<span class="pl-2 pb-2">검색어를 입력바랍니다.</span>
			</li>
		</div>
<?
		}
	} else if($_POST['act']=='sel_ct_level') {
		unset($list);
		$query = "select * from category_t where ct_level = '".$_POST['sel_ct_level']."' and ct_pid = '".$_POST['sel_ct_id']."' order by ct_rank asc, ct_name asc, ct_id asc";
		$list = $DB->select_query($query);

		echo "<option value=''>".$arr_sel_ct_level[$_POST['sel_ct_level']]."</option>";
		if($list) {
			foreach($list as $row) {
				echo "<option value='".$row['ct_id']."'>".$row['ct_name']."</option>";
			}
		}
	} else if($_POST['act']=='delete') {
		$DB->del_query('product_afterservice_info_t', " pt_idx = '".$_POST['pt_idx']."'");
		$DB->del_query('product_attribute_t', " pt_idx = '".$_POST['pt_idx']."'");
		$DB->del_query('product_deliveryInfo_t', " pt_idx = '".$_POST['pt_idx']."'");
		$DB->del_query('product_option_t', " pt_idx = '".$_POST['pt_idx']."'");
		$DB->del_query('product_provide_t', " pt_idx = '".$_POST['pt_idx']."'");
		$DB->del_query('product_purchase_benefit_t', " pt_idx = '".$_POST['pt_idx']."'");
		$DB->del_query('product_search_config_t', " pt_idx = '".$_POST['pt_idx']."'");
		$DB->del_query('product_sellercode_t', " pt_idx = '".$_POST['pt_idx']."'");
		$DB->del_query('product_supplement_t', " pt_idx = '".$_POST['pt_idx']."'");

		$query_ptc = "select * from product_t where idx = '".$_POST['pt_idx']."'";
		$row_ptc = $DB->fetch_query($query_ptc);

		for($q=1;$q<=$pt_image_num;$q++) {
			@unlink($ct_img_dir_a."/".$row_ptc['pt_image'.$q]);
		}

		preg_match_all("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i", $row_pt['pt_content'], $pt_content_img);

		foreach($pt_content_img[1] as $key => $val) {
			$val = str_replace($ct_img_url, '', $val);
			if(is_file($ct_img_dir_a."/".$val)) {
				@unlink($ct_img_dir_a."/".$val);
			}
		}

		$DB->del_query('product_t', " idx = '".$_POST['pt_idx']."'");

		echo "Y";
	} else if($_POST['act']=='input' || $_POST['act']=='update') {
		unset($arr_query);
        if($_POST['pt_discount_per'] == 0) {
            $pt_sale_chk = "N";
        } else {
            $pt_sale_chk = "Y";
        }
        if($_POST['pt_show'] == "") {
            $pt_show = "Y";
        } else {
            $pt_show = $_POST['pt_show'];
        }
        if($_POST['pt_sale_now'] == "") {
            $pt_sale_now = "Y";
        } else {
            $pt_sale_now = $_POST['pt_sale_now'];
        }
        if($_POST['pt_random_chk'] == "Y") {
            $pt_random_chk = "Y";
        } else {
            $pt_random_chk = "N";
        }
        if($_POST['pt_best_chk'] == "Y") {
            if($_POST['pt_idx']=='') {
                $count = $DB->count_query("select * from product_t where pt_best_chk = 'Y' and pt_show = 'Y' and pt_sale_now = 'Y'");
                if($count > 5) {
                    p_alert("HOT한 상품은 최대 6개까지 등록할 수 있습니다.");
                    return false;
                } else {
                    $pt_best_chk = "Y";
                }
            } else {
                $query = "select * from product_t where idx = ".$_POST['pt_idx'];
                $row = $DB->fetch_query($query);
                if($row['pt_best_chk'] == "Y") {
                    $pt_best_chk = "Y";
                } else {
                    $count = $DB->count_query("select * from product_t where pt_best_chk = 'Y' and pt_show = 'Y' and pt_sale_now = 'Y'");
                    if($count > 5) {
                        p_alert("HOT한 상품은 최대 6개까지 등록할 수 있습니다.");
                        return false;
                    } else {
                        $pt_best_chk = "Y";
                    }
                }
            }
        } else {
            $pt_best_chk = "N";
        }
        if($_POST['pt_new_chk'] == "Y") {
            if($_POST['pt_idx']=='') {
                $count = $DB->count_query("select * from product_t where pt_new_chk = 'Y' and pt_show = 'Y' and (pt_sale_now = 'Y' or pt_sale_now = 'N')");
                if($count > 5) {
                    p_alert("NEW 룰루팝은 최대 6개까지 등록할 수 있습니다.");
                    return false;
                } else {
                    $pt_new_chk = "Y";
                    $pt_new_url = $_POST['pt_new_url'];
                    $pt_new_date = $_POST['YEAR']."-".$_POST['MONTH']."-".$_POST['DAY'];
                }
            } else {
                $query = "select * from product_t where idx = ".$_POST['pt_idx'];
                $row = $DB->fetch_query($query);
                if($row['pt_new_chk'] == "Y") {
                    $pt_new_chk = "Y";
                    $pt_new_url = $_POST['pt_new_url'];
                    $pt_new_date = $_POST['YEAR']."-".$_POST['MONTH']."-".$_POST['DAY'];
                } else {
                    $count = $DB->count_query("select * from product_t where pt_new_chk = 'Y' and pt_show = 'Y' and (pt_sale_now = 'Y' or pt_sale_now = 'N')");
                    if($count > 5) {
                        p_alert("NEW 룰루팝은 최대 6개까지 등록할 수 있습니다.");
                        return false;
                    } else {
                        $pt_new_chk = "Y";
                        $pt_new_url = $_POST['pt_new_url'];
                        $pt_new_date = $_POST['YEAR']."-".$_POST['MONTH']."-".$_POST['DAY'];
                    }
                }
            }
        } else {
            $pt_new_chk = "N";
            $pt_new_url = null;
            $pt_new_date = null;
        }
		$arr_query = array(
            "pct_idx" => $_POST['pct_idx'],
            "pct_m_idx" => $_POST['pct_m_idx'],
			"pt_title" => $_POST['pt_title'],
			"pt_best_chk" => $pt_best_chk,
			"pt_new_chk" => $pt_new_chk,
			"pt_new_date" => $pt_new_date,
			"pt_new_url" => $pt_new_url,
			"pt_selling_price" => $_POST['pt_selling_price'],
			"pt_stock" => $_POST['pt_stock'],
			"pt_sale_chk" => $pt_sale_chk,
			"pt_option_chk" => $_POST['pt_option_chk'],
			"pt_content" => $_POST['pt_content'],
			"pt_stock_chk" => $_POST['pt_stock_chk'],
			"pt_random_chk" => $pt_random_chk,
			"pt_show" => $pt_show,
			"pt_sale_now" => $pt_sale_now,
			"pt_wdate" => "now()",
		);

		//판매가
		if($pt_sale_chk=='Y') {
            $arr_query['pt_discount_per'] = $_POST['pt_discount_per'];
			$arr_query['pt_price'] = $_POST['pt_price'];
		} else {
			$arr_query['pt_price'] = $_POST['pt_selling_price'];
		}
        if($_POST['pt_delivery_free_chk'] == "Y") {
            $pt_delivery_free_chk = "Y";
        } else {
            $pt_delivery_free_chk = "N";
        }

        //배송비
        $arr_query['pt_delivery_chk'] = $_POST['pt_delivery_chk'];
        $arr_query['pt_delivery_price'] = $_POST['pt_delivery_price'];
        $arr_query['pt_delivery_free_chk'] = $pt_delivery_free_chk;
        $arr_query['pt_delivery_free_price'] = $_POST['pt_delivery_free_price'];
        $arr_query['pt_delivery_refund_price'] = $_POST['pt_delivery_refund_price'];
        $arr_query['pt_delivery_exchange_price'] = $_POST['pt_delivery_exchange_price'];
        $arr_query['pt_delivery_comment'] = $_POST['pt_delivery_comment'];
        $arr_query['pt_refund_comment'] = $_POST['pt_refund_comment'];

		//옵션
		if($_POST['pt_option_chk']!='1') {
			$arr_query['pt_option_type'] = $_POST['pt_option_type'];
			$arr_query['pt_option_name1'] = $_POST['pt_option_name1'];
			$arr_query['pt_option_val1'] = $_POST['pt_option_val1'];
			$arr_query['pt_option_name2'] = $_POST['pt_option_name2'];
			$arr_query['pt_option_val2'] = $_POST['pt_option_val2'];
			$arr_query['pt_option_name3'] = $_POST['pt_option_name3'];
			$arr_query['pt_option_val3'] = $_POST['pt_option_val3'];
		} else {
            if($_POST['pt_idx']) {
                $DB->del_query("product_option_t", "pt_idx = " . $_POST['pt_idx']);
            }
        }

        if($_POST['pt_idx']) {
            $query_ptc = "select * from product_t where idx = '".$_POST['pt_idx']."'";
            $row_ptc = $DB->fetch_query($query_ptc);
        }

        if($_POST['pt_idx']=='') {
            $_POST['pt_code'] = get_pt_code();
            $arr_query['pt_code'] = $_POST['pt_code'];

            $DB->insert_query('product_t', $arr_query);
            $_last_pt_idx = $DB->insert_id();
        } else {
            $where_query = "idx = '".$row_ptc['idx']."'";
            unset($arr_query['pt_wdate']);
            $arr_query['pt_udate'] = date('Y-m-d H:i:s');
            $DB->update_query('product_t', $arr_query, $where_query);
            $_last_pt_idx = $row_ptc['idx'];
        }

		unset($arr_query_img);
		$arr_query_img = array();
		for($q=1;$q<=$pt_image_num;$q++) {
			$temp_img_txt = "pt_image".$q;
			$temp_img_on_txt = "pt_image".$q."_on";
			$temp_img_temp_on_txt = "pt_image".$q."_temp_on";
			$temp_img_del_txt = "pt_image".$q."_del";

			if($_FILES[$temp_img_txt]['name']) {
				$pt_image = $_FILES[$temp_img_txt]['tmp_name'];
				$pt_image_name = $_FILES[$temp_img_txt]['name'];
				$pt_image_size = $_FILES[$temp_img_txt]['size'];
				$pt_image_type = $_FILES[$temp_img_txt]['type'];

				if($pt_image_name!="") {
					@unlink($ct_img_dir_a."/".$_POST[$temp_img_on_txt]);
					$_POST[$temp_img_on_txt] = "pt_image_".$_last_pt_idx."_".$q.".".get_file_ext($pt_image_name);
					upload_file($pt_image, $_POST[$temp_img_on_txt], $ct_img_dir_a."/");
					//thumnail($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
					//scale_image_fit($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
					thumnail_width($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000");
				}
			} else {
				if($_POST[$temp_img_del_txt]) {
					unlink($ct_img_dir_a."/".$_POST[$temp_img_del_txt]);
				}
			}
			$arr_query_img['pt_image'.$q] = $_POST['pt_image'.$q.'_on'];
		}
        $temp_img_txt = "pt_new_img";
        $temp_img_on_txt = "pt_new_img_on";
        $temp_img_temp_on_txt = "pt_new_img_temp_on";
        $temp_img_del_txt = "pt_new_img_del";
        if($_FILES[$temp_img_txt]['name']) {
            $pt_new_img = $_FILES[$temp_img_txt]['tmp_name'];
            $pt_new_img_name = $_FILES[$temp_img_txt]['name'];
            $pt_new_img_size = $_FILES[$temp_img_txt]['size'];
            $pt_new_img_type = $_FILES[$temp_img_txt]['type'];

            if($pt_new_img_name!="") {
                @unlink($ct_img_dir_a."/".$_POST[$temp_img_on_txt]);
                $_POST[$temp_img_on_txt] = "pt_new_img_".$_last_pt_idx.".".get_file_ext($pt_new_img_name);
                upload_file($pt_new_img, $_POST[$temp_img_on_txt], $ct_img_dir_a."/");
                //thumnail($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
                //scale_image_fit($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000", "1000");
                thumnail_width($ct_img_dir_a."/".$_POST[$temp_img_on_txt], $_POST[$temp_img_on_txt], $ct_img_dir_a."/", "1000");
            }
        } else {
            if($_POST[$temp_img_del_txt]) {
                unlink($ct_img_dir_a."/".$_POST[$temp_img_del_txt]);
            }
        }
        if($pt_new_chk == "N") {
            unlink($ct_img_dir_a."/".$row_ptc['pt_new_img']);
            $arr_query_img['pt_new_img'] = null;
        } else {
            $arr_query_img['pt_new_img'] = $_POST['pt_new_img_on'];
        }

		if($arr_query_img) {
			$where_query = "idx = '".$_last_pt_idx."'";

			$DB->update_query('product_t', $arr_query_img, $where_query);
		}
		
		$array_query = $arr_query;

		if($_POST['pt_option_chk']=='2') {
			$DB->del_query('product_option_t', " pt_idx = '".$_last_pt_idx."'");

			if($_POST['pt_option_type']=='1') { //단독형
				//상품옵션 테이블
				foreach($_POST['pot_name'] as $key => $val) {
					unset($arr_query);
					$arr_query = array(
						"pt_idx" => $_last_pt_idx,
						"pot_name" => $_POST['pot_name'][$key],
						"pot_value" => $_POST['pot_value'][$key],
						"pot_use" => $_POST['pot_use'][$key],
						"pot_jaego" => $_POST['pot_jaego'][$key],
					);

					$DB->insert_query('product_option_t', $arr_query);
				}
			}
		}

		//cart_t
		if($_POST['act']=='update'){
			$cart_list = $DB->select_query("select * from cart_t where pt_idx=".$row_ptc['idx']." and ct_select < 2");
			foreach($cart_list as $cart_row){
				if($cart_row['idx'] > 0) {
                    $cart_set = array();

                    if ($_POST['pt_option_chk'] == '2' && $_POST['pt_option_type'] == '1') {    //단독형
                        $ct_opt_name = str_replace('/', '', $cart_row['ct_opt_name']);
                        $ct_opt_value = str_replace('/', '', $cart_row['ct_opt_value']);
                        $option_row = $DB->fetch_query("select * from product_option_t where pt_idx=" . $row_ptc['idx'] . " and pot_name='" . $ct_opt_name . "' and pot_value='" . $ct_opt_value . "' and pot_use='Y'");
                        $cart_set['pt_price'] = $array_query['pt_price'];
                        $cart_set['ct_opt_price'] = $array_query['pt_price'];
                        $cart_set['ct_price'] = $array_query['pt_price'] * $cart_row['ct_opt_qty'];
                        if ($option_row['idx']) {
                            $DB->update_query("cart_t", $cart_set, "idx=" . $cart_row['idx']);
                        } else {
                            $DB->del_query("cart_t", "idx=" . $cart_row['idx']);
                        }
                    }
                    if($_POST['pt_show'] == "N" || $_POST['pt_sale_now'] == "N" || $_POST['pt_sale_now'] == "0") {
                        $DB->del_query("cart_t", "idx=" . $cart_row['idx']);
                    }
                }
			}
		}

		if($_POST['pat_jejosa']) {
			if($_POST['pat_origin1']=='기타') {
				$pat_origin_t = $_POST['pat_origin1'];
				$pat_origin_etc_t = $_POST['pat_origin_etc'];
			} else {
				$pat_origin_t = $_POST['pat_origin1']."|".$_POST['pat_origin2']."|".$_POST['pat_origin3']."|";
				$pat_origin_etc_t = '';
			}

			//상품주요정보 테이블
			unset($arr_query);
			$arr_query = array(
				"pt_idx" => $_last_pt_idx,
				"pat_model" => $_POST['pat_model'],
				"pat_brand" => $_POST['pat_brand'],
				"pat_jejosa" => $_POST['pat_jejosa'],
				"pat_kc_chk" => $_POST['pat_kc_chk'],
				"pat_kc_info" => $_POST['pat_kc_info'],
				"pat_origin" => $pat_origin_t,
				"pat_origin_etc" => $pat_origin_etc_t,
				"pat_used" => $_POST['pat_used'],
				"pat_jejo_date" => $_POST['pat_jejo_date'],
				"pat_valid_date" => $_POST['pat_valid_date'],
				"pat_child" => $_POST['pat_child'],
			);

			$query_ptc = "select idx from product_attribute_t where pt_idx = '".$_last_pt_idx."'";
			$row_ptc = $DB->fetch_query($query_ptc);

			if($row_ptc['idx']=='') {
				$DB->insert_query('product_attribute_t', $arr_query);
			} else {
				$where_query = "idx = '".$row_ptc['idx']."'";

				$DB->update_query('product_attribute_t', $arr_query, $where_query);
			}
		}

		if($_POST['ppt_chk']!='') {
			//상품정보제공고시 테이블
			unset($arr_query);
			$arr_query = array(
				"pt_idx" => $_last_pt_idx,
				"ppt_chk" => $_POST['ppt_chk'],
				"ppt_content" => implode('|:|', $_POST['ppt_content']),
			);

			$query_ptc = "select idx from product_provide_t where pt_idx = '".$_last_pt_idx."'";
			$row_ptc = $DB->fetch_query($query_ptc);

			if($row_ptc['idx']=='') {
				$DB->insert_query('product_provide_t', $arr_query);
			} else {
				$where_query = "idx = '".$row_ptc['idx']."'";

				$DB->update_query('product_provide_t', $arr_query, $where_query);
			}
		}
		p_alert('처리되었습니다.');
	} else if($_POST['act']=='product_option_list') {
		if($_POST['pt_option_name1']=='' || $_POST['pt_option_val1']=='') {
			echo "error";
			exit;
		}

		$_POST['pt_option_name1'] = trim($_POST['pt_option_name1']);
		$_POST['pt_option_name2'] = trim($_POST['pt_option_name2']);
		$_POST['pt_option_name3'] = trim($_POST['pt_option_name3']);
		$pt_option_val1_ex = explode(',', $_POST['pt_option_val1']);
		$pt_option_val2_ex = explode(',', $_POST['pt_option_val2']);
		$pt_option_val3_ex = explode(',', $_POST['pt_option_val3']);

		unset($arr_list);
		$arr_list = array();
		if($_POST['pt_option_type']=='1') { //단독형
			if($_POST['update_chk']) {
?>
<table class="table table-striped table-hover">
<thead>
<tr>
	<th class="text-center">
		옵션명
	</th>
	<th class="text-center">
		옵션값
	</th>
    <th calss="text-center">
        재고수량
    </th>
	<th class="text-center">
		사용여부
	</th>
	<th class="text-center">
		삭제
	</th>
</tr>
</thead>
<tbody>
<?
	$q = 1;
	unset($list);
	$query = "select * from product_option_t where pt_idx = '".$_POST['update_chk']."'";
	$list = $DB->select_query($query);

	if($list) {
		foreach($list as $row) {
?>
<tr class="c_pot_list" id="pot_list_<?=$q?>">
	<td class="text-center">
		<input type="text" name="pot_name[]" id="pot_name<?=$q?>" value="<?=$row['pot_name']?>" class="form-control form-control-sm" />
	</td>
	<td>
		<input type="text" name="pot_value[]" id="pot_value<?=$q?>" value="<?=$row['pot_value']?>" class="form-control form-control-sm" />
	</td>
    <td>
        <input type="text" name="pot_jaego[]" id="pot_jaego<?=$q?>" value="<?=$row['pot_jaego']?>" class="form-control form-control-sm" />
    </td>
	<td class="text-center">
		<select name="pot_use[]" id="pot_use<?=$q?>" class="custom-select">
			<option value="Y"<? if($row['pot_use']=='Y') { ?> selected<? } ?>>Y</option>
			<option value="N"<? if($row['pot_use']!='Y') { ?> selected<? } ?>>N</option>
		</select>
	</td>
	<td class="text-center">
		<input type="button" class="btn btn-outline-danger btn-sm" value="삭제" onclick="f_pot_delete('<?=$q?>');" />
	</td>
</tr>
<?
			$q++;
		}
	}
?>
</tbody>
</table>
<?
			} else {
				if($pt_option_val1_ex) {
					foreach($pt_option_val1_ex as $key1 => $val1) {
						if($val1) {
							$arr_list[$_POST['pt_option_name1']][] = trim($val1);
						}
					}
				}
				if($pt_option_val2_ex) {
					foreach($pt_option_val2_ex as $key2 => $val2) {
						if($val2) {
							$arr_list[$_POST['pt_option_name2']][] = trim($val2);
						}
					}
				}
				if($pt_option_val3_ex) {
					foreach($pt_option_val3_ex as $key3 => $val3) {
						if($val3) {
							$arr_list[$_POST['pt_option_name3']][] = trim($val3);
						}
					}
				}
?>
<table class="table table-striped table-hover">
<thead>
<tr>
	<th class="text-center">
		옵션명
	</th>
	<th class="text-center">
		옵션값
	</th>
    <th calss="text-center">
        재고수량
    </th>
	<th class="text-center">
		사용여부
	</th>
	<th class="text-center">
		삭제
	</th>
</tr>
</thead>
<tbody>
<?
	$q = 1;
	foreach($arr_list as $key => $val) {
		foreach($val as $key2 => $val2) {
?>
<tr class="c_pot_list" id="pot_list_<?=$q?>">
	<td class="text-center">
		<input type="text" name="pot_name[]" id="pot_name<?=$q?>" value="<?=$key?>" class="form-control form-control-sm" />
	</td>
	<td>
		<input type="text" name="pot_value[]" id="pot_value<?=$q?>" value="<?=$val2?>" class="form-control form-control-sm" />
	</td>
    <td>
		<input type="text" name="pot_jaego[]" id="pot_value<?=$q?>" value="99" class="form-control form-control-sm" />
	</td>
	<td class="text-center">
		<select name="pot_use[]" id="pot_use<?=$q?>" class="custom-select">
			<option value="Y">Y</option>
			<option value="N">N</option>
		</select>
	</td>
	<td class="text-center">
		<input type="button" class="btn btn-outline-danger btn-sm" value="삭제" onclick="f_pot_delete('<?=$q?>');" />
	</td>
</tr>
<?
			$q++;
		}
	}
?>
</tbody>
</table>
<?
			}
		} else { //조합형
			if($_POST['update_chk']) {
				$query_pt = "select pt_option_name1, pt_option_name2, pt_option_name3 from product_t where idx = '".$_POST['update_chk']."'";
				$row_pt = $DB->fetch_query($query_pt);
?>
<table class="table table-striped table-hover">
<thead>
<tr>
	<? if($row_pt['pt_option_name1']) { ?>
	<th class="text-center">
		<?=$row_pt['pt_option_name1']?>
	</th>
	<? } ?>
	<? if($row_pt['pt_option_name2']) { ?>
	<th class="text-center">
		<?=$row_pt['pt_option_name2']?>
	</th>
	<? } ?>
	<? if($row_pt['pt_option_name3']) { ?>
	<th class="text-center">
		<?=$row_pt['pt_option_name3']?>
	</th>
	<? } ?>
	<th class="text-center">
		옵션가
	</th>
	<th class="text-center">
		재고
	</th>
	<th class="text-center">
		사용여부
	</th>
	<th class="text-center">
		삭제
	</th>
</tr>
</thead>
<tbody>
<?
	$q = 1;
	unset($list);
	$query = "select * from product_option_t where pt_idx = '".$_POST['update_chk']."'";
	$list = $DB->select_query($query);

	if($list) {
		foreach($list as $row) {
			$val_ex = explode('|:|', $row['pot_name']);
?>
<tr class="c_pot_list" id="pot_list_<?=$q?>">
	<? if($val_ex[0]) { ?>
	<td class="text-center">
		<input type="text" name="pot_name1[]" id="pot_name1" value="<?=$val_ex[0]?>" class="form-control form-control-sm" />
	</td>
	<? } ?>
	<? if($val_ex[1]) { ?>
	<td class="text-center">
		<input type="text" name="pot_name2[]" id="pot_name2" value="<?=$val_ex[1]?>" class="form-control form-control-sm" />
	</td>
	<? } ?>
	<? if($val_ex[2]) { ?>
	<td class="text-center">
		<input type="text" name="pot_name3[]" id="pot_name3" value="<?=$val_ex[2]?>" class="form-control form-control-sm" />
	</td>
	<? } ?>
	<td>
		<input type="text" name="pot_price[]" id="pot_price<?=$q?>" value="<?=$row['pot_price']?>" class="form-control form-control-sm" numberOnly placeholder="1000" />
	</td>
	<td>
		<input type="text" name="pot_jaego[]" id="pot_jaego<?=$q?>" value="<?=$row['pot_jaego']?>" class="form-control form-control-sm" numberOnly placeholder="99" />
	</td>
	<td class="text-center">
		<select name="pot_use[]" id="pot_use<?=$q?>" class="custom-select">
			<option value="Y"<? if($row['pot_use']=='Y') { ?> selected<? } ?>>Y</option>
			<option value="N"<? if($row['pot_use']!='Y') { ?> selected<? } ?>>N</option>
		</select>
	</td>
	<td class="text-center">
		<input type="button" class="btn btn-outline-danger btn-sm" value="삭제" onclick="f_pot_delete('<?=$q?>');" />
	</td>
</tr>
<?
			$q++;
		}
	}
?>
</tbody>
</table>
<?
			} else {
				if($pt_option_val1_ex) {
					foreach($pt_option_val1_ex as $key1 => $val1) {
						if($pt_option_val2_ex) {
							foreach($pt_option_val2_ex as $key2 => $val2) {
								if($pt_option_val3_ex) {
									foreach($pt_option_val3_ex as $key3 => $val3) {
										$arr_list_t = "";
										if($val1) {
											$arr_list_t .= trim($val1)."|:|";
										}
										if($val2) {
											$arr_list_t .= trim($val2)."|:|";
										}
										if($val3) {
											$arr_list_t .= trim($val3);
										}

										$arr_list[] = $arr_list_t;
									}
								}
							}
						}
					}
				}
?>
<table class="table table-striped table-hover">
<thead>
<tr>
	<? if($_POST['pt_option_name1']) { ?>
	<th class="text-center">
		<?=$_POST['pt_option_name1']?>
	</th>
	<? } ?>
	<? if($_POST['pt_option_name2']) { ?>
	<th class="text-center">
		<?=$_POST['pt_option_name2']?>
	</th>
	<? } ?>
	<? if($_POST['pt_option_name3']) { ?>
	<th class="text-center">
		<?=$_POST['pt_option_name3']?>
	</th>
	<? } ?>
	<th class="text-center">
		옵션가
	</th>
	<th class="text-center">
		재고
	</th>
	<th class="text-center">
		사용여부
	</th>
	<th class="text-center">
		삭제
	</th>
</tr>
</thead>
<tbody>
<?
	$q = 1;
	foreach($arr_list as $key => $val) {
		$val_ex = explode('|:|', $val);
?>
<tr class="c_pot_list" id="pot_list_<?=$q?>">
	<? if($_POST['pt_option_name1']) { ?>
	<td class="text-center">
		<input type="text" name="pot_name1[]" id="pot_name1" value="<?=$val_ex[0]?>" class="form-control form-control-sm" />
	</td>
	<? } ?>
	<? if($_POST['pt_option_name2']) { ?>
	<td class="text-center">
		<input type="text" name="pot_name2[]" id="pot_name2" value="<?=$val_ex[1]?>" class="form-control form-control-sm" />
	</td>
	<? } ?>
	<? if($_POST['pt_option_name3']) { ?>
	<td class="text-center">
		<input type="text" name="pot_name3[]" id="pot_name3" value="<?=$val_ex[2]?>" class="form-control form-control-sm" />
	</td>
	<? } ?>
	<td>
		<input type="text" name="pot_price[]" id="pot_price<?=$q?>" value="0" class="form-control form-control-sm" numberOnly placeholder="1000" />
	</td>
	<td>
		<input type="text" name="pot_jaego[]" id="pot_jaego<?=$q?>" value="0" class="form-control form-control-sm" numberOnly placeholder="99" />
	</td>
	<td class="text-center">
		<select name="pot_use[]" id="pot_use<?=$q?>" class="custom-select">
			<option value="Y">Y</option>
			<option value="N">N</option>
		</select>
	</td>
	<td class="text-center">
		<input type="button" class="btn btn-outline-danger btn-sm" value="삭제" onclick="f_pot_delete('<?=$q?>');" />
	</td>
</tr>
<?
		$q++;
	}
?>
</tbody>
</table>
<?
			}
		}
	} else if($_POST['act']=='pat_origin') {
		if($_POST['pat_origin_level']=='2') {
			foreach($arr_pat_origin[$_POST['pat_origin2_value']] as $key => $val) {
				if($key) {
					$arr_pat_origin_option .= "<option value='".$key."' >".$key."</option>";
				}
			}
		} else {
			foreach($arr_pat_origin[$_POST['pat_origin1_value']][$_POST['pat_origin2_value']] as $key => $val) {
				if($val) {
					$arr_pat_origin_option .= "<option value='".$val."' >".$val."</option>";
				}
			}
		}

		echo $arr_pat_origin_option;
	} else if($_POST['act']=='product_supplement_list') {
		if($_POST['update_chk']) {
?>
<table class="table table-striped table-hover">
<thead>
<tr>
	<th class="text-center">
		추가상품명.
	</th>
	<th class="text-center">
		추가상품값
	</th>
	<th class="text-center">
		추가상품가
	</th>
	<th class="text-center">
		재고
	</th>
	<th class="text-center">
		사용여부
	</th>
	<th class="text-center">
		삭제
	</th>
</tr>
</thead>
<tbody>
<?
			$q = 1;
			unset($list);
			$query = "select * from product_supplement_t where pt_idx = '".$_POST['update_chk']."'";
			$list = $DB->select_query($query);

			if($list) {
				foreach($list as $row) {
?>
<tr class="c_pst_list" id="pst_list_<?=$q?>">
	<td class="text-center">
		<input type="text" name="pst_title_t[]" id="pst_title_t<?=$q?>" value="<?=$row['pst_title']?>" class="form-control form-control-sm" />
	</td>
	<td class="text-center">
		<input type="text" name="pst_value_t[]" id="pst_value_t<?=$q?>" value="<?=$row['pst_value']?>" class="form-control form-control-sm" />
	</td>
	<td>
		<input type="text" name="pst_price_t[]" id="pst_price_t<?=$q?>" value="<?=$row['pst_price']?>" class="form-control form-control-sm" numberOnly placeholder="1000" />
	</td>
	<td>
		<input type="text" name="pst_jaego_t[]" id="pst_jaego_t<?=$q?>" value="<?=$row['pst_jaego']?>" class="form-control form-control-sm" numberOnly placeholder="99" />
	</td>
	<td class="text-center">
		<select name="pst_use_t[]" id="pot_use_t<?=$q?>" class="custom-select">
			<option value="Y"<? if($row['pot_use']=='Y') { ?> selected<? } ?>>Y</option>
			<option value="N"<? if($row['pot_use']!='Y') { ?> selected<? } ?>>N</option>
		</select>
	</td>
	<td class="text-center">
		<input type="button" class="btn btn-outline-danger btn-sm" value="삭제" onclick="f_pst_delete('<?=$q?>');" />
	</td>
</tr>
<?
					$q++;
				}
			}
?>
</tbody>
</table>
<?
		} else {
			if($_POST['pst_title'][0]=='') {
				echo "error";
				exit;
			}
			if($_POST['pst_value'][0]=='') {
				echo "error";
				exit;
			}
			if($_POST['pst_price'][0]=='') {
				echo "error";
				exit;
			}
?>
<table class="table table-striped table-hover">
<thead>
<tr>
	<th class="text-center">
		추가상품명
	</th>
	<th class="text-center">
		추가상품값
	</th>
	<th class="text-center">
		추가상품가
	</th>
	<th class="text-center">
		재고
	</th>
	<th class="text-center">
		사용여부
	</th>
	<th class="text-center">
		삭제
	</th>
</tr>
</thead>
<tbody>
<?
			$q = 1;
			foreach($_POST['pst_title'] as $key => $val) {
				if($val) {
?>
<tr class="c_pst_list" id="pst_list_<?=$q?>">
	<td class="text-center">
		<input type="text" name="pst_title_t[]" id="pst_title_t<?=$q?>" value="<?=$_POST['pst_title'][$key]?>" class="form-control form-control-sm" />
	</td>
	<td class="text-center">
		<input type="text" name="pst_value_t[]" id="pst_value_t<?=$q?>" value="<?=$_POST['pst_value'][$key]?>" class="form-control form-control-sm" />
	</td>
	<td>
		<input type="text" name="pst_price_t[]" id="pst_price_t<?=$q?>" value="<?=$_POST['pst_price'][$key]?>" class="form-control form-control-sm" numberOnly placeholder="1000" />
	</td>
	<td>
		<input type="text" name="pst_jaego_t[]" id="pst_jaego_t<?=$q?>" value="" class="form-control form-control-sm" numberOnly placeholder="99" />
	</td>
	<td class="text-center">
		<select name="pst_use_t[]" id="pot_use_t<?=$q?>" class="custom-select">
			<option value="Y">Y</option>
			<option value="N">N</option>
		</select>
	</td>
	<td class="text-center">
		<input type="button" class="btn btn-outline-danger btn-sm" value="삭제" onclick="f_pst_delete('<?=$q?>');" />
	</td>
</tr>
<?
					$q++;
				}
			}
?>
</tbody>
</table>
<?
		}
	} else if($_POST['act']=='swipe_image') {
?>
	<div class="modal-header">
		<h5 class="modal-title" id="staticBackdropLabel">등록상품이미지</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body">
		<div class="slider" id="product-swiper">
			<?
				$query = "
					select * from product_t
					where idx = '".$_POST['pt_idx']."'
				";
				$row = $DB->fetch_query($query);

				for($q=1;$q<=$pt_image_num;$q++) {
					$pt_image_t = "pt_image".$q;
					if($row[$pt_image_t]) {
			?>
			<div class="m-2"><img src="<?=$ct_img_url."/".$row[$pt_image_t]?>" onerror="this.src='<?=$ct_no_img_url?>'" class="product-swipe" alt="<?=$row['pt_title']?>"></div>
			<?
					}
				}
			?>
		</div>
	</div>
<?
	} else if($_POST['act']=='update_info') {
		$rtn = get_product_info($_POST['pt_idx']);

		echo result_data('true', '관리자 상품수정 정보입니다.', $rtn);
	} else if($_POST['act']=='change_status') {     //상품 관리 리스트에서 상태값 변경 함수
        unset($arr_query);
        $arr_query = array(
            "pt_sale_now" => $_POST['pt_sale_now'],
            "pt_udate" => "now()",
        );

        $where_query = "idx = '".$_POST['pt_idx']."'";

        $DB->update_query('product_t', $arr_query, $where_query);

        echo "Y";
    } else if($_POST['act']=='select_stop') {   //리스트에서 선택한 값 상태값 변경 함수
        if(count($_POST['pt_idx']) > 0)
        {
            for($i=0; $i<count($_POST['pt_idx']); $i++)
            {                
                unset($arr_query);
                $arr_query = array(
                    "pt_sale_now" => $_POST['status'],
                    "pt_udate" => "now()",
                );

                $where_query = "idx = '".$_POST['pt_idx'][$i]."'";

                $DB->update_query('product_t', $arr_query, $where_query);				
            }
            echo json_encode(array('result' => '_ok', 'msg'=>'판매중지 되었습니다.'));
			exit;
        }
        else{
            echo json_encode(array('result' => 'false', 'msg'=>'삭제 실패'));
			exit;
        }
    } else if($_POST['act']=='select_delete') {     //리스트에서 선택한 값 삭제 함수
        if(count($_POST['pt_idx'])>0) {
            for ($i = 0; $i < count($_POST['pt_idx']); $i++) {
                $DB->del_query('product_afterservice_info_t', " pt_idx = '" . $_POST['pt_idx'][$i] . "'");
                $DB->del_query('product_attribute_t', " pt_idx = '" . $_POST['pt_idx'][$i] . "'");
                $DB->del_query('product_deliveryInfo_t', " pt_idx = '" . $_POST['pt_idx'][$i] . "'");
                $DB->del_query('product_option_t', " pt_idx = '" . $_POST['pt_idx'][$i] . "'");
                $DB->del_query('product_provide_t', " pt_idx = '" . $_POST['pt_idx'][$i] . "'");
                $DB->del_query('product_purchase_benefit_t', " pt_idx = '" . $_POST['pt_idx'][$i] . "'");
                $DB->del_query('product_search_config_t', " pt_idx = '" . $_POST['pt_idx'][$i] . "'");
                $DB->del_query('product_sellercode_t', " pt_idx = '" . $_POST['pt_idx'][$i] . "'");
                $DB->del_query('product_supplement_t', " pt_idx = '" . $_POST['pt_idx'][$i] . "'");

                $query_ptc = "select * from product_t where idx = '" . $_POST['pt_idx'][$i] . "'";
                $row_ptc = $DB->fetch_query($query_ptc);

                for ($q = 1; $q <= $pt_image_num; $q++) {					
					
					if(is_file($ct_img_dir_a . "/" . $row_ptc['pt_image' . $q])){
						echo $ct_img_dir_a . "/" . $row_ptc['pt_image' . $q];
                    	@unlink($ct_img_dir_a . "/" . $row_ptc['pt_image' . $q]);
					}
                }

                preg_match_all("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i", $row_ptc['pt_content'], $pt_content_img);

                foreach ($pt_content_img[1] as $key => $val) {
                    $val = str_replace($ct_img_url, '', $val);					
                    if (is_file($ct_img_dir_a . "/" . $val)) {
                        @unlink($ct_img_dir_a . "/" . $val);
                    }
                }

                $DB->del_query('product_t', " idx = '" . $_REQUEST['idx'][$i] . "'");
            }
            echo json_encode(array('result' => '_ok', 'msg'=>'삭제 되었습니다.'));
			exit;
        }
        else{
            echo json_encode(array('result' => 'false', 'msg'=>'삭제 실패'));
			exit;
        }
    } else if($_POST['act'] == "send_push") {
        $query = "select * from wish_product_t where pt_idx = ".$_POST['idx']." and wpt_status = 'Y'";
        $list = $DB->select_query($query);
        if($list) {
            foreach ($list as $row) {
                $query = "select * from member_t where idx = ".$row['mt_idx'];
                $list2 = $DB->select_query($query);
                if($list2) {
                    foreach ($list2 as $row2) {
                        if($row2['mt_pushing'] == "Y" || $row2['mt_pushing2'] == "Y") {
                            $chk = "Y";
                        } else {
                            $chk = "N";
                        }
                        $token_list = array($row2['mt_fcm']);
                        $message = "품절되었던 상품이 재입고 되었습니다.";
                        $title = "룰루팝 상품 알림";

                        $op_idx .= $row2['idx'].",";

                        send_notification2($token_list, $title, $message, "Product_Detail_Page", $_POST['idx'], $chk);
                    }
                }
            }
            unset($arr_query);
            $plt_set = array(
                'plt_title'=>$title,
                'plt_content'=>$message,
                'plt_table'=>"product_t",
                'plt_type'=> 2,
                'plt_index'=>$_POST['idx'],
                'mt_idx'=>1,
                'op_idx'=>$op_idx,
                'plt_wdate'=>'now()'
            );
            $DB->insert_query("pushnotification_log_t", $plt_set);
        }
        echo json_encode(array('result' => '_ok', 'msg'=>'푸시가 발송되었습니다.'));
    } else if($_POST['act'] == "del_img") {
        $DB->update_query("product_t", array("pt_image".$_POST['num'] => null), "idx = ".$_POST['idx']);
        @unlink($ct_img_dir_a . "/" . $_POST['name']);
        echo json_encode(array('result' => '_ok', 'msg'=>'삭제되었습니다.'));
    } else if($_POST['act'] == "get_pct_m") {
        $html = '';
        $list = $DB->select_query("select * from product_category_t where pc_m_idx = ".$_POST['pc_m_idx']);
        if($list) {
            foreach ($list as $row) {
                $html .= '<option value="'.$row['idx'].'" >'.$row['pc_name'].'</option>';
            }
        }
        echo json_encode(array('result' => '_ok', 'data'=>$html));
    }

	include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>