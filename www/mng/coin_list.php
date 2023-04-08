<?
	include "./head_inc.php";
	$chk_menu = '7';
	$chk_sub_menu = '1';
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
					<h4 class="card-title">코인결제내역</h4>
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
                                    <label for="sel_search_date" class="col-sm-2 col-form-label">결제일</label>
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
                                        <input type="button" class="btn btn-secondary ml-2" value="초기화" onclick="location.href='./coin_list.php'" />
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
						<th class="text-center" style="width: 40%">
							결제코인
						</th>
						<th class="text-center">
							결제금액
						</th>
                        <th class="text-center">
                            결제자
                        </th>
                        <th class="text-center">
                            환불요청
                        </th>
                        <th class="text-center">
                            결제일
                        </th>
						<th class="text-center">
							관리
						</th>
					</tr>
					</thead>
					<tbody>
					<?
                        $where_query = " where ";
						$query = "
							select a1.*, a1.idx as ct_idx, mt_nickname from coin_t a1 left join member_t on member_t.idx = a1.mt_idx and mt_level = 3 
						";
						$query_count = "
							select count(*), a1.idx as ct_idx, mt_nickname from coin_t a1 left join member_t on member_t.idx = a1.mt_idx and mt_level = 3 
						";

						if($_GET['search_txt']) {
                            $where_query .= "instr((SELECT mt_nickname FROM member_t WHERE idx=mt_idx), '".$_GET['search_txt']."')";
                            $where_query .= " and ";
						}
                        if($_GET['sel_search_sdate'] && $_GET['sel_search_edate']) {
                            $where_query .= " (ct_pdate between '".$_GET['sel_search_sdate']." 00:00:00' and '".$_GET['sel_search_edate']." 23:59:59')";
                            $where_query .= " and ";
                        }
                        $where_query .= " ct_type = 1 and ct_status in (2,4) and mt_id is not null";

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
							<?=number_format($row['ct_amount'])?>코인결제
						</td>
                        <td class="text-center">
                            <?=number_format($row['ct_price'])?>원
                        </td>
                        <td class="text-center">
                            <?=$row['mt_nickname']?>
                        </td>
                        <td class="text-center">
                            <?if($row['ct_refund_status'] == "" || $row['ct_refund_status'] == 0) echo '-'; if($row['ct_refund_status'] == "1") echo '환불요청'; if($row['ct_refund_status'] == "2") echo '환불완료';?>
                        </td>
                        <td class="text-center">
                            <?=DateType($row['ct_pdate'],8)?>
                        </td>
						<td class="text-center">
                            <input type="button" class="btn btn-outline-primary btn-sm" value="자세히" onclick="location.href='./coin_form.php?act=update&ct_idx=<?=$row['ct_idx']?>&<?=$_get_txt.$_GET['pg']?>'" />
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
            url: './coin_update.php',
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