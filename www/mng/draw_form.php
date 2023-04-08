<?
	include "./head_inc.php";
	$chk_menu = '12';
	$chk_sub_menu = '0';
	include "./head_menu_inc.php";

    $query = "select * from draw_t where idx = 1";
    $row1 = $DB->fetch_assoc($query);
    $query = "select * from draw_t where idx = 2";
    $row2 = $DB->fetch_assoc($query);
?>
<!-- 메인 시작 -->
<div class="content-wrapper">
    <div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
                    <form method="post" name="frm_form" id="frm_form" action="./draw_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm" enctype="multipart/form-data">
                        <input type="hidden" name="act" value="random" />
                    <h4 class="card-title">랜덤뽑기효과 영상설정</h4>
					<p class="card-description">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <label for="" class="col-sm-2 col-form-label">효과1 </label>
                                <div class="col-sm-3">
                                    <b id="dt_effect1_txt" style="vertical-align: -webkit-baseline-middle"><?=$row1['dt_effect']?></b>
                                    <input type="file" name="dt_effect1" id="dt_effect1" value="<?=$row1['dt_effect']?>" accept=".mp4" class="d-none">
                                    <input type="hidden" name="dt_effect1_on" id="dt_effect1_on" value="<?=$row1['dt_effect']?>" class="form-control">
                                    <label for="dt_effect1" class="btn-sm btn-primary ml-3">파일업로드</label>
                                    <script type="text/javascript">
                                        $('#dt_effect1').on('change', function() {
                                            var fileValue = $("#dt_effect1").val().split("\\");
                                            var fileName = fileValue[fileValue.length-1]; // 파일명
                                            $("#dt_effect1_txt").text(fileName);
                                        });
                                    </script>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <label for="" class="col-sm-2 col-form-label">효과2 </label>
                                <div class="col-sm-3">
                                    <b id="dt_effect2_txt" style="vertical-align: -webkit-baseline-middle"><?=$row2['dt_effect']?></b>
                                    <input type="file" name="dt_effect2" id="dt_effect2" value="<?=$row2['dt_effect']?>" accept=".mp4, .gif" class="d-none">
                                    <input type="hidden" name="dt_effect2_on" id="dt_effect2_on" value="<?=$row2['dt_effect']?>" class="form-control">
                                    <label for="dt_effect2" class="btn-sm btn-primary ml-3">파일업로드</label>
                                    <script type="text/javascript">
                                        $('#dt_effect2').on('change', function() {
                                            var fileValue = $("#dt_effect2").val().split("\\");
                                            var fileName = fileValue[fileValue.length-1]; // 파일명
                                            $("#dt_effect2_txt").text(fileName);
                                        });
                                    </script>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="p-2 mt-3 text-center">
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
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
                    <form method="post" name="frm_form" id="frm_form" action="./draw_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm" enctype="multipart/form-data">
                        <input type="hidden" name="act" value="catalog" />
                        <input type="hidden" name="count" id="count" value="" />
                        <h4 class="card-title">도감효과 영상설정</h4>
                        <div class="p-2 text-right">
                            <button type="button" class="btn btn-primary" onclick="plus()">+</button>
                            <button type="button" class="btn btn-danger" onclick="minus()">-</button>
                        </div>
                        <p class="card-description" id="">
                        <?
                        $query = "select * from draw_t where dt_type = 2";
                        $list = $DB->select_query($query);
                        if($list) {
                            $i = 0;
                            foreach ($list as $row) {
                                ++$i;
                        ?>
                        <ul class="list-group list-group-flush effect" id="effect_area1">
                            <li class="list-group-item">
                                <div class="form-group row align-items-center mb-0">
                                    <label for="" class="col-sm-2 col-form-label">효과<?=$i?> </label>
                                    <input type="hidden" name="dt_idx_<?=$i?>" value="<?=$row['idx']?>" />
                                    <div class="col-sm-3">
                                        <b id="dt_effect_<?=$i?>_txt" style="vertical-align: -webkit-baseline-middle"><?=$row['dt_effect']?></b>
                                        <input type="file" name="dt_effect_<?=$i?>" id="dt_effect_<?=$i?>" value="<?=$row['dt_effect']?>" accept=".mp4, .gif" class="d-none">
                                        <input type="hidden" name="dt_effect_<?=$i?>_on" id="dt_effect_<?=$i?>_on" value="<?=$row['dt_effect']?>" class="form-control">
                                        <label for="dt_effect_<?=$i?>" class="btn-sm btn-primary ml-3">파일업로드</label>
                                        <script type="text/javascript">
                                            $('input[id^="dt_effect_"]').on('change', function(e) {
                                                var fileValue = $("#"+e.target.id).val().split("\\");
                                                var fileName = fileValue[fileValue.length-1]; // 파일명
                                                $("#"+e.target.id+"_txt").text(fileName);
                                            });
                                        </script>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <?
                            }
                        }
                        ?>
                        <p></p>
                        <ul class="list-group list-group-flush" id="effect_area"></ul>
                        <div class="p-2 mt-3 text-center">
                            <button type="submit" class="btn btn-primary">저장</button>
                        </div>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        var length = $(".effect").length;
        $("#count").val(length);
    });
    function plus() {
        var length = $(".effect").length;

        var html = $("#effect_area").html();
        html += '<ul class="list-group list-group-flush effect" id="effect_area'+length+'">';
        html += '    <li class="list-group-item">';
        html += '        <div class="form-group row align-items-center mb-0">';
        html += '            <label for="" class="col-sm-2 col-form-label">효과'+length+' </label>';
        html += '            <div class="col-sm-3">';
        html += '                <b id="dt_effect_'+length+'_txt" style="vertical-align: -webkit-baseline-middle"></b>';
        html += '                <input type="file" name="dt_effect_'+length+'" id="dt_effect_'+length+'" value="" accept=".mp4, .gif" class="d-none">';
        html += '                    <input type="hidden" name="dt_effect_'+length+'_on" id="dt_effect_'+length+'_on" value="" class="form-control" />';
        html += '                    <label for="dt_effect_'+length+'" class="btn-sm btn-primary ml-3">파일업로드</label>';
        html += '            </div>';
        html += '        </div>';
        html += '    </li>';
        html += '</ul>';

        $("#effect_area").html(html);
        $("#count").val(length);
    }
    function minus() {
        var length = $(".effect").length-1;
        $("#effect_area"+length).remove();
        $("#count").val($(".effect").length);
    }
    function frm_form_chk(f) {
        $('#splinner_modal').modal('show');
    }
</script>
<!-- 메인 끝 -->
<?
	include "./foot_inc.php";
?>