<?php
include "./head_inc.php";
$chk_menu = '11';
$chk_sub_menu = '1';
include "./head_menu_inc.php";

$n_limit = $n_limit_num;
$pg = $_GET['pg'];
$_colspan_txt = "7";
$_get_txt = "sel_search=".$_GET['sel_search']."&search_txt=".$_GET['search_txt']."&pg=";
?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">알림 관리</h4>
                        <div class="p-3 float-left">
                            <form method="get" name="frm_search" id="frm_search" action="<?=$_SERVER['PHP_SELF']?>" class="form-inline" onsubmit="return frm_search_chk(this);">
                                <div class="form-group mx-sm-1">
                                    <select name="sel_search" id="sel_search" class="form-control form-control-sm">
                                        <option value="all">통합검색</option>
                                        <option value="ft_title">제목</option>
                                    </select>
                                </div>

                                <div class="form-group mx-sm-1">
                                    <input type="text" class="form-control form-control-sm" style="width:200px;" name="search_txt" id="search_txt" value="<?=$_GET['search_txt']?>" />
                                </div>
                                <div class="form-group mx-sm-1">
                                    <input type="submit" class="btn btn-info" value="검색" />
                                </div>
                                <div class="form-group mx-sm-1">
                                    <input type="button" class="btn btn-secondary" value="초기화" onclick="location.href='./push_list.php'" />
                                </div>
                            </form>
                            <script type="text/javascript">
                                function frm_search_chk(f) {
                                    // if(f.search_txt.value=="") {
                                    //     alert("검색어를 입력바랍니다.");
                                    //     f.search_txt.focus();
                                    //     return false;
                                    // }
                                    return true;
                                }
                                <? if($_GET['sel_search']) { ?>$('#sel_search').val('<?=$_GET['sel_search']?>');<? } ?>
                            </script>
                        </div>
                        <div class="p-3 float-right">
                            <form class="form-inline">
                                <div class="form-group mx-sm-1">
                                    <input type="button" class="btn btn-primary" value="알림 추가" onclick="f_push_content('add')">
                                </div>
                                <div class="form-group mx-sm-1">
                                    <input type="button" class="btn btn-secondary" value="알림 삭제" id="delete_btn" onclick="select_delete()">
                                </div>
                            </form>
                        </div>

                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="chk_all" />
                                </th>
                                <th class="text-center">
                                    등록일
                                </th>
                                <th class="text-center">
                                    발송일
                                </th>
                                <th class="text-center">
                                    제목
                                </th>
                                <th class="text-center">
                                    짧은내용
                                </th>
                                <th class="text-center">
                                    내용
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
							    select *, a1.idx as ft_idx from pushnotification_t a1
						    ";
                            $query_count = "
							    select count(*) from pushnotification_t a1
						    ";

                            if($_GET['search_txt']) {
                                if($_GET['sel_search']=="all") {
                                    $where_query .= $_where."(instr(ft_title, '".$_GET['search_txt']."'))";
                                } else {
                                    $where_query .= $_where."instr(".$_GET['sel_search'].", '".$_GET['search_txt']."')";
                                }
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
                                        <td class="text-center" style="width: 5%;">
                                            <input type="checkbox" name="chk_box" id="chk_box_<?=$row['ft_idx']?>" />
                                        </td>
                                        <td class="text-center" style="width: 13%;">
                                            <?php echo $row['pst_wdate'];?>
                                        </td>
                                        <td class="text-center" style="width: 13%;">
                                            <?php echo $row['pst_sdate'];?>
                                        </td>
                                        <td class="text-center">
                                            <?=$row['pst_title']?>
                                        </td>
                                        <td class="text-center">
                                            <?=$row['pst_shot_memo']?>
                                        </td>
                                        <td class="text-center">
                                            <?=cut_str(get_text($row['pst_content']), 0, 20, '...')?>
                                        </td>
                                        <td class="text-center" style="width: 13%;">
                                            <input type="button" class="btn btn-outline-secondary btn-sm" value="수정" onclick="f_push_content('<?=$row['ft_idx']?>')" />
                                            <input type="button" class="btn btn-outline-danger btn-sm" value="삭제" onclick="f_post_del('./push_update.php', '<?=$row['ft_idx']?>');" />                                        
                                        </td>
                                    </tr>
                                    <script>
                                        <? if($row['ft_orderby']) { ?> $('#ft_orderby_<?=$row['ft_idx']?>').val('<?=$row['ft_orderby']?>').prop("selected", true);<? } ?>
                                    </script>
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
    $(document).ready(function(){
        $("input[name='chk_box']").removeAttr("checked");
    });

    function f_checkbox_all(obj) {
        $('input:checkbox[name="'+obj+'"]').each(function() {
            if($(this).prop('checked')==true) {
                $(this).prop('checked', false);
            } else {
                $(this).prop('checked', true);
            }
        });

        return false;
    }
    $("#chk_all").on('click', function(){
        f_checkbox_all('chk_box');
    });

    // 체크 시 checked 옵션 생성안돼서 만든 함수
    $("input[name='chk_box']").on('click', function(){
        var id= this.id;
        if($("#"+id).prop('checked')==true) {
            $("#"+id).attr("checked", true);
        } else {
            $("#"+id).removeAttr("checked");
        }
    });

    var previous;
    $("select[name=ft_orderby]").on('focus', function () {
        previous = this.value;
    }).change(function() {
        var idx = this.id;
        idx = idx.replace("ft_orderby_", "");
        if(confirm("순위를 변경하시겠습니까?"))
        {
            $.ajax({
                type : 'post',
                url : './push_update.php',
                dataType : 'json',
                data : { act : 'order_update', ft_orderby : this.value, idx : idx},
                success : function(d, s){
                    if(d['result'] == "_ok")
                    {
                        alert(d['msg']);
                        location.reload();
                    }
                },
                cache : false
            });
        }
        else{
            $("#ft_orderby_"+idx).val(previous);
            // previous = this.value;
        }
    });

    function select_delete()
    {
        var list = $("input[name='chk_box']");
        var ids = [];
        for(var i=0; i<list.length; i++)
        {
            if($("#"+list[i].id).is(":checked"))
            {
                var id = list[i].id;
                id = id.replace("chk_box_", "");
                ids.push(id);
            }
        }
        $.ajax({
            type : 'post',
            url : './push_update.php',
            dataType : 'json',
            data : { act : 'select_delete', idx : ids},
            success : function(d, s){
                if(d['result'] == "_ok")
                {
                    alert(d['msg']);
                    location.reload();
                }
            },
            cache : false
        });
        ids = [];
    }
</script>
<?
include "./foot_inc.php";
?>