<?
	include "./head_inc.php";
	$chk_menu = "2";
	$chk_sub_menu = "1";
	$chk_post_code = 'Y';
	$chk_ckeditor = 'Y';
	include "./head_menu_inc.php";

	if($_GET['act']=="update") {
		$query_pt = "
			select *, a1.idx as pt_idx from product_t a1
			where a1.idx = '".$_GET['pt_idx']."'
		";
		$row_pt = $DB->fetch_query($query_pt);

		$_act = "update";
		$_act_txt = "수정";
	} else {
		$_act = "input";
		$_act_txt = "등록";
	}

	if($_GET['ptl_idx']=='') {
		$_GET['ptl_idx'] = '1';
	}

	$_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&pt_sale_now=".$_GET['pt_sale_now']."&pt_show=".$_GET['pt_show']."&sel_ct_id1=".$_GET['sel_ct_id1']."&sel_ct_id2=".$_GET['sel_ct_id2']."&sel_ct_id3=".$_GET['sel_ct_id3']."&sel_ct_id4=".$_GET['sel_ct_id4']."&pg=".$_GET['pg'];

    $category1 = $DB->select_query("select idx, pc_name from product_category_t where pc_depth = 0");
?>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">상품관리 <?=$_act_txt?></h4>
                    <? if($_GET['act']=="update") { ?>
                    <input type="button" value="푸시발송" onclick="send_push()" class="btn btn-primary float-right mx-2">
                    <? } ?>
					<p class="card-description">
						상품관리 수정, 삭제 할 수 있습니다.<br/>
						Chrome 사용을 권장합니다. 이외의 브라우저 또는 Chrome 하위버전으로 접속 할 경우 페이지가 깨져 보일 수 있습니다.<br/>
						<i class="mdi mdi-circle-medium text-danger"></i> <b>필수등록 항목입니다.</b>
					</p>

					<form method="post" name="frm_form" id="frm_form" action="./product_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm" enctype="multipart/form-data">
					<input type="hidden" name="act" id="act" value="input" />
					<input type="hidden" name="pt_idx" id="pt_idx" value="<?=$_GET['pt_idx']?>" />

					<div class="faq-section">
						<div id="pro_accordion" class="accordion acc_card">
							<?
								$pro_acc_iid = '1';
							?>
                            <div class="card">
                                <div class="card-header" id="pro_head_<?=$pro_acc_iid?>">
                                    <h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_<?=$pro_acc_iid?>" aria-expanded="true" aria-controls="pro_collapse_<?=$pro_acc_iid?>" class="text-dark">카테고리 <i class="mdi mdi-circle-medium text-danger"></i></a></h5>
                                </div>
                                <div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
                                    <div class="card-body">
                                        <p class="mb-0">
                                        <div class="custom-control custom-check custom-control-inline pl-0">
                                            <select class="form-control form-control-sm" name="pct_idx" id="pct_idx" onchange="get_pct_m(this)">
                                                <option value="" hidden="">대분류 선택</option>
                                                <?php foreach($category1 as $val):?>
                                                    <option value="<?=$val['idx']?>" <?if($val['idx'] == $row_pt['pct_idx']) echo "selected=''";?>><?=$val['pc_name']?></option>
                                                <?php endforeach;?>
                                            </select>
                                        </div>
                                        <div class="custom-control custom-check custom-control-inline pl-0">
                                            <select class="form-control form-control-sm" name="pct_m_idx" id="pct_m_idx">
                                                <option value="" hidden="">중분류 선택</option>
                                                <?
                                                if($_act == "update") {
                                                $category2 = $DB->select_query("select idx, pc_name from product_category_t  where pc_depth = 1 and pc_m_idx = ".$row_pt['pct_idx']);
                                                foreach($category2 as $val): ?>
                                                    <option value="<?=$val['idx']?>" <?if($val['idx'] == $row_pt['pct_m_idx']) echo "selected=''";?>><?=$val['pc_name']?></option>
                                                <?php endforeach; } ?>
                                            </select>
                                        </div>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?
								$pro_acc_iid = '2';
							?>
							<div class="card">
								<div class="card-header" id="pro_head_<?=$pro_acc_iid?>">
									<h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_<?=$pro_acc_iid?>" aria-expanded="true" aria-controls="pro_collapse_<?=$pro_acc_iid?>" class="text-dark">상품명 <i class="mdi mdi-circle-medium text-danger"></i></a></h5>
								</div>
								<div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
									<div class="card-body">
										<p class="mb-0">
											<input type="text" name="pt_title" id="pt_title" value="<?php echo $row_pt['pt_title'];?>" maxlength="100" class="form-control form-control-sm" />
										</p>
									</div>
								</div>
							</div>
							<?
								$pro_acc_iid = '3';
                                $pct_id_arr = explode(',', $row_pt['pct_id']);
							?>
                            <div class="card">
                                <div class="card-header" id="pro_head_<?=$pro_acc_iid?>">
                                    <h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_<?=$pro_acc_iid?>" aria-expanded="true" aria-controls="pro_collapse_<?=$pro_acc_iid?>" class="text-dark">상품이미지 <i class="mdi mdi-circle-medium text-danger"></i></a></h5>
                                </div>
                                <div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
                                    <div class="card-body">
