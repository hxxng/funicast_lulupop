<?
	include "./head_inc.php";
	$chk_menu = "10";
    $list_url_t = "qna_list.php";
	include "./head_menu_inc.php";

    $query = "
        SELECT a1.*, a1.idx as qt_idx, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname
        FROM qna_t a1
        where a1.idx = '".$_GET['qt_idx']."'
    ";
    $row = $DB->fetch_query($query);

    $_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&sel_search_sdate=".$_GET['sel_search_sdate']."&sel_search_edate=".$_GET['sel_search_edate']."&pg=";
?>
<style>.tab-content{border-bottom:1px solid #f3f3f3;border-top:1px solid #f3f3f3}.col-form-label{text-align:center;background-color:#f7f7f7;}.tab-content{padding:0;}.row{margin-right:0;}</style>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
                <form method="post" name="frm_form" id="frm_form" action="./qna_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm" >
                <input type="hidden" name="act" id="act" value="update" />
                <input type="hidden" name="qt_idx" id="qt_idx" value="<?=$_GET['qt_idx']?>" />
                <div class="card-body">
                    <div class="row">
                        <h4 class="card-title">후기 상세보기</h4>
                    </div>
                    <div class="tab-content">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-sm-3 col-form-label">작성자</label>
                                    <div class="col-sm-9 custom-control">
                                        <span><?=$row['mt_nickname']?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-sm-3 col-form-label">작성일</label>
                                    <div class="col-sm-9 custom-control">
                                        <span><?=DateType($row['qt_wdate'],1)?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-form-label" style="height: 300px;line-height: 260px;width: 191px;">문의 내용</label>
                                    <div class="custom-control" style="width: 1338px;height: 300px;overflow-y: auto;">
                                        <span class="p-1" style="background-color: #fff;border: none;"><?=$row['qt_content']?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-form-label" style="height: 300px;line-height: 260px;width: 191px;">답글</label>
                                    <div class="custom-control" style="width: 1338px;height: 300px;overflow-y: auto;">
                                        <textarea class="p-1" name="qt_answer" style="width: 100%;height: 295px;background-color: #fff;border: none;" placeholder="답글을 입력해주세요."><?=$row['qt_answer']?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="p-3 mt-3 text-center">
                    <button type="submit" class="btn btn-primary">저장</button>
                    <input type="button" value="목록" onclick="location.href='./<?=$list_url_t?>?<?=$_get_txt?>'" class="btn btn-outline-secondary mx-2" />
                </p>
                </form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
    function frm_form_chk(f) {
        if(f.qt_answer.value=="") {
            alert("답변을 입력해주세요.");
            f.qt_answer.focus();
            return false;
        }
        $('#splinner_modal').modal('show');
    }
</script>
<?
	include "./foot_inc.php";
?>