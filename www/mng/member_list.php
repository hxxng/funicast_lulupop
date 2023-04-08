<?
	include "./head_inc.php";
	$chk_menu = '0';
	include "./head_menu_inc.php";

	$n_limit = $n_limit_num;
	$pg = $_GET['pg'];
	$_colspan_txt = "9";
	$_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&mt_seller_chk=".$_GET['mt_seller_chk']."&pg=";

    $query = "
        select *, a1.idx as mt_idx from member_t a1 where mt_level = 3
    ";
    $query_count = "
        select count(*) from member_t a1 where mt_level = 3
    ";

    if($_GET['search_txt']) {
//        if($_GET['sel_search']=="all") {
//            $where_query .= $_where."(instr(a1.mt_id, '".$_GET['search_txt']."') or instr(a1.mt_name, '".$_GET['search_txt']."') or instr(a1.mt_nickname, '".$_GET['search_txt']."') or instr(a1.mt_hp, '".$_GET['search_txt']."') or instr(a1.mt_email, '".$_GET['search_txt']."'))";
//        } else {
//            $where_query .= $_where."instr(".$_GET['sel_search'].", '".$_GET['search_txt']."')";
//        }
        $where_query .= " and (instr(a1.mt_id, '".$_GET['search_txt']."') or instr(a1.mt_name, '".$_GET['search_txt']."') or instr(a1.mt_nickname, '".$_GET['search_txt']."') or instr(a1.mt_hp, '".$_GET['search_txt']."') or instr(a1.mt_email, '".$_GET['search_txt']."'))";
    }

    $row_cnt = $DB->fetch_query($query_count.$where_query);
    $couwt_query = $row_cnt[0];
    $counts = $couwt_query;
    $n_page = ceil($couwt_query / $n_limit_num);
    if($pg=="") $pg = 1;
    $n_from = ($pg - 1) * $n_limit;
    $counts = $counts - (($pg - 1) * $n_limit_num);

    unset($list);
    $sql_query = $query.$where_query." order by a1.idx desc limit ".$n_from.", ".$n_limit;
    $list = $DB->select_query($sql_query);

?>
<div class="content-wrapper">
	<div class="row">
		<div class="col-md-12 grid-margin stretch-card">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">회원관리</h4>					
                    <div class="p-3 float-left">
                    회원수 : <?php echo number_format($counts);?>명
                    </div>
					<div class="p-3 float-right">
						<form method="get" name="frm_search" id="frm_search" action="<?=$_SERVER['PHP_SELF']?>" class="form-inline" onsubmit="return frm_search_chk(this);">
							<div class="form-group mx-sm-1">
<!--								<select name="sel_search" id="sel_search" class="form-control form-control-sm">-->
<!--									<option value="all">통합검색</option>-->
<!--									<option value="a1.mt_id">아이디</option>-->
<!--									<option value="a1.mt_nickname">닉네임</option>-->
<!--									<option value="a1.mt_hp">연락처</option>-->
<!--								</select>-->
							</div>

							<div class="form-group mx-sm-1">
								<input type="text" class="form-control form-control-sm" style="width:200px;" name="search_txt" id="search_txt" value="<?=$_GET['search_txt']?>" />
							</div>

							<div class="form-group mx-sm-1">
								<input type="submit" class="btn btn-primary" value="검색" />
							</div>

							<div class="form-group mx-sm-1">
								<input type="button" class="btn btn-secondary" value="초기화" onclick="location.href='./member_list.php'" />
							</div>
						</form>
					</div>

					<table class="table table-striped table-hover">
					<thead>
					<tr>
                        <th class="text-center">
                            <input type="checkbox" onclick="f_checkbox_all('chk_all')" />
                        </th>
                        <th class="text-center">
							닉네임
						</th>
						<th class="text-center">
							이름
						</th>
                        <th class="text-center">
							연락처
						</th>
						<th class="text-center">
							가입일
						</th>
						<th class="text-center">
							마지막접속
						</th>
						<th class="text-center">
							구분
						</th>
						<th class="text-center">
							관리
						</th>
					</tr>
					</thead>
					<tbody>
					<?php
						if($list) {
							foreach($list as $row) {                                
                                $mt_level_name = '정상';
                                if($row['mt_level']=='1') $mt_level_name = '탈퇴';
                                if($row['mt_grade']=='1') $mt_grade = '일반사용자';
                                if($row['mt_grade']=='2') $mt_grade = '프리미엄';
					?>
					<tr>
                        <td class="text-center">
                            <input type="checkbox" id="chk_all" name="chk_all[]" value="<?=$row['mt_idx']?>">
                        </td>
                        <td class="text-center">
                            <?=$row['mt_nickname'];?>
                        </td>
                        <td class="text-center">
                            <?=$row['mt_name'];?>
                        </td>
                        <td class="text-center">
                            <?=$row['mt_hp'];?>
                        </td>
						<td class="text-center">
                            <?=DateType($row['mt_wdate'], 1)?>
						</td>
                        <td class="text-center">
                            <?=DateType($row['mt_ldate'], 1)?>
                        </td>
						<td class="text-center">
							<?=$mt_grade;?>
						</td>
						<td class="text-center">
							<input type="button" class="btn btn-outline-primary btn-sm" value="자세히" onclick="location.href='./member_form.php?act=update&mt_idx=<?=$row['mt_idx']?>&<?=$_get_txt.$_GET['pg']?>'" />
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
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="col-sm-2 float-right">
                            <div class="form-group mb-1">
                                <button type="button" onclick="chg_level(1);" class="btn btn btn-primary btn-sm mr-2">이용정지</button>
                                <button type="button" onclick="chg_level(2);" class="btn btn btn-primary btn-sm">강제탈퇴</button>
                            </div>
                            <div class="form-group mb-1">
                                <select class="custom-select col-6 mr-1" id="select_status">
                                    <option value='1' selected >일반사용자</option>
                                    <option value='2'>프리미엄</option>
                                </select>
                                <button type="button" onclick="chg_status();" class="btn btn-outline-secondary btn-sm">저장</button>
                            </div>
                        </div>
                    </li>
                </ul>
			</div>
		</div>
	</div>
</div>
<script>
    function f_checkbox_cnt() {
        var chk_cnt = 0;
        var idx = '';

        $('input:checkbox[name="chk_all[]"]').each(function() {
            if($(this).prop('checked')==true) {
                chk_cnt++;
                idx += $(this).val()+'|';
            }
        });

        if(chk_cnt<1) {
            alert('처리할 회원을 선택해주세요.');
            return false;
        }

        return {idx: idx};
    }

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
    function chg_level(num) {
        var idx = f_checkbox_cnt();
        var msg = "";
        if (idx == '') {
            return false;
        }
        if(num == 1) {
            msg = "선택된 회원을 이용 정지하시겠습니까?";
        } else {
            msg = "선택된 회원을 강제 탈퇴 처리하시겠습니까?";
        }
        if (confirm(msg)) {
            $.ajax({
                type: 'post',
                url: './member_update.php',
                dataType: 'json',
                data: {act: 'chg_level', idx: idx, num:num},
                success: function (d, s) {
                    alert(d['msg']);
                    location.reload();
                },
                cache: false
            });
        } else {
            alert("취소되었습니다.");
        }

        return false;
    }
</script>
<?
	include "./foot_inc.php";
?>