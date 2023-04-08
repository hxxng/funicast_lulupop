<?
	include "./head_inc.php";
	$chk_menu = '4';
	include "./head_menu_inc.php";

	$n_limit = $n_limit_num;
	$pg = $_GET['pg'];
	$_colspan_txt = "9";
    if($_GET['sel_ct_status'] == "")
    {
        $_GET['sel_ct_status'] = 1;
    }
	$_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&mt_seller_chk=".$_GET['mt_seller_chk']."&pg=";
    $_get_txt = "sel_ct_status=".$_GET['sel_ct_status']."&sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&sel_search_sdate=".$_GET['sel_search_sdate']."&sel_search_edate=".$_GET['sel_search_edate']."&pg=";
?>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">커뮤니티관리</h4>
                        <form method="get" name="frm_search" id="frm_search" action="<?=$_SERVER['PHP_SELF']?>" onsubmit="return frm_search_chk(this);">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="form-group row align-items-center mb-0">
                                    <label for="sel_search" class="col-sm-2 col-form-label">검색어</label>
                                    <div class="col-sm-4">
                                        <div class="input-group">
                                            <input type="text" name="search_txt" id="search_txt" value="<?=$_GET['search_txt']?>" class="form-control form-control-sm" />
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="form-group row align-items-center mb-0">
                                    <label for="sel_search_date" class="col-sm-2 col-form-label">작성일</label>
                                    <div class="col-sm-4">
                                        <div class="input-group">
                                            <input type="date" name="sel_search_sdate" id="sel_search_sdate" value="<?=$_GET['sel_search_sdate']?>" class="form-control datepicker" /> <span class="m-2">~</span> <input type="date" name="sel_search_edate" id="sel_search_edate" value="<?=$_GET['sel_search_edate']?>" class="form-control" />
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="form-group row align-items-center mb-0">
                                    <label for="sel_ct_status" class="col-sm-2 col-form-label">상태값</label>
                                    <div class="col-sm-6">
                                        <input type="hidden" name="sel_ct_status" id="sel_ct_status" value="<?=$_GET['sel_ct_status']?>" />
                                        <div class="btn-group" role="group" aria-label="pt_sale_now">
                                            <button type="button" onclick="f_sel_ct_status('1');" id="sel_ct_status1" class="c_sel_ct_status btn btn-outline-secondary<? if($_GET['sel_ct_status']=='1') { ?> btn-info text-white<? } ?>">전체</button>
                                            <button type="button" onclick="f_sel_ct_status('2');" id="sel_ct_status2" class="c_sel_ct_status btn btn-outline-secondary<? if($_GET['sel_ct_status']=='2') { ?> btn-info text-white<? } ?>">정상</button>
                                            <button type="button" onclick="f_sel_ct_status('3');" id="sel_ct_status3" class="c_sel_ct_status btn btn-outline-secondary<? if($_GET['sel_ct_status']=='3') { ?> btn-info text-white<? } ?>">블라인드</button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="form-group row align-items-center mb-0">
                                    <div class="col-sm-12 text-center">
                                        <input type="submit" class="btn btn-primary" id="search_btn" value="검색" />
                                        <input type="button" class="btn btn-secondary ml-2" value="초기화" onclick="location.href='./community_list.php'" />
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <p>&nbsp;</p>
                    </form>
                    <script>
                        <? if($_GET['sel_search_sdate']) { ?>$('#sel_search_sdate').val('<?=$_GET['sel_search_sdate']?>');<? } ?>
                        <? if($_GET['sel_search_edate']) { ?>$('#sel_search_edate').val('<?=$_GET['sel_search_edate']?>');<? } ?>
                        <? if($_GET['sel_search']) { ?>$('#sel_search').val('<?=$_GET['sel_search']?>');<? } ?>
                    </script>

					<table class="table table-striped table-hover">
					<thead>
					<tr>
                        <th class="text-center">
                            <input type="checkbox" onclick="f_checkbox_all('chk_all')" />
                        </th>
                        <th class="text-center">
							카테고리
						</th>
						<th class="text-center">
							글제목
						</th>
                        <th class="text-center">
							뷰수
						</th>
						<th class="text-center">
							좋아요수
						</th>
						<th class="text-center">
							댓글수
						</th>
						<th class="text-center">
							상태값
						</th>
                        <th class="text-center">
                            작성일시
                        </th>
						<th class="text-center">
							관리
						</th>
					</tr>
					</thead>
					<tbody>
					<?php
                        $_where = " where ";
                        $where_query = "";
                        $query = "
                                select a1.*, a1.idx as ct_idx, (SELECT pc_name FROM product_category_t WHERE pc_depth = 0 and idx=ct_cate_idx) as pc_name from community_t a1 
                            ";
                        $query_count = "
                                select count(*), a1.*, a1.idx as ct_idx, (SELECT pc_name FROM product_category_t WHERE pc_depth = 0 and idx=ct_cate_idx) as pc_name from community_t a1 
                            ";

                        if($_GET['search_txt']) {
                            $where_query .= $_where."(instr(a1.ct_title, '".$_GET['search_txt']."') or instr(a1.ct_content, '".$_GET['search_txt']."'))";
                            $_where = " and ";
                        }
                        if($_GET['sel_search_sdate'] && $_GET['sel_search_edate']) {
                            $where_query .= $_where." ct_wdate between '".$_GET['sel_search_sdate']." 00:00:00' and '".$_GET['sel_search_edate']." 23:59:59'";
                            $_where = " and ";
                        }
                        if($_GET['sel_ct_status']) {
                            if ($_GET['sel_ct_status'] == "1") {
                                $where_query .= $_where . "a1.ct_status in (1, 2) ";
                                $_where = " and ";
                            } else if ($_GET['sel_ct_status'] == "2") {
                                $where_query .= $_where . "a1.ct_status = 1";
                                $_where = " and ";
                            } else if ($_GET['sel_ct_status'] == "3") {
                                $where_query .= $_where . "a1.ct_status = 2";
                                $_where = " and ";
                            }
                        }

                        $count_query = $DB->count_query($query.$where_query);
                        $counts = $count_query;
                        $n_page = ceil($count_query / $n_limit_num);
                        if($pg=="") $pg = 1;
                        $n_from = ($pg - 1) * $n_limit;
                        $counts = $counts - (($pg - 1) * $n_limit_num);

                        unset($list);
                        $sql_query = $query.$where_query." order by a1.idx desc limit ".$n_from.", ".$n_limit;
                        $list = $DB->select_query($sql_query);


                        if($list) {
                            foreach($list as $row) {
                                $query = "select * from community_view_log_t where ct_idx = ".$row['idx'];
                                $view_cnt = $DB->count_query($query);

                                $query = "select * from like_community_t where ct_idx = ".$row['idx']." and lct_status = 'Y'";
                                $like_cnt = $DB->count_query($query);

                                $query = "select * from comment_t where ct_idx = ".$row['idx']." and ct_table = 'community_t'";
                                $comment_cnt = $DB->count_query($query);
                        ?>
					<tr>
                        <td class="text-center">
                            <input type="checkbox" id="chk_all" name="chk_all[]" value="<?=$row['ct_idx']?>">
                        </td>
                        <td class="text-center">
                            <?=$row['pc_name'];?>
                        </td>
                        <td class="text-center" style="width: 20%;">
                            <?=$row['ct_title'];?>
                        </td>
                        <td class="text-center">
                            <?=number_format((int)$view_cnt)?>
                        </td>
						<td class="text-center">
                            <?=number_format((int)$like_cnt)?>
						</td>
                        <td class="text-center">
                            <?=number_format((int)$comment_cnt)?>
                        </td>
                        <td class="text-center">
                            <? echo ($row['ct_status'] == 1 ? '정상' : '블라인드') ?>
                        </td>
                        <td class="text-center">
                            <?=DateType($row['ct_wdate'], 8)?>
                        </td>
						<td class="text-center">
							<input type="button" class="btn btn-outline-primary btn-sm" value="자세히" onclick="location.href='./community_form.php?act=update&ct_idx=<?=$row['ct_idx']?>&<?=$_get_txt.$_GET['pg']?>'" />
						</td>
					</tr>
					<?
								$counts--;
							}
						} else {
					?>
					<tr>
						<td colspan="<?=$_colspan_txt?>" class="text-center"><b>자료가 없습니다.</b></td>
					</tr>
					<?
						}
					?>
					</tbody>
					</table>
					<?
						if($n_page>1) {
							echo page_listing($pg, $n_page, $_SERVER['PHP_SELF']."?".$_get_txt);
						}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
    function chg_status(data) {
        var idx = f_checkbox_cnt("처리되었습니다.");
        if (idx == '') {
            return false;
        }
        var mt_grage = $("#select_status option:selected").val()
        if (confirm("구분을 변경하시겠습니까?")) {
            $.ajax({
                type: 'post',
                url: './member_update.php',
                dataType: 'json',
                data: {act: 'status_update', idx: idx, mt_grade: mt_grage},
                success: function (d, s) {
                    alert(d['msg']);
                    location.reload();
                },
                cache: false
            });
        } else {
            $("#select_status_" + id).val(previous);
        }
    }
</script>
<?
	include "./foot_inc.php";
?>