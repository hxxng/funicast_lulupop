<?
include "./head_inc.php";
$chk_menu = '3';
$chk_sub_menu = '2';
include "./head_menu_inc.php";

$n_limit = $n_limit_num;
$pg = $_GET['pg'];
$_colspan_txt = "12";
$_get_txt = "search_txt=".$_GET['search_txt']."&sel_search_sdate=".$_GET['sel_search_sdate']."&sel_search_edate=".$_GET['sel_search_edate']."&pg=";
?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">교환관리</h4>
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
                                        <label for="sel_search_date" class="col-sm-2 col-form-label">요청일</label>
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
                                            <input type="button" class="btn btn-secondary ml-2" value="초기화" onclick="location.href='./exchange_list.php'" />
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <p>&nbsp;</p>
                        </form>
                        <script type="text/javascript">
                            function f_ct_status(nm) {
                                var nm_t = Number(nm-1);

                                $('.c_sel_ct_status').each(function(i, obj) {
                                    if(nm_t==i) {
                                        $('#sel_ct_status'+nm).addClass('btn-info text-white');
                                    } else {
                                        $('#sel_ct_status'+Number(i+1)).removeClass('btn-info text-white');
                                    }
                                });
                                $('#sel_ct_status').val(nm);

                                return false;
                            }

                            function frm_search_chk(f) {
                                /*
                                if(f.search_txt.value=="") {
                                    alert("검색어를 입력바랍니다.");
                                    f.search_txt.focus();
                                    return false;
                                }
                                */

                                return true;
                            }

                            function f_excel_down(act_t) {
                                var f = document.frm_search;

                                if(f.sel_search_sdate.value=="") {
                                    alert("조회기간을 입력바랍니다.");
                                    f.sel_search_sdate.focus();
                                    return false;
                                }
                                if(f.sel_search_edate.value=="") {
                                    alert("조회기간을 입력바랍니다.");
                                    f.sel_search_edate.focus();
                                    return false;
                                }

                                hidden_ifrm.document.location.href = './order_excel.php?act='+act_t+'&search_date='+f.sel_search_date.value+'&sdate='+f.sel_search_sdate.value+'&edate='+f.sel_search_edate.value;

                                return false;
                            }

                            <? if($_GET['sel_search_sdate']) { ?>$('#sel_search_sdate').val('<?=$_GET['sel_search_sdate']?>');<? } ?>
                            <? if($_GET['sel_search_edate']) { ?>$('#sel_search_edate').val('<?=$_GET['sel_search_edate']?>');<? } ?>
                        </script>
                        <!--
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item" style="border-top: 0;margin-top: 0px;">
                            <div class="form-group row align-items-center mb-0">
                                <button type="button" onclick="f_ot_status_chk('3');" class="btn btn-outline-secondary btn-sm mr-2">발주확인</button>
                                <button type="button" onclick="f_order_delivery();" class="btn btn-outline-secondary btn-sm mr-2">발송처리</button>
                                <button type="button" onclick="f_order_cancel('<?=$row['ct_status']?>');" class="btn btn-outline-secondary btn-sm mr-2">취소처리</button>
                                <button type="button" onclick="f_ot_status_chk('2');" class="btn btn-outline-secondary btn-sm">입금처리</button>
                            </div>
                        </li>
                    </ul>
                    -->
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="text-center" style="width:80px;">
                                    <input type="checkbox" onclick="f_checkbox_all('chk_all')" />
                                    <!--							<input type="button" class="btn btn-secondary btn-xs" value="선택" onclick="f_checkbox_all('chk_all')" />-->
                                </th>
                                <th class="text-center">
                                    교환번호
                                </th>
                                <th class="text-center">
                                    주문번호
                                </th>
                                <th class="text-center">
                                    주문자
                                </th>
                                <th class="text-center">
                                    교환상품이름
                                </th>
                                <th class="text-center">
                                    총결제가격
                                </th>
                                <th class="text-center">
                                    총구매수량
                                </th>
                                <th class="text-center">
                                    요청일
                                </th>
                                <th class="text-center">
                                    상태값
                                </th>
                                <th class="text-center">
                                    관리
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?
                            $_where = " and ";
                            $query = "
                                select a1.*, ct_status, ct_request_wdate from order_t a1 left join cart_t on cart_t.ot_code = a1.ot_code where ct_status in (80,81,82)
                            ";
                            if($_GET['search_txt']) {
                                $where_query .= $_where."(instr(a1.ot_code, '".$_GET['search_txt']."') or instr(a1.ot_name, '".$_GET['search_txt']."'))";
                                $_where = " and ";
                            }
                            if($_GET['sel_search_sdate'] && $_GET['sel_search_edate']) {
                                $where_query .= $_where." ct_request_wdate between '".$_GET['sel_search_sdate']." 00:00:00' and '".$_GET['sel_search_edate']." 23:59:59'";
                                $_where = " and ";
                            }

                            $count_query = $DB->count_query($query.$where_query);
                            $counts = $count_query;
                            $n_page = ceil($count_query / $n_limit_num);
                            if($pg=="") $pg = 1;
                            $n_from = ($pg - 1) * $n_limit;
                            $counts = $counts - (($pg - 1) * $n_limit_num);

                            unset($list);
                            $sql_query = $query.$where_query." group by a1.ot_code order by a1.ot_wdate desc limit ".$n_from.", ".$n_limit;
                            $list = $DB->select_query($sql_query);

                            if($list) {
                                foreach($list as $row) {
//								$pt_info = get_product_t_info($row['pt_idx']);
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" id="chk_all" name="chk_all[]" value="<?=$row['ot_code']?>">
                                        </td>
                                        <td class="text-center">
                                            <?=$row['ot_code']?>
                                        </td>
                                        <td class="text-center">
                                            <?=$row['ot_code']?>
                                        </td>
                                        <td class="text-center">
                                            <?=$row['ot_name']?>
                                        </td>
                                        <td class="text-center">
                                            <?= $row['ot_pt_name'] ?>
                                        </td>
                                        <td class="text-center">
                                            <?= number_format($row['ot_price']) ?>원
                                        </td>
                                        <td class="text-center">
                                            <?= $row['ot_qty'] ?>
                                        </td>
                                        <td class="text-center">
                                            <?=DateType($row['ot_wdate'], 1)?>
                                        </td>
                                        <td class="text-center" style="width: 80px;">
                                            <?=$arr_ct_status[$row['ct_status']]?>
                                        </td>
                                        <td class="text-center">
                                            <input type="button" class="btn btn-outline-secondary btn-sm" value="자세히" onclick="location.href='./exchange_form.php?act=view_order&ot_code=<?=$row['ot_code']?>&<?=$_get_txt.$_GET['pg']?>'" />
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
<?
include "./foot_inc.php";
?>