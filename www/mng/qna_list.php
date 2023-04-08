<?
include "./head_inc.php";
$chk_menu = '10';
include "./head_menu_inc.php";

$n_limit = $n_limit_num;
$pg = $_GET['pg'];
$_colspan_txt = "8";
$_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&sel_search_sdate=".$_GET['sel_search_sdate']."&sel_search_edate=".$_GET['sel_search_edate']."&pg=";
?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"> C/S 관리</h4>
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
                                            <div class="col-sm-12 text-center">
                                                <input type="submit" class="btn btn-primary" id="search_btn" value="검색" />
                                                <input type="button" class="btn btn-secondary ml-2" value="초기화" onclick="location.href='./qna_list.php'" />
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
                                <th class="text-center">
                                    상태
                                </th>
                                <th class="text-center" style="width:500px;">
                                    문의 내용
                                </th>
                                <th class="text-center">
                                    작성자
                                </th>
                                <th class="text-center">
                                    작성일
                                </th>
                                <th class="text-center">
                                    관리
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?
                            $_where = " where ";
                            $where_query = "";
                            $query = "
                                SELECT a1.*, a1.idx as qt_idx, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname
                                FROM qna_t a1
                            ";
                            $query_count = "
                                SELECT count(*), a1.*, a1.idx as qt_idx, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname
                                FROM qna_t a1
                            ";

                            if($_GET['search_txt']) {
                                $where_query .= $_where."(instr(a1.qt_content, '".$_GET['search_txt']."') or instr(mt_nickname, '".$_GET['search_txt']."'))";
                                $_where = " and ";
                            }
                            if($_GET['sel_search_sdate'] && $_GET['sel_search_edate']) {
                                $where_query .= $_where." qt_wdate between '".$_GET['sel_search_sdate']." 00:00:00' and '".$_GET['sel_search_edate']." 23:59:59'";
                                $_where = " and ";
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

                            if($list) {
                                foreach($list as $row) {
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="chk_box" id="chk_box_<?= $row['qt_idx'] ?>"/>
                                        </td>
                                        <td class="text-center">
                                            <?
                                            if($row['qt_status'] == 1) echo "답변대기"; else echo "답변완료";
                                            ?>
                                        </td>
                                        <td>
                                            <?=cut_str(get_text($row['qt_content']), 0, 65, '...')?>
                                        </td>
                                        <td class="text-center">
                                            <?=$row['mt_nickname']?>
                                        </td>
                                        <td class="text-center">
                                            <?=DateType($row['qt_wdate'],8)?>
                                        </td>
                                        <td class="text-center">
                                            <input type="button" class="btn btn-outline-secondary btn-sm" value="상세보기" onclick="location.href='./qna_form.php?act=update&qt_idx=<?=$row['qt_idx']?>&<?=$_get_txt.$_GET['pg']?>'" />
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
                url: './qna_update.php',
                dataType: 'json',
                data: {act: 'select_delete', idx: ids},
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