<!--                                        <div class="text-right pr-2">-->
<!--                                            <a data-toggle="popover" title="" data-html='true' data-trigger="hover" data-content="상품이미지 등록하기 내용" data-original-title="상품이미지 등록하기 제목"><i class="mdi mdi-help-rhombus-outline"></i></a>-->
<!--                                        </div>-->
                                        <li class="list-group-item pl-0" style="border: none;">
                                            <div class="form-group row align-items-center mb-0">
                                                <div class="col-sm-12">
                                                    <? for($q=1;$q<=$pt_image_num;$q++) { ?>
                                                        <input type="file" name="pt_image<?=$q?>" id="pt_image<?=$q?>" value="<?=$row_pt['pt_image'.$q]?>" accept=".gif, .jpg, .jpeg, .png, .gif, .bmp" class="d-none" />
                                                        <input type="hidden" name="pt_image<?=$q?>_on" id="pt_image<?=$q?>_on" value="<?=$row_pt['pt_image'.$q]?>" class="form-control" />

                                                        <label for="pt_image<?=$q?>" class="plus-input-small mr-2" id="pt_image<?=$q?>_box" style="width: 100px;height: 100px;border-radius: 10px;">
                                                            <i class="mdi mdi-plus" style="line-height: 90px;"></i></label>
                                                        <script type="text/javascript">
                                                            $('#pt_image<?=$q?>').on('change', function(e) {
                                                                var target_id = e.target.id;
                                                                var files = e.target.files;
                                                                var filesArr = Array.prototype.slice.call(files);

                                                                if(filesArr.lengths>10) {
                                                                    alert("추가이미지는 최대 10개까지 가능합니다.");
                                                                    return;
                                                                } else {
                                                                    filesArr.forEach(function(f) {
                                                                        if(!f.type.match("image.*")) {
                                                                            alert("확장자는 이미지 확장자만 가능합니다.");
                                                                            return;
                                                                        }
                                                                        if(f.size >= 10000000) {
                                                                            alert("사진 크기 15MB 이상은 등록할 수 없습니다.");
                                                                            return;
                                                                        }

                                                                        sel_files.push(f);

                                                                        var reader = new FileReader();
                                                                        reader.onload = function(e) {
                                                                            $("#"+target_id+"_box").css('border', 'none');
                                                                            $("#"+target_id+"_box").html('<img src="'+e.target.result+'" style="width: 100px;height: 100px;border-radius: 10px;" /><i class="mdi mdi-close" style="position: relative;left: 40px;top: -123px;color:red;cursor: pointer;" onclick="del_img2('+target_id+')"></i>');
                                                                            var length = $("#"+target_id+"_box").find("img").length;
                                                                            if(length > 0) {
                                                                                $("#"+target_id+"_box").attr("for", "");
                                                                            }
                                                                        }
                                                                        reader.readAsDataURL(f);
                                                                    });
                                                                }
                                                            });
                                                        </script>
                                                    <? } ?>
                                                </div>
                                                <small id="select_category_help" class="form-text text-muted">
                                                    크기 : 15MB 이하<br/>
                                                    jpg,jpeg,gif,png,bmp 형식의 정지 이미지만 등록됩니다.(움직이는 이미지의 경우 첫 번째 컷이 등록)
                                                </small>
                                            </div>
                                        </li>
                                        </p>
                                    </div>
                                </div>
                            </div>
							<?
								$pro_acc_iid = '4';
							?>
                            <div class="card">
                                <div class="card-header" id="pro_head_<?=$pro_acc_iid?>">
                                    <h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_<?=$pro_acc_iid?>" aria-expanded="true" aria-controls="pro_collapse_<?=$pro_acc_iid?>" class="text-dark">판매가 <i class="mdi mdi-circle-medium text-danger"></i></a></h5>
                                </div>
                                <div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
                                    <div class="card-body">
                                        <p class="mb-0">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="pt_selling_price" class="col-sm-2 col-form-label">정상가 <b class="text-danger">*</b></label>
                                                    <div class="col-sm-3">
                                                        <div class="input-group">
                                                            <input type="text" name="pt_selling_price" id="pt_selling_price" value="<?php echo $row_pt['pt_selling_price']?>" class="form-control" numberOnly maxlength="10" onkeyup="chk_sale()" />
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">원</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="pt_price" class="col-sm-2 col-form-label">판매가 <b class="text-danger">*</b></label>
                                                    <div class="col-sm-3">
                                                        <div class="input-group">
                                                            <input type="text" name="pt_price" id="pt_price" value="<?php echo $row_pt['pt_price']?>" class="form-control" numberOnly maxlength="10" onkeyup="chk_sale()"/>
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">원</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="pt_discount_per" class="col-sm-2 col-form-label">할인율 <b class="text-danger">*</b></label>
                                                    <div class="col-sm-3">
                                                        <div class="input-group">
                                                            <input type="text" name="pt_discount_per" id="pt_discount_per" readonly value="<?php echo $row_pt['pt_discount_per']?>" class="form-control" numberOnly maxlength="10" />
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                        </p>
                                    </div>
                                </div>
                            </div>
							<?
								$pro_acc_iid = '5';
							?>
                            <div class="card">
                                <div class="card-header" id="pro_head_<?=$pro_acc_iid?>">
                                    <h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_<?=$pro_acc_iid?>" aria-expanded="true" aria-controls="pro_collapse_<?=$pro_acc_iid?>" class="text-dark">판매재고 <i class="mdi mdi-circle-medium text-danger"></i></a></h5>
                                </div>
                                <div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
                                    <div class="card-body">
                                        <p class="mb-0">
                                        <div class="col-sm-10">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="jaego" name="pt_stock_chk" value="Y" class="custom-control-input">
                                                <label class="custom-control-label" for="jaego">재고량에 따름</label>
                                            </div>
                                            <div class="custom-control custom-check custom-control-inline">
                                                <div class="input-group">
                                                    <input type="text" name="pt_stock" id="pt_stock" value="0" class="form-control" readonly numberonly="" maxlength="10">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">개</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="infinity" name="pt_stock_chk" checked="" value="N" class="custom-control-input">
                                                <label class="custom-control-label" for="infinity">무한정 판매</label>
                                            </div>
                                        </div>
                                        </p>
                                    </div>
                                </div>
                                <script>
                                    $("input[name='pt_stock_chk']:radio").change(function () {
                                        if(this.value == "N") {
                                            $("#pt_stock").prop('readonly', true);
                                        } else {
                                            $("#pt_stock").prop('readonly', false);
                                        }
                                    });
                                </script>
                            </div>
							<?
								$pro_acc_iid = '6';
							?>
                            <div class="card">
                                <div class="card-header" id="pro_head_<?=$pro_acc_iid?>">
                                    <h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_<?=$pro_acc_iid?>" aria-expanded="false" aria-controls="pro_collapse_<?=$pro_acc_iid?>">옵션</a></h5>
                                </div>
                                <div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
                                    <div class="card-body">
                                        <p class="mb-0">
                                        <div class="text-right pr-2">
                                            <a data-toggle="popover" title="" data-html='true' data-trigger="hover" data-content="등록 상품의 사이즈, 색상 등 구매의 상세 조건을 설정할 수 있는 기능입니다.<br/>※ 참고.<br/>- 어떠한 경우에도 옵션을 통한 구매자의  개인정보 수집은 금지 행위에 포함 되며, 적발시 판매에 불이익이 발생 할 수 있습니다.<br/>- 등록 상품에서 다른 상품도 추가로 판매하길 원하는 경우에는, ‘옵션’이 아닌 하단 ‘추가구성상품’에서 설정이 가능합니다." data-original-title="'옵션'은 어떻게 등록하나요?"><i class="mdi mdi-help-rhombus-outline"></i></a>
                                        </div>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="pt_chk_use_option" class="col-sm-2 col-form-label">옵션사용 <b class="text-danger">*</b></label>
                                                    <div class="col-sm-10">
                                                        <input type="hidden" name="pt_option_chk" id="pt_option_chk" value="1" class="form-control" />
                                                        <div class="btn-group" role="group" aria-label="select_category">
                                                            <button type="button" onclick="f_pt_option_chk_1('2');" id="f_pt_option_chk2" class="btn btn-outline-secondary">설정함</button>
                                                            <button type="button" onclick="f_pt_option_chk_1('1');" id="f_pt_option_chk1" class="btn btn-outline-secondary btn-info text-white">설정안함</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item c_pt_option_chk1">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="pt_option_input_num" class="col-sm-2 col-form-label">옵션명 개수 <b class="text-danger">*</b></label>
                                                    <div class="col-sm-3">
                                                        <select name="pt_option_input_num" id="pt_option_input_num" class="custom-select" onchange="f_pt_option_input_num(this.value)">
                                                            <option value="1">1개</option>
                                                            <option value="2">2개</option>
                                                            <option value="3">3개</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item c_pt_option_chk1">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="pt_option_input_name" class="col-sm-2 col-form-label">옵션입력 <b class="text-danger">*</b></label>
                                                    <div class="col-sm-10">
                                                        <ul id="f_pt_option_input_box" class="pl-0">
                                                            <li class="row align-items-center">
                                                                <div class="col-sm-4">
                                                                    <p>옵션명1</p>
                                                                    <input type="text" name="pt_option_name1" id="pt_option_name1" value="" class="form-control form-control-sm" maxlength="50" placeholder="예)색상" />
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <p>옵션값1</p>
                                                                    <input type="text" name="pt_option_val1" id="pt_option_val1" value="" class="form-control form-control-sm" maxlength="100" placeholder="예)파랑,노랑,빨강 콤마로 구분해서 입력바랍니다." />
                                                                </div>
                                                            </li>
                                                        </ul>
                                                        <button type="button" onclick="f_product_option_list();" class="btn btn-info mt-4">목록에 적용</button>
                                                    </div>
                                                </div>
                                            </li>
                                            <input type="hidden" name="pt_option_type" id="pt_option_type" value="1" />
                                            <li class="list-group-item c_pt_option_chk1">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="pt_option_input_value" class="col-sm-2 col-form-label">옵션목록 <b class="text-danger">*</b></label>
                                                    <div class="col-sm-10" id="product_option_list_box"></div>
                                                </div>
                                            </li>
                                        </ul>
                                        </p>
                                    </div>
                                </div>
                            </div>
							<?
								$pro_acc_iid = '7';
							?>
                            <div class="card">
                                <div class="card-header" id="pro_head_<?=$pro_acc_iid?>">
                                    <h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_<?=$pro_acc_iid?>" aria-expanded="true" aria-controls="pro_collapse_<?=$pro_acc_iid?>" class="text-dark">배송비 <i class="mdi mdi-circle-medium text-danger"></i></a></h5>
                                </div>
                                <div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
                                    <div class="card-body">
                                        <p class="mb-0">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="col-sm-10">
                                                    <div class="custom-control custom-radio custom-control-inline">
                                                        <input type="radio" id="basic_delivery" name="pt_delivery_chk" value="N" class="custom-control-input">
                                                        <label class="custom-control-label" for="basic_delivery">기본배송정보</label>
                                                    </div>
                                                    <div class="custom-control custom-radio custom-control-inline">
                                                        <input type="radio" id="new_delivery" name="pt_delivery_chk" checked="" value="Y" class="custom-control-input">
                                                        <label class="custom-control-label" for="new_delivery">신규설정</label>
                                                    </div>
                                                </div>
                                            </li>
                                            <?
                                                $query = "select * from policy_t where idx = 1";
                                                $row_p = $DB->fetch_assoc($query);
                                            ?>
                                            <script>
                                                $("input[name='pt_delivery_chk']:radio").change(function () {
                                                    if(this.value == "N") {
                                                        $("#pt_delivery_price").val(<?=$row_p['pt_delivery_price']?>);
                                                        $("#pt_delivery_free_price").val(<?=$row_p['pt_free_delivery_price']?>);
                                                        $("#pt_delivery_exchange_price").val(<?=$row_p['pt_exchange_price']?>);
                                                        $("#pt_delivery_refund_price").val(<?=$row_p['pt_refund_price']?>);
                                                        if("<?=$row_p['pt_free_delivery_chk']?>" == "Y") {
                                                            $("#pt_delivery_free_chk").attr("checked", true);
                                                        }
                                                    } else {
                                                        $("#pt_delivery_price").val(0);
                                                        $("#pt_delivery_free_price").val(0);
                                                        $("#pt_delivery_refund_price").val(0);
                                                        $("#pt_delivery_exchange_price").val(0);
                                                    }
                                                });
                                            </script>
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="" class="col-sm-2 col-form-label">단건 <b class="text-danger">*</b></label>
                                                    <div class="col-sm-3">
                                                        <div class="input-group">
                                                            <input type="text" name="pt_delivery_price" id="pt_delivery_price" value="0" class="form-control" numberonly="" maxlength="10">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">원</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="custom-control custom-check custom-control-inline col-sm-5">
                                                        <div class="col-sm-2 col-form-label text-right">
                                                            <input type="checkbox" id="pt_delivery_free_chk" name="pt_delivery_free_chk" value="Y" class="custom-control-input">
                                                            <label class="custom-control-label" for="pt_delivery_free_chk">무료배송설정</label>
                                                        </div>
                                                        <div class="col-sm-8 mt-1">
                                                            <div class="form-group row align-items-center mb-0">
                                                                <div class="input-group">
                                                                    <input type="text" name="pt_delivery_free_price" id="pt_delivery_free_price" value="0" readonly class="form-control" numberonly="" maxlength="10">
                                                                    <div class="input-group-append">
                                                                        <span class="input-group-text">원</span>
                                                                    </div>
                                                                    <span class="col-form-label">&nbsp; 이상 구매 시 무료 배송</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <script>
                                                    $("#pt_delivery_free_chk").on("click", function() {
                                                        if($("#pt_delivery_free_chk").is(":checked") == false) {
                                                            $("#pt_delivery_free_price").attr("readonly", true);
                                                        } else {
                                                            $("#pt_delivery_free_price").attr("readonly", false);
                                                        }
                                                    });
                                                </script>
                                            </li>
                                        </ul>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="" class="col-sm-2 col-form-label">반품 <b class="text-danger">*</b></label>
                                                    <div class="col-sm-3">
                                                        <div class="input-group">
                                                            <input type="text" name="pt_delivery_refund_price" id="pt_delivery_refund_price" value="0" class="form-control" numberonly="" maxlength="10">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">원</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="" class="col-sm-2 col-form-label">교환 <b class="text-danger">*</b></label>
                                                    <div class="col-sm-3">
                                                        <div class="input-group">
                                                            <input type="text" name="pt_delivery_exchange_price" id="pt_delivery_exchange_price" value="0" class="form-control" numberonly="" maxlength="10">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">원</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?
                            $pro_acc_iid = '8';
                            ?>
                            <div class="card">
                                <div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="pt_show" class="col-sm-2 col-form-label">배송정보</label>
                                                    <div class="col-sm-10">
                                                        <textarea name="pt_delivery_comment" id="pt_delivery_comment" class="form-control form-control-sm" style="height: 200px;" placeholder="배송정보"><? if($_act == "input" || $row_pt['pt_delivery_comment'] == "") {
                                                                echo $row_p['pt_delivery_info'];
                                                            } else {
                                                                echo $row_pt['pt_delivery_comment'];
                                                            }
                                                            ?></textarea>
                                                        <script type="text/javascript">
                                                            CKEDITOR.replace('pt_delivery_comment', {
                                                                extraPlugins: 'uploadimage, image2',
                                                                height : '300px',
                                                                filebrowserImageBrowseUrl : '',
                                                                filebrowserImageUploadUrl : './file_upload.php?Type=Images&upload_name=pt_refund_comment',
                                                                enterMode : CKEDITOR.ENTER_BR,
                                                            });
                                                        </script>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="pt_show" class="col-sm-2 col-form-label">교환 및 반품정보</label>
                                                    <div class="col-sm-10">
                                                        <textarea name="pt_refund_comment" id="pt_refund_comment" class="form-control form-control-sm" placeholder="교환 및 반품정보"><? if($_act == "input" || $row_pt['pt_refund_comment'] == "") {
                                                                echo $row_p['pt_return_info'];
                                                            } else {
                                                                echo $row_pt['pt_refund_comment'];
                                                            }
                                                            ?></textarea>
                                                        <script type="text/javascript">
                                                            CKEDITOR.replace('pt_refund_comment', {
                                                                extraPlugins: 'uploadimage, image2',
                                                                height : '300px',
                                                                filebrowserImageBrowseUrl : '',
                                                                filebrowserImageUploadUrl : './file_upload.php?Type=Images&upload_name=pt_refund_comment',
                                                                enterMode : CKEDITOR.ENTER_BR,
                                                            });
                                                        </script>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="pt_show" class="col-sm-2 col-form-label">상태값설정</label>
                                                    <div class="col-sm-10">
                                                        <input type="hidden" name="pt_show" id="pt_show" value="<?php echo $row_pt['pt_show'];?>" />
                                                        <div class="btn-group" role="group" aria-label="pt_show">
                                                            <button type="button" onclick="f_pt_show('Y');" id="f_pt_show_btn1" class="btn btn-outline-secondary <?php if($row_pt['pt_show']=='Y' || $row_pt['pt_show']=='') echo 'btn-info text-white';?>">노출함</button>
                                                            <button type="button" onclick="f_pt_show('N');" id="f_pt_show_btn2" class="btn btn-outline-secondary <?php if($row_pt['pt_show']=='N') echo 'btn-info text-white';?>">노출안함</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="pt_sale_now" class="col-sm-2 col-form-label">판매상태</label>
                                                    <div class="col-sm-10">
                                                        <input type="hidden" name="pt_sale_now" id="pt_sale_now" value="<?php echo $row_pt['pt_sale_now'];?>" />
                                                        <div class="btn-group" role="group" aria-label="pt_sale_now">
                                                            <button type="button" onclick="f_pt_sale_now('Y');" id="f_pt_sale_now_btn1" class="btn btn-outline-secondary <?php if($row_pt['pt_sale_now']=='Y' || $row_pt['pt_sale_now']=='') echo 'btn-info text-white';?>">판매함</button>
                                                            <button type="button" onclick="f_pt_sale_now('N');" id="f_pt_sale_now_btn2" class="btn btn-outline-secondary <?php if($row_pt['pt_sale_now']=='N') echo 'btn-info text-white';?>">판매안함</button>
                                                            <button type="button" onclick="f_pt_sale_now('0');" id="f_pt_sale_now_btn3" class="btn btn-outline-secondary <?php if($row_pt['pt_sale_now']=='0') echo 'btn-info text-white';?>">품절</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                </div>
                            </div>
                            <?
                            $pro_acc_iid = '9';
                            ?>
                            <div class="card">
                                <div class="card-header" id="pro_head_<?=$pro_acc_iid?>">
                                    <h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_<?=$pro_acc_iid?>" aria-expanded="true" aria-controls="pro_collapse_<?=$pro_acc_iid?>" class="text-dark">상품정보 등록고시 </a></h5>
                                </div>
                                <div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
                                    <div class="card-body">
                                        <p class="mb-0">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="" class="col-sm-2 col-form-label">제조사 </label>
                                                    <div class="col-sm-3">
                                                        <div class="input-group">
                                                            <input type="text" name="" id="" value="" class="form-control" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <label for="" class="col-sm-2 col-form-label">제조업체 </label>
                                                    <div class="col-sm-3">
                                                        <div class="input-group">
                                                            <input type="text" name="" id="" value="" class="form-control" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                        </p>
                                    </div>
                                </div>
                            </div>
							<?
								$pro_acc_iid = '10';
							?>
							<div class="card">
								<div class="card-header" id="pro_head_<?=$pro_acc_iid?>">
									<h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_<?=$pro_acc_iid?>" aria-expanded="true" aria-controls="pro_collapse_<?=$pro_acc_iid?>" class="text-dark">상세설명 <i class="mdi mdi-circle-medium text-danger"></i></a></h5>
								</div>
								<div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
									<div class="card-body">
										<p class="mb-0">
											<textarea name="pt_content" id="pt_content" class="form-control form-control-sm"><?php echo $row_pt['pt_content']?></textarea>
											<script type="text/javascript">
												CKEDITOR.replace('pt_content', {
													extraPlugins: 'uploadimage, image2',
													height : '300px',
													filebrowserImageBrowseUrl : '',
													filebrowserImageUploadUrl : './file_upload.php?Type=Images&upload_name=pt_content',
													enterMode : CKEDITOR.ENTER_BR,
												});
											</script>

											<small id="select_category_help" class="form-text text-muted">
											상품명과 직접적 관련 없는 상세설명, 외부 링크 입력 시 관리자에 의해 판매 금지 될 수 있습니다.<br/>
											안전거래정책에 위배될 경우 관리자에 의해 제재조치가 있을 수 있습니다.<br/>
											네이버 이외의 외부링크, 일부 스크립트 및 태그는 자동 삭제될 수 있습니다.<br/>
											상세설명 권장 크기 : 가로 860px
											</small>
										</p>
									</div>
								</div>
							</div>
                            <?
                            $pro_acc_iid = '11';
                            ?>
                            <div class="card">
                                <div class="card-header" id="pro_head_<?=$pro_acc_iid?>">
                                    <h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_<?=$pro_acc_iid?>" aria-expanded="true" aria-controls="pro_collapse_<?=$pro_acc_iid?>" class="text-dark">랜덤/HOT/NEW 상품 등록 </a></h5>
                                </div>
                                <div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
                                    <div class="card-body">
                                        <p class="mb-0">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <div class="custom-control custom-checkbox custom-control-inline">
                                                        <input type="checkbox" id="pt_random_chk" name="pt_random_chk" value="Y" class="custom-control-input">
                                                        <label class="custom-control-label" for="pt_random_chk">랜덤 상품 등록</label>
                                                    </div>
                                                    <div class="form-group row align-items-center mb-0" id="effect_area">
                                                        <label id="pt_random_effect"></label>
                                                    </div>
                                                </div>

                                            </li>
                                        </ul>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <div class="custom-control custom-checkbox custom-control-inline">
                                                        <input type="checkbox" id="pt_best_chk" name="pt_best_chk" value="Y" <?if($row_pt['pt_best_chk'] == 'Y') echo 'checked';?> class="custom-control-input">
                                                        <label class="custom-control-label" for="pt_best_chk">HOT 상품 등록</label>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="form-group row align-items-center mb-0">
                                                    <div class="custom-control custom-checkbox custom-control-inline">
                                                        <input type="checkbox" id="pt_new_chk" name="pt_new_chk" value="Y" <?if($row_pt['pt_new_chk'] == 'Y') echo 'checked';?> class="custom-control-input">
                                                        <label class="custom-control-label" for="pt_new_chk">NEW 상품 등록</label>
                                                    </div>
                                                    <div id="new_area" class="form-group row align-items-center mb-0 <?if($row_pt['pt_new_chk'] == 'Y') echo ''; else echo "d-none";?>">
                                                        <div class="col-2">
                                                            <input type="file" name="pt_new_img" id="pt_new_img" value="<?=$row_pt['pt_new_img']?>" accept=".gif, .jpg, .jpeg, .png, .gif, .bmp" class="d-none" />
                                                            <input type="hidden" name="pt_new_img_on" id="pt_new_img_on" value="<?=$row_pt['pt_new_img']?>" class="form-control" />

                                                            <label for="pt_new_img" class="plus-input" id="pt_new_img_box" style="width: 430px;">
                                                                <? if($row_pt['pt_new_img'] == "") { ?>
                                                                    <i class="mdi mdi-plus"></i>
                                                                <? } else { ?>
                                                                    <img src="<?=$ct_img_url."/".$row_pt['pt_new_img']?>" />
                                                                <? } ?>
                                                            </label>
                                                        </div>
                                                        <div class="custom-control custom-control-inline">
                                                            <input type="text" id="pt_new_url" name="pt_new_url" value="<?=$row_pt['pt_new_url']?>" class="form-control col-10 mr-3" placeholder="url을 입력해주세요.">
                                                            <select id="YEAR" name="YEAR" class="form-control col-3 mr-3"></select>
                                                            <select id="MONTH" name="MONTH" class="form-control col-3 mr-3"></select>
                                                            <select id="DAY" name="DAY" class="form-control col-3 mr-3"></select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <script type="text/javascript">
                                                    $('#pt_new_img').on('change', function(e) {
                                                        var target_id = e.target.id;
                                                        var files = e.target.files;
                                                        var filesArr = Array.prototype.slice.call(files);

                                                        if(filesArr.lengths>10) {
                                                            alert("추가이미지는 최대 10개까지 가능합니다.");
                                                            return;
                                                        } else {
                                                            filesArr.forEach(function(f) {
                                                                if(!f.type.match("image.*")) {
                                                                    alert("확장자는 이미지 확장자만 가능합니다.");
                                                                    return;
                                                                }

                                                                sel_files.push(f);

                                                                var reader = new FileReader();
                                                                reader.onload = function(e) {
                                                                    $("#"+target_id+"_box").css('border', 'none');
                                                                    $("#"+target_id+"_box").html('<img src="'+e.target.result+'" />');
                                                                }
                                                                reader.readAsDataURL(f);
                                                            });
                                                        }
                                                    });

                                                </script>
                                            </li>
                                        </ul>
                                        </p>
                                    </div>
                                </div>
							
							<?
								$pro_acc_iid = '16';
							?>
