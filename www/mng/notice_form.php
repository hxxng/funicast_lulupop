<?
	include "./head_inc.php";
	$chk_menu = "14";
    $list_url_t = "notice_list.php";
	include "./head_menu_inc.php";

    if($_GET['act']=="update") {
        $query_pt = "
                    select *, a1.idx as ct_idx from coupon_t a1
                    where a1.idx = '".$_GET['ct_idx']."'
                ";
        $row = $DB->fetch_query($query_pt);

        $_act = "update";
        $_act_txt = "수정";
    } else {
        $_act = "input";
        $_act_txt = "등록";
    }

    $query = "
        SELECT a1.*, a1.idx as nt_idx FROM notice_t a1
        where a1.idx = '".$_GET['nt_idx']."'
    ";
    $row = $DB->fetch_query($query);

    $_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&sel_search_sdate=".$_GET['sel_search_sdate']."&sel_search_edate=".$_GET['sel_search_edate']."&pg=";
?>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
                <form method="post" name="frm_form" id="frm_form" action="./notice_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm" >
                <input type="hidden" name="act" id="act" value="<?=$_act?>" />
                <input type="hidden" name="nt_idx" id="nt_idx" value="<?=$_GET['nt_idx']?>" />
                <div class="card-body">
                    <div class="row">
                        <h4 class="card-title">공지사항 상세보기</h4>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <label for="pt_delivery_price" class="col-sm-2 col-form-label">타이틀 <b class="text-danger">*</b></label>
                                <div class="col-sm-10">
                                    <div class="input-group">
                                        <input type="text" name="nt_title" id="nt_title" value="<?=$row['nt_title']?>" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="form-group row align-items-center mb-0">
                                <label for="pt_delivery_price" class="col-sm-2 col-form-label">내용 <b class="text-danger">*</b></label>
                                <div class="col-sm-10">
                                    <div class="input-group">
                                        <textarea type="text" name="nt_content" id="nt_content" class="form-control" style="height: 400px;"><?=$row['nt_content']?></textarea>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <p class="p-3 mt-3 text-center">
                    <?
                    if($_act == "update") echo '<button type="button" class="btn btn-danger" onclick="del_notice()">삭제</button>';
                    ?>
                    <button type="submit" class="btn btn-primary mx-2" id="input_btn"><?=$_act_txt?></button>
                    <input type="button" value="목록" onclick="location.href='./<?=$list_url_t?>?<?=$_get_txt?>'" class="btn btn-outline-secondary" />
                </p>
                </form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
    function frm_form_chk(f) {
        if(f.nt_title.value=="") {
            alert("타이틀을 입력해주세요.");
            f.nt_title.focus();
            return false;
        }
        if(f.nt_content.value=="") {
            alert("내용을 입력해주세요.");
            f.nt_content.focus();
            return false;
        }
        $('#splinner_modal').modal('show');
    }
    function del_notice() {
        $("#act").val("delete");
        $("#frm_form").submit();
    }
</script>
<?
	include "./foot_inc.php";
?>