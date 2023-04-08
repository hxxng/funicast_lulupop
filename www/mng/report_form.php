<?
	include "./head_inc.php";
	$chk_menu = "9";
    $list_url_t = "report_list.php";
	include "./head_menu_inc.php";

    $query = "
        SELECT a1.*, a1.idx as rt_idx, (SELECT mt_nickname FROM member_t WHERE idx=reporter_idx) as reporter_name, (SELECT mt_nickname FROM member_t WHERE idx=reported_idx) as reported_name
        FROM report_t a1
        where a1.idx = '".$_GET['rt_idx']."'
    ";
    $row = $DB->fetch_query($query);
    if($row['rt_type'] == 2) {
        $query = "select * from ".$row['rt_table']." where idx = ".$row['report_idx'];
        $report = $DB->fetch_assoc($query);
        if($row['rt_table'] == "community_t") {
            $title = "커뮤니티";
        } else if($row['rt_table'] == "trade_t") {
            $title = "중고거래";
        } else if($row['rt_table'] == "comment_t") {
            $title = "댓글";
        } else if($row['rt_table'] == "qna_t") {
            $title = "문의";
        } else if($row['rt_table'] == "review_product_t") {
            $title = "상품 후기";
        } else if($row['rt_table'] == "chat_t") {
            $title = "채팅";
        }
    } else {
        $title = $row['reported_name'];
    }

    $_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&sel_search_sdate=".$_GET['sel_search_sdate']."&sel_search_edate=".$_GET['sel_search_edate']."&pg=";
?>
<style>.tab-content{border-bottom:1px solid #f3f3f3;border-top:1px solid #f3f3f3}.col-form-label{text-align:center;background-color:#f7f7f7;}.tab-content{padding:0;}.row{margin-right:0;}</style>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
                <form method="post" name="frm_form" id="frm_form" action="./report_update.php" onsubmit="return frm_form_chk(this);" target="hidden_ifrm" >
                <input type="hidden" name="act" id="act" value="update" />
                <input type="hidden" name="rt_idx" id="rt_idx" value="<?=$_GET['rt_idx']?>" />
                <input type="hidden" name="rt_table" id="rt_table" value="<?=$row['rt_table']?>" />
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
                                        <span><?=$row['reporter_name']?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-sm-3 col-form-label">작성일</label>
                                    <div class="col-sm-9 custom-control">
                                        <span><?=DateType($row['rt_wdate'],1)?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content" style="border-top: none;">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-sm-3 col-form-label">분류</label>
                                    <div class="col-sm-9 custom-control">
                                        <span>
                                        <?
                                        if($row['rt_type'] == 1) echo "사용자신고"; else echo "게시글신고";
                                        ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-sm-3 col-form-label">신고닉네임/게시글</label>
                                    <div class="col-sm-9 custom-control">
                                        <span><?=$title?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content" style="border-top: none;">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-sm-3 col-form-label">카테고리</label>
                                    <div class="col-sm-9 custom-control">
                                        <span>
                                        <?
                                        if($row['rt_category'] == 1) echo "음란물/성적인 내용"; if($row['rt_category'] == 2) echo "저작권 침해";
                                        if($row['rt_category'] == 3) echo "혐오발언,학대"; if($row['rt_category'] == 4) echo "부적절한 컨텐츠";
                                        if($row['rt_category'] == 5) echo "기타";
                                        ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="row align-items-center" style="padding-left: 10px;">
                                    <label for="mt_id" class="col-sm-3 col-form-label">처리값</label>
                                    <div class="col-sm-9 custom-control">
                                        <span>
                                        <?
                                        if($row['rt_status'] == 1) echo "미처리"; if($row['rt_status'] == 2) echo "신고글정지";
                                        if($row['rt_status'] == 3) echo "신고사용자정지"; if($row['rt_status'] == 4) echo "처리거절";
                                        ?>
                                        </span>
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
                                    <label for="mt_id" class="col-form-label" style="height: 300px;line-height: 260px;width: 191px;">상세내용</label>
                                    <div class="custom-control" style="width: 1338px;height: 300px;overflow-y: auto;">
                                        <span class="p-1" style="background-color: #fff;border: none;"><?=$row['rt_content']?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="p-3 mt-3 text-center">
                    <? if($row['rt_type'] == 2 && $row['rt_status'] != 2) echo '<button type="submit" class="btn btn-outline-danger" name="rt_status" value="2">신고글정지</button>';?>
                    <? if($row['rt_type'] == 1 && $row['rt_status'] != 3) echo '<button type="submit" class="btn btn-outline-danger" name="rt_status" value="3">신고사용자정지</button>';?>
                    <button type="submit" class="btn btn-outline-info ml-2" name="rt_status" value="4">신고거절</button>
                    <input type="button" value="목록" onclick="location.href='./<?=$list_url_t?>?<?=$_get_txt?>'" class="btn btn-outline-secondary mx-2" />
                </p>
                </form>
			</div>
		</div>
	</div>
</div>
<?
	include "./foot_inc.php";
?>