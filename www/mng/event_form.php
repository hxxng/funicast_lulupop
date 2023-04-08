<?
	include "./head_inc.php";
	$chk_menu = "6";
	$chk_sub_menu = "1";
	$chk_post_code = 'Y';
	$chk_ckeditor = 'Y';
	include "./head_menu_inc.php";

	if($_GET['act']=="update") {
		$query_pt = "
			select *, a1.idx as et_idx from event_t a1
			where a1.idx = '".$_GET['et_idx']."'
		";
		$row = $DB->fetch_query($query_pt);

		$_act = "update";
		$_act_txt = "수정";
	} else {
		$_act = "input";
		$_act_txt = "추가";
	}

	$_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&pt_sale_now=".$_GET['pt_sale_now']."&pg=".$_GET['pg'];
?>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">이벤트 <?=$_act_txt?></h4>

					<form method="post" name="frm_form" id="frm_form" action="./event_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm" enctype="multipart/form-data">
					<input type="hidden" name="act" id="act" value="<?=$_act?>" />
					<input type="hidden" name="et_idx" id="et_idx" value="<?=$_GET['et_idx']?>" />

					<div class="faq-section">
						<div id="pro_accordion" class="accordion acc_card">
							<?
								$pro_acc_iid = '1';
							?>
                            <div class="card">
                                <div class="card-header" id="pro_head_<?=$pro_acc_iid?>">
                                    <h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_<?=$pro_acc_iid?>" aria-expanded="true" aria-controls="pro_collapse_<?=$pro_acc_iid?>" class="text-dark">노출 <i class="mdi mdi-circle-medium text-danger"></i></a></h5>
                                </div>
                                <div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
                                    <div class="card-body">
                                        <p class="mb-0">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="show" name="et_status" value="1" <?if($row['et_status'] == 1 || $_act == "input") echo 'checked=""';?> class="custom-control-input">
                                            <label class="custom-control-label" for="show">노출</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="hide" name="et_status" value="2" <?if($row['et_status'] == 2) echo 'checked=""';?> class="custom-control-input">
                                            <label class="custom-control-label" for="hide">비노출</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?
								$pro_acc_iid = '2';
							?>
							<div class="card">
								<div class="card-header" id="pro_head_<?=$pro_acc_iid?>">
									<h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_<?=$pro_acc_iid?>" aria-expanded="true" aria-controls="pro_collapse_<?=$pro_acc_iid?>" class="text-dark">제목 <i class="mdi mdi-circle-medium text-danger"></i></a></h5>
								</div>
								<div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
									<div class="card-body">
										<p class="mb-0">
											<input type="text" name="et_title" id="et_title" value="<?php echo $row['et_title'];?>" maxlength="100" class="form-control form-control-sm" />
										</p>
									</div>
								</div>
							</div>
                            <?
                            $pro_acc_iid = '3';
                            ?>