<!--							<div class="card">-->
<!--								<div class="card-header" id="pro_head_--><?//=$pro_acc_iid?><!--">-->
<!--									<h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_--><?//=$pro_acc_iid?><!--" aria-expanded="false" aria-controls="pro_collapse_--><?//=$pro_acc_iid?><!--">검색설정</a></h5>-->
<!--								</div>-->
<!--								<div id="pro_collapse_--><?//=$pro_acc_iid?><!--" class="collapse show" aria-labelledby="pro_head_--><?//=$pro_acc_iid?><!--" data-parent="#pro_accordion">-->
<!--									<div class="card-body">-->
<!--										<p class="mb-0">-->
<!--											<div class="text-right pr-2">-->
<!--												<a data-toggle="popover" title="" data-html='true' data-trigger="hover" data-content="검색설정 등록하기 내용" data-original-title="검색설정 등록하기 제목"><i class="mdi mdi-help-rhombus-outline"></i></a>-->
<!--											</div>-->
<!--											<ul class="list-group list-group-flush">-->
<!--												<li class="list-group-item">-->
<!--													<div class="form-group row align-items-center mb-0">-->
<!--														<label for="psct_tag" class="col-sm-2 col-form-label">태그</label>-->
<!--														<div class="col-sm-10">-->
<!--															<input type="text" name="psct_tag" id="psct_tag" value="--><?//=$row['psct_tag']?><!--" placeholder=", 콤마로 구분하여 입력바랍니다." class="form-control form-control-sm" />-->
<!--														</div>-->
<!--													</div>-->
<!--												</li>-->
<!--												<li class="list-group-item">-->
<!--													<div class="form-group row align-items-center mb-0">-->
<!--														<label for="psct_page_title" class="col-sm-2 col-form-label">Page Title</label>-->
<!--														<div class="col-sm-10">-->
<!--															<input type="text" name="psct_page_title" id="psct_page_title" value="--><?//=$row['psct_page_title']?><!--" class="form-control form-control-sm" />-->
<!--														</div>-->
<!--													</div>-->
<!--												</li>-->
<!--												<li class="list-group-item">-->
<!--													<div class="form-group row align-items-center mb-0">-->
<!--														<label for="psct_meta_description" class="col-sm-2 col-form-label">Meta description</label>-->
<!--														<div class="col-sm-10">-->
<!--															<input type="text" name="psct_meta_description" id="psct_meta_description" value="--><?//=$row['psct_meta_description']?><!--" class="form-control form-control-sm" />-->
<!--														</div>-->
<!--													</div>-->
<!--												</li>-->
<!--											</ul>-->
<!--										</p>-->
<!--									</div>-->
<!--								</div>-->
<!--							</div>-->
						</div>
					</div>

					<div class="fixed-bottom product_form">
						<p class="p-3 mt-3 text-center">
							<!-- <input type="button" value="미리보기" onclick="location.href='./product_list.php?<?=$_get_txt?>'" class="btn btn-outline-secondary" />
							<input type="button" value="임시저장" onclick="location.href='./product_list.php?<?=$_get_txt?>'" class="btn btn-secondary mx-2" /> -->
							<input type="submit" value="<?=$_act_txt?>" class="btn btn-info" />
							<input type="button" value="목록" onclick="location.href='./product_list.php?<?=$_get_txt?>'" class="btn btn-outline-secondary mx-2" />
						</p>
					</div>

					</form>
					<script type="text/javascript">
						$('#mem_tab a').on('click', function (e) {
							e.preventDefault()
							$(this).tab('show')
						});

						(function($) {
							'use strict';
							$(function() {
								<? if($_act=='update') { ?>
								f_update_product_info('update', '<?=$_GET['pt_idx']?>');
								<? } else if($_act=='input') { ?>
								f_update_template_info('<?=$_GET['ptl_idx']?>');
								<? } ?>

								jQuery.datetimepicker.setLocale('ko');

								jQuery(function () {
									jQuery('#pt_selling_sdate').datetimepicker({
										format: 'Y-m-d',
										onShow: function (ct) {
											this.setOptions({
												maxDate: jQuery('#pt_selling_edate').val() ? jQuery('#pt_selling_edate').val() : false
											})
										},
										timepicker: false
									});
									jQuery('#pt_selling_edate').datetimepicker({
										format: 'Y-m-d',
										onShow: function (ct) {
											this.setOptions({
												minDate: jQuery('#pt_selling_sdate').val() ? jQuery('#pt_selling_sdate').val() : false
											})
										},
										timepicker: false
									});
									jQuery('#pat_jejo_date, #pat_valid_date').datetimepicker({
										format: 'Y-m-d',
										timepicker: false
									});
									jQuery('#ppbt_multi_pay_sale_sdate').datetimepicker({
										format: 'Y-m-d',
										onShow: function (ct) {
											this.setOptions({
												maxDate: jQuery('#ppbt_multi_pay_sale_edate').val() ? jQuery('#ppbt_multi_pay_sale_edate').val() : false
											})
										},
										timepicker: false
									});
									jQuery('#ppbt_multi_pay_sale_edate').datetimepicker({
										format: 'Y-m-d',
										onShow: function (ct) {
											this.setOptions({
												minDate: jQuery('#ppbt_multi_pay_sale_sdate').val() ? jQuery('#ppbt_multi_pay_sale_sdate').val() : false
											})
										},
										timepicker: false
									});
									jQuery('#ppbt_nointerest_sdate').datetimepicker({
										format: 'Y-m-d',
										onShow: function (ct) {
											this.setOptions({
												maxDate: jQuery('#ppbt_nointerest_edate').val() ? jQuery('#ppbt_nointerest_edate').val() : false
											})
										},
										timepicker: false
									});
									jQuery('#ppbt_nointerest_edate').datetimepicker({
										format: 'Y-m-d',
										onShow: function (ct) {
											this.setOptions({
												minDate: jQuery('#ppbt_nointerest_sdate').val() ? jQuery('#ppbt_nointerest_sdate').val() : false
											})
										},
										timepicker: false
									});
								});

								// $('#pt_image1').on('change', preview_image_sigle_selected);
                                setDateBox();
                                var date = "<?=$row_pt['pt_new_date']?>";
                                if(date != "0000-00-00" || date != "" || date != null) {
                                    date = date.split("-");
                                    $("#YEAR").val(date[0]).prop("selected", true);
                                    $("#MONTH").val(date[1]).prop("selected", true);
                                    $("#DAY").val(date[2]).prop("selected", true);
                                } else {
                                    $("#YEAR").val('').prop("selected", true);
                                    $("#MONTH").val('').prop("selected", true);
                                    $("#DAY").val('').prop("selected", true);
                                }
							});
						})(jQuery);

						function frm_form_chk(f) {
							var oEditor = CKEDITOR.instances.pt_content;

							var pct_id_cnt = 0;
							$('select[name="pct_idx"] option:selected').each(function() {
                                if($(this).val()!=""){
									pct_id_cnt += 1;
								}
							});
							if(pct_id_cnt==0) {
								alert("대분류를 선택해주세요.");
								return false;
							}
                            var pct_id_cnt2 = 0;
                            $('select[name="pct_m_idx"] option:selected').each(function() {
                                if($(this).val()!=""){
                                    pct_id_cnt2 += 1;
                                }
                            });
                            if(pct_id_cnt2==0) {
                                alert("중분류를 선택해주세요.");
                                return false;
                            }
                            if(f.pt_title.value=="") {
                                alert("상품명을 등록해주세요.");
                                f.pt_title.focus();
                                return false;
                            }
                            var cnt = 0;
                            for(var i=1; i<=10; i++) {
                                if($("#pt_image"+i).val() != "" || $("#pt_image"+i+"_on").val() != "") {
                                    cnt++;
                                }
                            }
							if(cnt < 1) {
								alert("이미지를 등록해주세요.");
								f.pt_image1.focus();
								return false;
							}
                            if(f.pt_selling_price.value=="") {
                                alert("정상가를 등록해주세요.");
                                f.pt_selling_price.focus();
                                return false;
                            }
							if(f.pt_price.value=="") {
								alert("판매가를 등록해주세요.");
								f.pt_price.focus();
								return false;
							}
                            if($("input[name='pt_stock_chk']:checked").val() == "Y") {
                                if(f.pt_stock.value=="") {
                                    alert("재고수량을 등록해주세요.");
                                    f.pt_stock.focus();
                                    return false;
                                }
                            }

							//옵션
							if(f.pt_option_chk.value=="2") {
								if(f.pt_option_name1.value=="") {
									alert("옵션을 1개이상 등록바랍니다.");
									f.pt_option_name1.focus();
									return false;
								}

								// var pt_option_type_checked = '';
								// $('input:radio[name="pt_option_type"]').each(function() {
								// 	if($(this).prop('checked')==true) {
								// 		pt_option_type_checked = $(this).val();
								// 	}
								// });

								if(f.pt_option_type.value==2) {
									var pot_name_cnt = 0;
									$('input[name="pot_name1[]"]').each(function() {
										console.log($(this).val());
										if($(this).val()) {
											pot_name_cnt += 1;
										}
									});
									console.log(pot_name_cnt);
									if(pot_name_cnt<1) {
										alert("옵션목록을 적용하고 등록바랍니다.");
										f.pt_option_name1.focus();
										return false;
									}

									var pot_jaego_t = 0;
									$('input[name="pot_jaego[]"]').each(function() {
										pot_jaego_t += Number($(this).val());
									});

									if(pot_jaego_t<1) {
										alert("옵션별 재고를 확인바랍니다. 최소 1개이상의 옵션에 재고가 있어야 합니다.");
										$('#pot_jaego1').focus();
										return false;
									}
								} else {
									var pot_name_cnt = 0;
									$('input[name="pot_name[]"]').each(function() {
										console.log($(this).val());
										if($(this).val()) {
											pot_name_cnt += 1;
										}
									});
									if(pot_name_cnt<1) {
										alert("옵션목록을 적용하고 등록바랍니다.");
										f.pt_option_name1.focus();
										return false;
									}
								}
							}
							
							if(f.pt_delivery_chk.value=='Y') {
								if(f.pt_delivery_price.value<1) {
									alert("단건 배송비를 등록해주세요.");
									f.pt_delivery_price.focus();
									return false;
								} else if(f.pt_delivery_refund_price.value<1) {
                                    alert("반품 배송비를 등록해주세요.");
                                    f.pt_delivery_refund_price.focus();
                                    return false;
                                } else if(f.pt_delivery_exchange_price.value<1) {
                                    alert("교환 배송비를 등록해주세요.");
                                    f.pt_delivery_exchange_price.focus();
                                    return false;
                                } else if($("#pt_delivery_free_chk").is(":checked") == true) {
                                    if(f.pt_delivery_free_price.value<1) {
                                        alert("무료 배송비를 등록해주세요.");
                                        f.pt_delivery_free_price.focus();
                                        return false;
                                    }
                                }
							}

                            if(oEditor.getData()=="") {
                                alert("상품설명을 등록해주세요.");
                                oEditor.focus();
                                return false;
                            }

							$('#splinner_modal').modal('show');
						}
                        function chk_sale() {
                            if($("#pt_selling_price").val() != "" && $("#pt_price").val() != "") {
                                var pt_selling_price = $("#pt_selling_price").val();
                                var pt_price = $("#pt_price").val();
                                var sale = pt_selling_price - pt_price;
                                sale = (sale / pt_selling_price) * 100;
                                $("#pt_discount_per").val(Math.round(sale));
                            } else {
                                $("#pt_discount_per").val(0);
                            }
                        }
                        $("#pt_new_chk").on("click", function() {
                            if($("#pt_new_chk").is(":checked")) {
                                $("#new_area").removeClass("d-none");
                                $("#YEAR").val('').prop("selected", true);
                                $("#MONTH").val('').prop("selected", true);
                                $("#DAY").val('').prop("selected", true);
                            } else {
                                $("#new_area").addClass("d-none");
                            }
                        });
                        function setDateBox(){
                            var dt = new Date();
                            var com_year = dt.getFullYear();
                            $("#YEAR").append("<option value='' selected=''>년</option>");
                            // 올해 기준으로 -1년부터 +5년을 보여준다.
                            for(var y = (com_year+5); y >= (com_year-1); y--){
                                $("#YEAR").append("<option value='"+ y +"'>"+ y + "년" +"</option>");
                            }
                            $("#MONTH").append("<option value='' selected=''>월</option>");
                            for(var i = 1; i <= 12; i++){
                                $("#MONTH").append("<option value='"+ (("00"+i.toString()).slice(-2)) +"'>"+ i + "월" +"</option>");
                            }
                            $("#DAY").append("<option value='' selected=''>일</option>");
                            for(var i = 1; i <= 31; i++){
                                $("#DAY").append("<option value='"+ (("00"+i.toString()).slice(-2)) +"'>"+ i + "일" +"</option>");
                            }
                        }
					</script>
				</div>
			</div>
		</div>
	</div>
