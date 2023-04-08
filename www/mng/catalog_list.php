<?
	include "./head_inc.php";
	$chk_menu = '2';
	$chk_sub_menu = '3';
	include "./head_menu_inc.php";

    $query = "select catalog_t.*, pt_title from catalog_t left join product_t on product_t.idx = catalog_t.pt_idx where pt_random_chk = 'Y' ";
    $list = $DB->select_query($query);
    $count = $DB->count_query($query);
?>
<style>
    .table td img {
        width: 100%;
        height: 100%;
        border-radius: 0px;
    }
</style>
<!-- 메인 시작 -->
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <form method="post" name="frm_form" id="frm_form" action="./catalog_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm" enctype="multipart/form-data">
                        <input type="hidden" name="act" id="act" value="get_list" />
                        <input type="hidden" name="count" value="<?=$count?>" />
                        <h4 class="card-title">도감상품관리</h4>
                        <p class="card-description">
                            <i class="mdi mdi-circle-medium text-danger"></i>랜덤상품관리 메뉴에서 상품 추가 후 사용가능한 메뉴입니다.
                        </p>
                        <div class="col-sm-12 custom-control">
                            <div class="form-group row align-items-center mb-0">
                                <div class="col-sm-12">
                                    <div class="row">
                                        <table class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th class="text-center" style="width: 15%">활성 이미지</th>
                                                <th class="text-center" style="width: 15%">비활성 이미지</th>
                                                <th class="text-center">도감 정보</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <? if($list) { for($i=0; $i<$count; $i++) { ?>
                                                <tr>
                                                    <td class="text-center">
                                                        <input type="file" name="catalog_img1_<?=$i?>" id="catalog_img1_<?=$i?>" value="<?=$list[$i]['ct_img1']?>" accept=".gif, .jpg, .jpeg, .png, .gif, .bmp" class="d-none" />
                                                        <input type="hidden" name="catalog_img1_<?=$i?>_on" id="catalog_img1_<?=$i?>_on" value="<?=$list[$i]['ct_img1']?>" class="form-control" />
                                                        <input type="hidden" name="ct_idx<?=$i?>" id="ct_idx<?=$i?>" value="<?=$list[$i]['idx']?>" class="form-control" />

                                                        <label for="catalog_img1_<?=$i?>" class="plus-input" id="catalog_img1_<?=$i?>_box" style="width: 220px;height: 200px;border-radius: 10px;<?= ($list[$i]['ct_img1'] == "") ? "" : "border:none;" ?>">
                                                            <? if($list[$i]['ct_img1'] == "") { ?>
                                                                <i class="mdi mdi-plus" style="line-height: 185px;"></i>
                                                            <? } else { ?>
                                                                <img src="<?=$ct_img_url."/".$list[$i]['ct_img1']?>?<?=time()?>" style="border-radius: 10px;" />
                                                            <? } ?>
                                                        </label>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="file" name="catalog_img2_<?=$i?>" id="catalog_img2_<?=$i?>" value="<?=$list[$i]['ct_img2']?>" accept=".gif, .jpg, .jpeg, .png, .gif, .bmp" class="d-none" />
                                                        <input type="hidden" name="catalog_img2_<?=$i?>_on" id="catalog_img2_<?=$i?>_on" value="<?=$list[$i]['ct_img2']?>" class="form-control" />

                                                        <label for="catalog_img2_<?=$i?>" class="plus-input" id="catalog_img2_<?=$i?>_box" style="width: 220px;height: 200px;border-radius: 10px;border-radius: 10px;<?= ($list[$i]['ct_img2'] == "") ? "" : "border:none;" ?>">
                                                            <? if($list[$i]['ct_img2'] == "") { ?>
                                                                <i class="mdi mdi-plus" style="line-height: 185px;"></i>
                                                            <? } else { ?>
                                                                <img src="<?=$ct_img_url."/".$list[$i]['ct_img2']?>?<?=time()?>" style="border-radius: 10px;" />
                                                            <? } ?>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <input class="form-control mb-1" id="pt_title<?=$i?>" readonly value="<?=$list[$i]['pt_title']?>" />
                                                        <input class="form-control mb-1" id="ct_title<?=$i?>" name="ct_title<?=$i?>" value="<?=$list[$i]['ct_title']?>" placeholder="도감 타이틀을 입력해주세요"/>
                                                        <select id="ct_effect<?=$i?>" name="ct_effect<?=$i?>" class="form-control col-4">
                                                            <option value="">도감 효과</option>
                                                            <?
                                                            $query = "select * from draw_t where dt_type = 2 order by idx";
                                                            $list2 = $DB->select_query($query);
                                                            if($list2) {
                                                                $j = 0;
                                                                foreach ($list2 as $row2) {
                                                                    ++$j;
                                                                    ?>
                                                                    <option value="<?=$row2['idx']?>" <? if($row2['idx'] == $list[$i]['ct_effect']) echo "selected=''"; ?>>도감 효과 <?=$j?></option>
                                                                    <?
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                            <? } } ?>
                                            </tbody>
                                        </table>
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
<script>
    $('input[id^="catalog_img"]').on('change', function(e) {
        var target_id = e.target.id;
        var files = e.target.files;
        var filesArr = Array.prototype.slice.call(files);

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
    });
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
    function frm_form_chk(f) {
        $('#splinner_modal').modal('show');
    }
</script>
<!-- 메인 끝 -->
<?
	include "./foot_inc.php";
?>