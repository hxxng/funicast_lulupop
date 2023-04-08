<?
	include "./head_inc.php";
	$chk_menu = "0";
    $list_url_t = "member_list.php";

	$chk_post_code = 'Y';
	include "./head_menu_inc.php";

    $query = "
        select *, a1.idx as mt_idx from member_t a1
        where a1.idx = '".$_GET['mt_idx']."'
    ";
    $row = $DB->fetch_query($query);

    $query = "select * from community_t where ct_status = 1 and mt_idx = ".$_GET['mt_idx'];
    $community = $DB->count_query($query);

    $query = "select * from trade_t where tt_status = 1 and tt_sale_status = 1 and mt_idx = ".$_GET['mt_idx'];
    $trade_sale = $DB->count_query($query);

    $query = "select * from trade_t where tt_status = 1 and tt_sale_status = 2 and mt_idx = ".$_GET['mt_idx'];
    $trade_soldout = $DB->count_query($query);

    $query = "select * from order_t where ot_status > 1 and mt_idx = ".$_GET['mt_idx'];
    $order = $DB->count_query($query);

    $query = "select * from review_product_t where mt_idx = ".$_GET['mt_idx'];
    $review = $DB->count_query($query);

    $query = "select * from qna_t where mt_idx = ".$_GET['mt_idx'];
    $qna = $DB->count_query($query);

    $query = "select * from report_t where reporter_idx = ".$_GET['mt_idx'];
    $report = $DB->count_query($query);

	$_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&pg=".$_GET['pg'];
?>
<style>.tab-content .col-sm-12{border-bottom:1px solid #f3f3f3}.tab-content{border-top:1px solid #f3f3f3}.col-form-label{text-align:center;background-color:#f7f7f7;}.tab-content{padding:0;}.row{margin-right:0;}</style>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
                <div class="card-body">
                    <h4 class="card-title">회원가입정보</h4>
                    <div class="tab-content">
                        <div role="tabpanel" aria-labelledby="tab_tab1">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="row align-items-center">
                                        <label for="" class="col-sm-3 col-form-label">가입일</label>
                                        <div class="col-sm-9 custom-control">
                                            <b><?=DateType($row['mt_wdate'],1)?></b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="row align-items-center">
                                        <label for="" class="col-sm-3 col-form-label">최근접속일</label>
                                        <div class="col-sm-9 custom-control">
                                            <b><?=DateType($row['mt_ldate'],1)?></b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="card-body">
                    <h4 class="card-title">추가정보</h4>
					<!-- Tab panes -->
					<div class="tab-content">
                        <div role="tabpanel" aria-labelledby="tab_tab1">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="row align-items-center">
                                        <label for="" class="col-sm-3 col-form-label">닉네임</label>
                                        <div class="col-sm-3 custom-control">
                                            <b><?=$row['mt_nickname']?></b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="row align-items-center">
                                        <label for="" class="col-sm-3 col-form-label">이름</label>
                                        <div class="col-sm-3 custom-control">
                                            <b><?=$row['mt_name']?></b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="row align-items-center">
                                        <label for="" class="col-sm-3 col-form-label">연락처</label>
                                        <div class="col-sm-9 custom-control">
                                            <b><?=$row['mt_hp']?></b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="row align-items-center">
                                        <label for="" class="col-sm-3 col-form-label">주소</label>
                                        <div class="col-sm-9 custom-control">
                                            <b><?=$row['mt_add1']?> <?=$row['mt_add2']?></b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
					</div>
                </div>
                <div class="row">
                    <div class="card-body col-6" style="padding-left:37px;">
                        <h4 class="card-title">활동정보1</h4>
                        <div class="tab-content">
                            <div role="tabpanel" aria-labelledby="tab_tab1">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="row align-items-center">
                                            <label for="" class="col-sm-3 col-form-label">커뮤니티 작성글</label>
                                            <div class="col-sm-9 custom-control">
                                                <b><?=number_format($community)?>건</b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="row align-items-center">
                                            <label for="" class="col-sm-3 col-form-label">중고게시글(판매중)</label>
                                            <div class="col-sm-9 custom-control">
                                                <b><?=number_format($trade_sale)?>건</b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="row align-items-center">
                                            <label for="" class="col-sm-3 col-form-label">중고게시글(판매완료)</label>
                                            <div class="col-sm-9 custom-control">
                                                <b><?=number_format($trade_soldout)?>건</b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="row align-items-center">
                                            <label for="" class="col-sm-3 col-form-label">상품주문내역</label>
                                            <div class="col-sm-9 custom-control">
                                                <b><?=number_format($order)?>건</b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="row align-items-center">
                                            <label for="" class="col-sm-3 col-form-label">작성후기</label>
                                            <div class="col-sm-9 custom-control">
                                                <b><?=number_format($review)?>건</b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="row align-items-center">
                                            <label for="" class="col-sm-3 col-form-label">문의내역</label>
                                            <div class="col-sm-9 custom-control">
                                                <b><?=number_format($qna)?>건</b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="row align-items-center">
                                            <label for="" class="col-sm-3 col-form-label">신고</label>
                                            <div class="col-sm-9 custom-control">
                                                <b><?=number_format($report)?>건</b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body col-6">
                        <h4 class="card-title">활동정보2</h4>
                        <div class="tab-content" style="border:none;">
                            <div role="tabpanel" aria-labelledby="tab_tab1">
                                <div class="row" style="border: 1px solid #f3f3f3;">
                                    <div class="col-sm-12" style="border:none;">
                                        <div class="row align-items-center">
                                            <label for="" class="col-sm-3 col-form-label">보유적립금</label>
                                            <div class="col-sm-9 custom-control">
                                                <b><?=number_format($row['mt_point'])?>P</b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br/>
                                <div class="row" style="border: 1px solid #f3f3f3;">
                                    <div class="col-sm-12" style="border:none;">
                                        <div class="row align-items-center">
                                            <label for="" class="col-sm-3 col-form-label">보유코인</label>
                                            <div class="col-sm-9 custom-control">
                                                <b><?=number_format($row['mt_coin'])?>C</b>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br/>
                                <div class="row" style="border-bottom: none;">
                                    <div class="col-sm-4"></div>
                                    <div class="col-sm-8">
                                        <div class="row align-items-center">
                                            <div class="col-sm-9 custom-control">
                                                <input type="text" id="mt_coin" name="mt_coin" class="form-control" numberonly="" />
                                            </div>
                                            <button class="col-sm-3 btn btn-primary" onclick="plus_coin()">코인추가지급</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="p-3 mt-3 text-center">
<!--                    <input type="submit" value="저장" class="btn btn-info" />-->
                    <input type="button" value="목록" onclick="location.href='./<?=$list_url_t?>?<?=$_get_txt?>'" class="btn btn-outline-secondary mx-2" />
                </p>
			</div>
		</div>
	</div>
</div>
<script>
    function plus_coin() {
        if (confirm("코인을 추가지급하시겠습니까?")) {
            $.ajax({
                type: 'post',
                url: './member_update.php',
                dataType: 'json',
                data: {act: 'plus_coin', idx: <?=$_GET['mt_idx']?>, mt_coin: $("#mt_coin").val()},
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
</script>
<?
	include "./foot_inc.php";
?>