</div>
<style type="text/css">
	.footer {
		margin-bottom:96px;
	}
</style>
<script>
    function send_push() {
        $('#splinner_modal').modal('show');
        $.ajax({
            type: 'post',
            url: './product_update.php',
            dataType: 'json',
            data: {act: 'send_push', idx: "<?=$_GET['pt_idx']?>"},
            success: function (d, s) {
                if(d['result'] == "_ok") {
                    alert(d['msg']);
                    location.reload();
                }
            },
            cache: false
        });
    }
    function del_img(id, num) {
        if(confirm("이미지를 삭제하시겠습니까?\n※삭제한 이미지는 복구할 수 없습니다.")) {
            var file_name = id.substring(0,10);
            $.ajax({
                type: 'post',
                url: './product_update.php',
                dataType: 'json',
                data: {act: 'del_img', idx: $("#pt_idx").val(), name: $(file_name+"_on").val(), num: num},
                success: function (d, s) {
                    if(d['result'] == "_ok") {
                        alert(d['msg']);
                        location.reload();
                    }
                },
                cache: false
            });
        }
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
    function del_img2(e) {
        var id = e.id;
        $("#"+id).val("");
        $("#"+id+"_box").css('border', '');
        $("#"+id+"_box").html('<i class="mdi mdi-plus" style="line-height: 90px;"></i>');
        setTimeout(function() {
            $("#"+id+"_box").attr("for", id);
        }, 1000);
    }
</script>
<?
	include "./foot_inc.php";
?>