<!--                            <div class="card">-->
<!--                                <div class="card-header" id="pro_head_--><?//=$pro_acc_iid?><!--">-->
<!--                                    <h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_--><?//=$pro_acc_iid?><!--" aria-expanded="true" aria-controls="pro_collapse_--><?//=$pro_acc_iid?><!--" class="text-dark">연결링크 <i class="mdi mdi-circle-medium text-danger"></i></a></h5>-->
<!--                                </div>-->
<!--                                <div id="pro_collapse_--><?//=$pro_acc_iid?><!--" class="collapse show" aria-labelledby="pro_head_--><?//=$pro_acc_iid?><!--" data-parent="#pro_accordion">-->
<!--                                    <div class="card-body">-->
<!--                                        <p class="mb-0">-->
<!--                                            <input type="text" name="et_url" id="et_url" value="--><?php //echo $row['et_url'];?><!--" maxlength="100" class="form-control form-control-sm" />-->
<!--                                        </p>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->
							<?
								$pro_acc_iid = '4';
							?>
                            <div class="card">
                                <div class="card-header" id="pro_head_<?=$pro_acc_iid?>">
                                    <h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_<?=$pro_acc_iid?>" aria-expanded="true" aria-controls="pro_collapse_<?=$pro_acc_iid?>" class="text-dark">대표사진 <i class="mdi mdi-circle-medium text-danger"></i></a></h5>
                                </div>
                                <div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
                                    <div class="card-body">
                                        <li class="list-group-item" style="border: none;">
                                            <div class="form-group row align-items-center mb-0">
                                                <div class="col-sm-10">
                                                    <input type="file" name="et_img" id="et_img" value="<?=$row['et_img']?>" accept=".gif, .jpg, .jpeg, .png, .gif, .bmp" class="d-none" />
                                                    <input type="hidden" name="et_img_on" id="et_img_on" value="<?=$row['et_img']?>" class="form-control" />

                                                    <label for="et_img" class="plus-input-small" id="et_img_box" style="width: 150px; height: 150px;border-radius: 5px;<? if($row['et_img']) echo 'border:none;'?>">
                                                        <? if($row['et_img'] == "") { ?>
                                                            <i class="mdi mdi-plus" style="line-height: 150px;"></i>
                                                        <? } else { ?>
                                                            <img src="<?=$ct_img_url."/".$row['et_img']?>" style="width: 150px; height: 150px;border-radius: 5px;"/>
                                                        <? } ?>
                                                    </label>
                                                    <script type="text/javascript">
                                                        $('#et_img').on('change', function(e) {
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
                                                                        $("#"+target_id+"_box").html('<img src="'+e.target.result+'" style="width: 150px; height: 150px;border-radius: 5px;"/>');
                                                                    }
                                                                    reader.readAsDataURL(f);
                                                                });
                                                            }
                                                        });
                                                    </script>
                                                </div>
                                            </div>
                                        </li>
                                        </p>
                                    </div>
                                </div>
                            </div>
							<?
								$pro_acc_iid = '5';
							?>
                            <div class="card">
                                <div class="card-header" id="pro_head_<?=$pro_acc_iid?>">
                                    <h5 class="mb-0"><a data-toggle="collapse" data-target="#pro_collapse_<?=$pro_acc_iid?>" aria-expanded="true" aria-controls="pro_collapse_<?=$pro_acc_iid?>" class="text-dark">노출날짜설정 <i class="mdi mdi-circle-medium text-danger"></i></a></h5>
                                </div>
                                <div id="pro_collapse_<?=$pro_acc_iid?>" class="collapse show" aria-labelledby="pro_head_<?=$pro_acc_iid?>" data-parent="#pro_accordion">
                                    <div class="card-body">
                                        <p class="mb-0">
                                        <div class="col-sm-10">
                                            <div class="form-group row align-items-center mb-0">
                                                <div class="col-sm-4">
                                                    <div class="input-group">
                                                        <input type="date" name="et_sdate" id="et_sdate" value="<?=$row['et_sdate']?>" class="form-control datepicker" />
                                                        <span class="m-2">부터 </span>
                                                        <input type="date" name="et_edate" id="et_edate" value="<?=$row['et_edate']?>" class="form-control ml-4 datepicker" />
                                                        <span class="m-2">까지 </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
											<textarea name="et_content" id="et_content" class="form-control form-control-sm"><?php echo $row['et_content']?></textarea>
											<script type="text/javascript">
												CKEDITOR.replace('et_content', {
													extraPlugins: 'uploadimage, image2',
													height : '300px',
													filebrowserImageBrowseUrl : '',
													filebrowserImageUploadUrl : './file_upload.php?Type=Images&upload_name=et_content',
													enterMode : CKEDITOR.ENTER_BR,
												});
											</script>
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="fixed-bottom product_form">
						<p class="p-3 mt-3 text-center">
							<input type="submit" value="<?=$_act_txt?>" class="btn btn-info" />
							<input type="button" value="목록" onclick="location.href='./event_list.php?<?=$_get_txt?>'" class="btn btn-outline-secondary mx-2" />
						</p>
					</div>

					</form>
					<script type="text/javascript">
						$('#mem_tab a').on('click', function (e) {
							e.preventDefault()
							$(this).tab('show')
						});

						function frm_form_chk(f) {
							var oEditor = CKEDITOR.instances.pt_content;

                            if(f.et_title.value=="") {
                                alert("제목을 등록해주세요.");
                                f.et_title.focus();
                                return false;
                            }
                            if(f.et_url.value=="") {
                                alert("url을 등록해주세요.");
                                f.et_url.focus();
                                return false;
                            }
							if(f.et_img.value=="" && f.et_img_on.value=="") {
								alert("이미지를 등록해주세요.");
								f.et_img.focus();
								return false;
							}
                            if(f.et_sdate.value=="") {
                                alert("노출 시작날짜를 등록해주세요.");
                                f.et_sdate.focus();
                                return false;
                            }
                            if(f.et_edate.value=="") {
                                alert("노출 종료날짜를 등록해주세요.");
                                f.et_edate.focus();
                                return false;
                            }
                            if(oEditor.getData()=="") {
                                alert("상품설명을 등록해주세요.");
                                oEditor.focus();
                                return false;
                            }

							$('#splinner_modal').modal('show');
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
<?
	include "./foot_inc.php";
?>