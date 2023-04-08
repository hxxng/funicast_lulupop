<?
	include "./head_inc.php";
	$chk_menu = '1';
	$chk_sub_menu = '0';
	include "./head_menu_inc.php";
?>
<!-- 메인 시작 -->
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <form method="post" name="frm_form" id="frm_form" action="./main_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm" enctype="multipart/form-data">
                        <input type="hidden" name="act" id="act" value="main" />
                    <h4 class="card-title">메인 비주얼</h4>
                    <?
                    $query = "select * from main_visual_t where idx = 1";
                    $row = $DB->fetch_assoc($query);
                    ?>
                    <div class="col-sm-12 custom-control">
                        <div class="form-group row align-items-center mb-0">
                            <div class="col-sm-12">
                                <div class="row">
                                <? for($q=1;$q<=6;$q++) { ?>
                                    <div class="col-4 mb-2">
                                        <input type="file" name="mvt_img<?=$q?>" id="mvt_img<?=$q?>" value="<?=$row['mvt_img'.$q]?>" accept=".gif, .jpg, .jpeg, .png, .gif, .bmp" class="d-none" />
                                        <input type="hidden" name="mvt_img<?=$q?>_on" id="mvt_img<?=$q?>_on" value="<?=$row['mvt_img'.$q]?>" class="form-control" />

                                        <label for="mvt_img<?=$q?>" class="plus-input" id="mvt_img<?=$q?>_box" style="width: 390px;<? if($row['mvt_img'.$q] != "") echo 'border:none;'; ?>">
                                            <? if($row['mvt_img'.$q] == "") { ?>
                                                <i class="mdi mdi-plus"></i>
                                            <? } else { ?>
                                            <img src="<?=$ct_img_url."/".$row['mvt_img'.$q]?>?<?=time()?>" style="width: 100%;border-radius: 10px;" />
                                            <? } ?>
                                        </label>
                                        <div class="row col-10 mt-1">
                                            <input class="form-control col-sm-10" id="mvt_url<?=$q?>" name="mvt_url<?=$q?>" value="<?=$row['mvt_url'.$q]?>" placeholder="url을 입력해주세요"/>
                                            <input type="button" class="btn btn-outline-primary btn-sm col-sm-2" value="삭제" onclick="delete_img('mvt_img<?=$q?>', '<?=$row['mvt_img'.$q]?>','main')">
                                        </div>
                                    </div>
                                    <script type="text/javascript">
                                        $('#mvt_img<?=$q?>').on('change', function(e) {
                                            //preview_image_multi_selected(e, '<?//=$q?>//');
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
                                                        $("#"+target_id+"_box").html('<img src="'+e.target.result+'" style="width: 100%;border-radius: 10px;" />');
                                                    }
                                                    reader.readAsDataURL(f);
                                                });
                                            }
                                        });
                                    </script>
                                <? } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="mt-4 text-center">
                        <input type="submit" value="저장" class="btn btn-info" />
                    </p>
                </form>
                </div>
            </div>
        </div>
    </div>
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">HOT한 아이템</h4>
					<p class="card-description">
						<table class="table">
						<tbody>
						<tr style="background-color: #f3f3f3;">
							<td class="text-center" style="width:80%;">
								상품명
							</td>
							<td class="text-center">
								선택
							</td>
                            <td class="text-center">
                                <input type="button" class="btn btn-outline-secondary btn-sm" value="추가" onclick="pop_up()" />
                            </td>
						</tr>
						<?
							unset($list);
							$query = "
								select *, a1.idx as pt_idx from product_t a1 where pt_best_chk = 'Y' and pt_show = 'Y' and pt_sale_now = 'Y' order by idx desc
							";
							$list = $DB->select_query($query);

							if($list) {
								foreach($list as $row) {
						?>
						<tr>
							<td class="text-center">
                                <?=$row['pt_title']?>
							</td>
                            <td class="text-center">
                                <input type="button" class="btn btn-outline-secondary btn-sm" value="삭제" onclick="delete_product('<?=$row['pt_idx']?>')" />
                            </td>
                            <td class="text-center">
                                <input type="button" class="btn btn-outline-secondary btn-sm" value="추가" onclick="pop_up()" />
                            </td>
						</tr>
						<?
								}
							} else {
						?>
						<tr>
							<td colspan="5" class="text-center"><b>자료가 없습니다.</b></td>
						</tr>
						<?
							}
						?>
                        </tbody>
						</table>
					</p>
				</div>
			</div>
		</div>
	</div>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <form method="post" name="frm_form" id="frm_form" action="./main_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm" enctype="multipart/form-data">
                        <input type="hidden" name="act" id="act" value="new" />
                    <h4 class="card-title">NEW 룰루팝</h4>
                    <?
                    $query = "select * from product_t where pt_new_chk = 'Y' and pt_show = 'Y' and (pt_sale_now = 'Y' or pt_sale_now = 'N') order by idx desc";
                    $row = $DB->select_query($query);
                    ?>
                    <div class="col-sm-12 custom-control">
                        <div class="form-group row align-items-center mb-0">
                            <div class="col-sm-12">
                                <div class="row">
                                    <? for($q=1;$q<=6;$q++) { ?>
                                        <div class="col-12 mb-2 row">
                                            <div class="col-2">
                                                <input type="file" name="pt_new_img<?=$q?>" id="pt_new_img<?=$q?>" value="<?=$row[$q-1]['pt_new_img']?>" accept=".gif, .jpg, .jpeg, .png, .gif, .bmp" class="d-none" />
                                                <input type="hidden" name="pt_new_img<?=$q?>_on" id="pt_new_img<?=$q?>_on" value="<?=$row[$q-1]['pt_new_img']?>" class="form-control" />
                                                <input type="hidden" name="pt_idx<?=$q?>" id="pt_idx<?=$q?>" value="<?=$row[$q-1]['idx']?>" class="form-control" />

                                                <label for="pt_new_img<?=$q?>" class="plus-input" id="pt_new_img<?=$q?>_box" style="width: 430px;border-radius: 10px;<? if($row[$q-1]['pt_new_img'] != "") echo 'border:none;'; ?>">
                                                    <? if($row[$q-1]['pt_new_img'] == "") { ?>
                                                        <i class="mdi mdi-plus"></i>
                                                    <? } else { ?>
                                                        <img src="<?=$ct_img_url."/".$row[$q-1]['pt_new_img']?>" style="border-radius: 10px;"/>
                                                    <? } ?>
                                                </label>
                                            </div>
                                            <div class="col-4 row align-items-center">
                                                <input class="form-control mr-2" id="pt_title<?=$q?>" name="pt_title<?=$q?>" readonly value="<?=$row[$q-1]['pt_title']?>" placeholder="상품명을 입력해주세요"/>
                                                <select id="YEAR<?=$q?>" name="YEAR<?=$q?>" class="form-control col-3 mr-3"></select>
                                                <select id="MONTH<?=$q?>" name="MONTH<?=$q?>" class="form-control col-3 mr-3"></select>
                                                <select id="DAY<?=$q?>" name="DAY<?=$q?>" class="form-control col-3 mr-3"></select>
                                            </div>
                                            <div class="col-5 row">
                                                <input class="form-control col-8 mr-2" style="margin-top: 12px;" id="pt_new_url<?=$q?>" name="pt_new_url<?=$q?>" value="<?=$row[$q-1]['pt_new_url']?>" placeholder="url을 입력해주세요"/>
                                                <input type="button" class="btn btn-outline-primary btn-sm mr-2" style="margin-top: 20px;height: 30px;" value="연결상품" onclick="pop_up2('<?=$q?>')">
                                                <input type="button" class="btn btn-outline-danger btn-sm" style="margin-top: 20px;height: 30px;" value="삭제" onclick="delete_new_img('<?=$row[$q-1]['idx']?>', '<?=$row[$q-1]['pt_new_img']?>')">
                                            </div>
                                        </div>
                                        <script type="text/javascript">
                                            function setDateBox(){
                                                var dt = new Date();
                                                var com_year = dt.getFullYear();
                                                $("#YEAR<?=$q?>").append("<option value='' hidden=''>년</option>");
                                                // 올해 기준으로 -1년부터 +5년을 보여준다.
                                                for(var y = (com_year+5); y >= (com_year-1); y--){
                                                    $("#YEAR<?=$q?>").append("<option value='"+ y +"'>"+ y + "년" +"</option>");
                                                }
                                                $("#MONTH<?=$q?>").append("<option value='' hidden=''>월</option>");
                                                for(var i = 1; i <= 12; i++){
                                                    $("#MONTH<?=$q?>").append("<option value='"+ (("00"+i.toString()).slice(-2)) +"'>"+ i + "월" +"</option>");
                                                }
                                                $("#DAY<?=$q?>").append("<option value='' hidden=''>일</option>");
                                                for(var i = 1; i <= 31; i++){
                                                    $("#DAY<?=$q?>").append("<option value='"+ (("00"+i.toString()).slice(-2)) +"'>"+ i + "일" +"</option>");
                                                }
                                            }
                                            $('#pt_new_img<?=$q?>').on('change', function(e) {
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
                                            setDateBox();
                                            var date = "<?=$row[$q-1]['pt_new_date']?>";
                                            date = date.split("-");
                                            $("#YEAR<?=$q?>").val(date[0]).prop("selected", true);
                                            $("#MONTH<?=$q?>").val(date[1]).prop("selected", true);
                                            $("#DAY<?=$q?>").val(date[2]).prop("selected", true);
                                        </script>
                                    <? } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="mt-4 text-center">
                        <input type="submit" value="저장" class="btn btn-info" />
                    </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <form method="post" name="frm_form" id="frm_form" action="./main_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm" enctype="multipart/form-data">
                        <input type="hidden" name="act" id="act" value="movies" />
                    <h4 class="card-title">룰루팝 MOVIES</h4>
                    <?
                    $query = "select * from main_visual_t where idx = 1";
                    $row = $DB->fetch_assoc($query);
                    ?>
                    <div class="col-sm-12 custom-control">
                        <div class="form-group row align-items-center mb-0">
                            <div class="col-sm-12">
                                <div class="row">
                                    <? for($q=1;$q<=3;$q++) { ?>
                                        <div class="col-12 mb-2 row">
                                            <div class="col-2">
                                                <input type="file" name="mvt_movies_img<?=$q?>" id="mvt_movies_img<?=$q?>" value="<?=$row['mvt_movies_img'.$q]?>" accept=".gif, .jpg, .jpeg, .png, .gif, .bmp" class="d-none" />
                                                <input type="hidden" name="mvt_movies_img<?=$q?>_on" id="mvt_movies_img<?=$q?>_on" value="<?=$row['mvt_movies_img'.$q]?>" class="form-control" />

                                                <label for="mvt_movies_img<?=$q?>" class="plus-input" id="mvt_movies_img<?=$q?>_box" style="width: 430px;<? if($row['mvt_movies_img'.$q] != "") echo 'border:none;'; ?>">
                                                    <? if($row['mvt_movies_img'.$q] == "") { ?>
                                                        <i class="mdi mdi-plus"></i>
                                                    <? } else { ?>
                                                        <img src="<?=$ct_img_url."/".$row['mvt_movies_img'.$q]?>" style="width:100%;border-radius: 10px;"/>
                                                    <? } ?>
                                                </label>
                                            </div>
                                            <div class="col-2 mt-4">
                                                <a class="btn btn-sm btn-outline-primary" href="javascript:$('#mvt_movies_img<?=$q?>').click()">썸네일업로드</a><br/>
                                                <a class="mt-2 btn btn-sm btn-outline-danger" href="javascript:delete_img('mvt_movies_img<?=$q?>', '<?=$row['mvt_movies_img'.$q]?>', 'moveis')">삭제</a>
                                            </div>
                                            <label for="mvt_movies_url<?=$q?> col-4 mr-3" style="padding-top:60px;margin-right:20px;" class="" id="">링크<?=$q?></label>
                                            <input class="form-control col-7 mt-5" id="mvt_movies_url<?=$q?>" name="mvt_movies_url<?=$q?>" value="<?=$row['mvt_movies_url'.$q]?>" placeholder="url을 입력해주세요"/>
                                        </div>
                                        <script type="text/javascript">
                                            $('#mvt_movies_img<?=$q?>').on('change', function(e) {
                                                //preview_image_multi_selected(e, '<?//=$q?>//');
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
                                    <? } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="mt-4 text-center">
                        <input type="submit" value="저장" class="btn btn-info" />
                    </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<? include_once("../inc/product_modal.php"); ?>
<script>
    function delete_img(column, img, type) {
        if (confirm("삭제하시겠습니까?")) {
            $.ajax({
                type: 'post',
                url: './main_update.php',
                dataType: 'json',
                data: {act: 'main_img_delete', column: column, img:img, type:type},
                success: function (d, s) {
                    alert(d['msg']);
                    location.reload();
                },
                cache: false
            });
        }
    }
    function delete_product(idx) {
        $.ajax({
            type: 'post',
            url: './main_update.php',
            dataType: 'json',
            data: {act: 'delete_product', pt_idx: idx},
            success: function (d, s) {
                if (d['result'] == "_ok") {
                    alert(d['msg']);
                    location.reload();
                }
            },
            cache: false
        });
    }
    function pop_up() {
        $.post('./main_update.php', {act: 'content_view'}, function (data) {
            if(data) {
                $('#product_modal-content').html(data);
                $('#product_modal').modal();

                get_list();
            }
        });

        return false;
    }
    function pop_up2(num) {
        $.post('./main_update.php', {act: 'content_view2', seq:num}, function (data) {
            if(data) {
                $('#product_modal-content').html(data);
                $('#product_modal').modal();

                get_list2();
            }
        });

        return false;
    }
    function delete_new_img(idx, img) {
        if (confirm("삭제하시겠습니까?")) {
            $.ajax({
                type: 'post',
                url: './main_update.php',
                dataType: 'json',
                data: {act: 'new_img_delete', idx: idx, img:img},
                success: function (d, s) {
                    alert(d['msg']);
                    location.reload();
                },
                cache: false
            });
        }
    }

    function frm_form_chk(f) {
        $('#splinner_modal').modal('show');
    }
</script>
<!-- 메인 끝 -->
<?
	include "./foot_inc.php";
?>