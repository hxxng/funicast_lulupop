<?php
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

if($_POST['act']=='change_status'){	//상품 상태값 변경 
	$DB->update_query('product_t', array('pt_sale_now'=>$_POST['pt_sale_now']),  "idx='".$_POST['idx']."'");
	echo json_encode(array('result' => '_ok', 'msg'=>'변경 되었습니다.'));	
}

if($_POST['act']=='pc_view'){  //카테고리 노출 상태값 변경
    $DB->update_query('product_category_t', array('pc_view'=>$_POST['pc_view']), "idx='".$_POST['pc_idx']."'");
    echo json_encode(array('result' => '_ok', 'msg'=>'변경 되었습니다.'));
}

if($_POST['act']=='pc_del'){   //카테고리 삭제
    $pc_idx_arr = array_filter(explode('|', $_POST['pc_idx_obj'])); //배열안 빈값 제거(| 기준으로 별열로 변환)
    $pc_idx_in = implode(', ', $pc_idx_arr);    //배열을 ,문자열로 변환
    $DB->del_query('product_category_t', 'idx in ('.$pc_idx_in.')');
    echo json_encode(array('result' => '_ok', 'msg'=>'삭제 되었습니다.'));
}

if($_POST['act']=='pc_update'){    //등록 및 수정
    if(!$_POST['pc_name_m'] || !$_POST['pc_orderby_m'] || $_POST['pc_depth_m'] == ""){
        echo json_encode(array('result' => '_false', 'msg'=>'빈값이 있습니다.'));
        exit;    
    }

    if($_POST['pc_idx_m']){
        $where_is = " idx !='".$_POST['pc_idx_m']."' and (pc_name='".$_POST['pc_name_m']."')";
    }else{
        $where_is = " (pc_name='".$_POST['pc_name_m']."')";
    }
    $count = $DB->fetch_query("select count(0) as cnt from product_category_t where ".$where_is);
    if($count['cnt']> 0 ) {
        echo json_encode(array('result' => '_false', 'msg'=>'중복된 이름 혹은 출력 순위가 있습니다.'));
        exit;    
    }

    if($_POST['pc_idx_m']) {
        $row = $DB->fetch_assoc("select * from product_category_t where idx = ".$_POST['pc_idx_m']);
    }

    $set_arr = array(
        'pc_name' => $_POST['pc_name_m'],
        'pc_orderby' => $_POST['pc_orderby_m'],
        'pc_depth' => $_POST['pc_depth_m']
    );
    if($_POST['act2'] == "write") {
        if($_POST['pc_depth_m'] == 1) {
            if(!$_POST['pc_m_idx_m']) {
                $set_arr['pc_m_idx'] = $_POST['pc_idx_m'];
            }
        } else if($_POST['pc_depth_m'] == 2) {
            $set_arr['pc_m_idx'] = $row['pc_m_idx'];
            $set_arr['pc_s_idx'] = $_POST['pc_idx_m'];
        }
    }

    if($_POST['pc_idx_m'] && $_POST['act2'] != "write"){
        $DB->update_query('product_category_t', $set_arr, "idx='".$_POST['pc_idx_m']."'");
    }else{
        $DB->insert_query('product_category_t', $set_arr);
    }
    echo json_encode(array('result' => '_ok', 'msg'=>'등록/수정 되었습니다.'));
}
if($_POST['type']=='pc_modal'):  //모달 창
    if($_POST['idx']) $row = $DB->fetch_query("select * from product_category_t where idx='".$_POST['idx']."'");    
?>
	<div class="modal-header">
		<h5 class="modal-title" id="staticBackdropLabel">카테고리 추가 / 수정</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body">
    <form name="f_product_category">
    <input type="hidden" name="act" value="pc_update">
	<input type="hidden" name="act2" value="<?=$_POST['act']?>" />
	<input type="hidden" name="pc_idx_m" id="pc_idx_m" value="<?=$row['idx']?>" />
	<input type="hidden" name="pc_m_idx_m" id="pc_m_idx_m" value="<?=$row['m_idx']?>" />
	<input type="hidden" name="pc_s_idx_m" id="pc_s_idx_m" value="<?=$row['s_idx']?>" />
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <div class="form-group row align-items-center mb-0">
                    <label class="col-sm-2 col-form-label">분류명 <b class="text-danger"></b></label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="pc_name_m" id="pc_name_m" value="<? if($_POST['act'] != "write") echo $row['pc_name']?>">
                    </div>
                </div>
            </li>
            <li class="list-group-item">
            <div class="form-group row align-items-center mb-0">
                <label class="col-sm-2 col-form-label">depth순서 <b class="text-danger"></b></label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" name="pc_depth_m" id="pc_depth_m" readonly value="<? if($_POST['act'] == "write") { echo $row['pc_depth']+1; } else {echo $row['pc_depth'];} if(!$_POST['idx']) echo 0;?>">
                </div>
            </div>
            </li>
            <li class="list-group-item">
                <div class="form-group row align-items-center mb-0">
                    <label class="col-sm-2 col-form-label">노출순위 <b class="text-danger"></b></label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="pc_orderby_m" id="pc_orderby_m" value="<? if($_POST['act'] != "write") echo $row['pc_orderby']?>">
                    </div>
                </div>
            </li>
        </ul>
        <p class="p-3 mt-3 text-center">
            <input type="button" value="저장하기" onclick="product_category_update()" class="btn btn-outline-secondary mx-2" />            
        </p>
	</form>
	</div>                
<?php

endif;
?>