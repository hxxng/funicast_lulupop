<?
	include "./head_inc.php";
	$chk_menu = '6';
	$chk_sub_menu = '2';
	include "./head_menu_inc.php";

	$n_limit = $n_limit_num;
	$pg = $_GET['pg'];
	$_colspan_txt = "9";
	$_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&pg=";
?>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">쿠폰추가</h4>
                    <form class="form-inline">
                        <div class="form-group mx-sm-1">
                            <input type="button" class="btn btn-primary" value="쿠폰 생성" onclick="location.href='./coupon_form.php?act=input'">
                        </div>
                        <div class="form-group mx-sm-1">
                            <input type="button" class="btn btn-danger" value="쿠폰 삭제" onclick="select_delete()">
                        </div>
                    </form>
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
                                    <label for="sel_search_date" class="col-sm-2 col-form-label">발급일</label>
                                    <div class="col-sm-4">
                                        <div class="input-group">
                                            <input type="date" name="sel_search_sdate" id="sel_search_sdate" value="<?=$_GET['sel_search_sdate']?>" class="form-control datepicker" /> <span class="m-2">~</span> <input type="date" name="sel_search_edate" id="sel_search_edate" value="<?=$_GET['sel_search_edate']?>" class="form-control" />
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="form-group row align-items-center mb-0">
                                    <div class="col-sm-12 text-center">
                                        <input type="submit" class="btn btn-primary" id="search_btn" value="검색" />
                                        <input type="button" class="btn btn-secondary ml-2" value="초기화" onclick="location.href='./coupon_list.php'" />
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
						<th class="text-center" style="width:50px;">
                            <input type="checkbox" id="chk_all"/>
						</th>
						<th class="text-center" style="width: 45%">
							쿠폰명
						</th>
						<th class="text-center">
							쿠폰번호
						</th>
                        <th class="text-center">
                            사용가능일시
                        </th>
                        <th class="text-center">
                            발급일시
                        </th>
						<th class="text-center">
							관리
						</th>
					</tr>
					</thead>
					<tbody>
					<?
						$_where = " where 1 and ";
						$query = "
							select *, a1.idx as ct_idx from coupon_t a1
						";
						$query_count = "
							select count(*) from coupon_t a1
						";

						if($_GET['search_txt']) {
                            $where_query .= $_where."(instr(a1.ct_name, '".$_GET['search_txt']."') or instr(a1.ct_code, '".$_GET['search_txt']."'))";
							$_where = " and ";
						}
                        if($_GET['sel_search_sdate'] && $_GET['sel_search_edate']) {
                            $where_query .= $_where." ct_wdate between '".$_GET['sel_search_sdate']." 00:00:00' and '".$_GET['sel_search_edate']." 23:59:59'";
                            $_where = " and ";
                        }

						$row_cnt = $DB->fetch_query($query_count.$where_query);
						$count_query = $row_cnt[0];
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
					?>
					<tr>
						<td class="text-center">
                            <input type="checkbox" name="chk_box" id="chk_box_<?= $row['ct_idx'] ?>"/>
						</td>
						<td class="text-center">
							<?=$row['ct_name']?>
						</td>
                        <td class="text-center">
                            <?=$row['ct_code']?>
                        </td>
                        <td class="text-center">
                            <?=$row['ct_sdate']?> - <?=$row['ct_edate']?>
                        </td>
                        <td class="text-center">
                            <?=DateType($row['ct_wdate'],8)?>
                        </td>
						<td class="text-center">
                            <input type="button" class="btn btn-outline-primary btn-sm" value="자세히" onclick="location.href='./coupon_form.php?act=update&ct_idx=<?=$row['ct_idx']?>&<?=$_get_txt.$_GET['pg']?>'" />
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
    $("#chk_all").on('click', function () {
        f_checkbox_all('chk_box');
    });

    function f_checkbox_all(obj) {
        $('input:checkbox[name="' + obj + '"]').each(function () {
            if ($(this).prop('checked') == true) {
                $(this).prop('checked', false);
            } else {
                $(this).prop('checked', true);
            }
        });

        return false;
    }

    function select_delete() {
        var list = $("input[name='chk_box']");
        var ids = [];
        for (var i = 0; i < list.length; i++) {
            if ($("#" + list[i].id).is(":checked")) {
                var id = list[i].id;
                id = id.replace("chk_box_", "");
                ids.push(id);
            }
        }
        $.ajax({
            type: 'post',
            url: './coupon_update.php',
            dataType: 'json',
            data: {act: 'select_delete', et_idx: ids},
            success: function (d, s) {
                if (d['result'] == "_ok") {
                    alert(d['msg']);
                    location.reload();
                }
            },
            cache: false
        });
    }

</script>
<?
	include "./foot_inc.php";
?>