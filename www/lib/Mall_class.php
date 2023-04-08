<?php
//쇼핑몰
Class Mall_class
{
    protected $config;

    public function __construct($config)
    {
        $this->db = $config['db'];
        $this->mt_idx = $config['mt_idx'];
    }

    public function detail($idx)
    {
        $result = $this->product_info($idx);

        if($result == "false") {
            p_alert("존재하지 않는 상품입니다.", "./mall.php");
        }

        if($this->mt_idx > 0){
            //찜하기
		    $row = $this->db->fetch_query("select count(0) as cnt from wish_product_t where pt_idx = ".$idx." and mt_idx=".$this->mt_idx." and wpt_status='Y'");
		    $result['wish'] = $row['cnt'];
            //회원정보
            $result['member'] = $this->db->fetch_query("select * from member_t where idx=".$this->mt_idx);
        }else{
            $result['wish'] = 0;
        }        
        return $result;
    }

    //결제완료
    public function complete($ot_code)
    {
        $query = "select * from order_t where ot_code = '".$ot_code."' and mt_idx=".$this->mt_idx;
        $result['order'] = $this->db->fetch_query($query);
        $query = "select *, (select pt_image1 from product_t where idx = pt_idx) as pt_image from cart_t where ot_code =  '".$ot_code."' and mt_idx=".$this->mt_idx;
        $result['cart'] = $this->db->select_query($query);
        $result['delivery'] = $this->delivery($ot_code);
        return $result;
    }

    public function relation($idx, $table)
    {
        if(!$idx) return false;
        if($table == "content") {
			$query = "select * from contents_t where ct_type = 2 and idx = ".$idx;
            $result = $this->db->fetch_query($query);
			if($result['cc_relation']) {
				$query = "select *, (SELECT GROUP_CONCAT(pc_name SEPARATOR ',') FROM `product_category_t` WHERE idx IN(1,3)) as pc_name from product_t where pt_show = 'Y' and pt_sale_now = 'Y' and idx in(".$result['cc_relation'].") order by idx desc";
				$list = $this->db->select_query($query);
			}
        } else {
            $query = "select * from product_t where pt_show = 'Y' and pt_sale_now = 'Y' and idx = ".$idx;
            $result = $this->db->fetch_query($query);
            if($result['ct_id']) {
                $query = "select * from product_t where pt_show = 'Y' and pt_sale_now = 'Y' and ct_id = ".$result['ct_id']." and idx <> ".$idx." order by idx desc";
                $list = $this->db->select_query($query);
            }
        }
        return $list;
    }

    public function product_info($idx)
    {
        $result = $this->db->fetch_query("select *, idx as pt_idx from product_t where idx=".$idx);
        if($result['pt_show'] == 'N' || $result['pt_sale_now'] == "N") {
            return 'false';
        } else {
            return $result;
        }
    }

    public function product_like($idx) {
		$list = array();
		if($this->mt_idx){
			$query = "select * from wish_product_t where mt_idx = ".$this->mt_idx." and pt_idx = ".$idx;
			$list = $this->db->fetch_assoc($query);
		}
        return $list;
    }

	//장바구니
	public function cart($act, $arr)
	{        
		$product = $this->product_info($arr['pt_idx']);
        if($arr['ct_opt_qty'] > $product['pt_stock']){  //재고부족
            return 'stock';
        }

        
        $pt_option_select = $arr['pt_option_select1'];
        if($arr['pt_option_select2']) $pt_option_select = $pt_option_select.'/'.$arr['pt_option_select2'];
        if($arr['pt_option_select3']) $pt_option_select = $pt_option_select.'/'.$arr['pt_option_select3'];
        
        $cart_ing = $this->db->fetch_assoc("select * from cart_t where pt_idx = ".$arr['pt_idx']." and mt_idx=".$this->mt_idx." and ct_select = 0 and ct_direct != 1 and ct_status=0 ");
        if($cart_ing['idx'] > 0 && $cart_ing['ct_opt_qty']==$arr['ct_opt_qty'] && $cart_ing['ct_opt_value']==$pt_option_select && $arr['order_act']!='direct'){
            $this->db->update_query("cart_t", array('ct_opt_qty'=>$cart_ing['ct_opt_qty']+1), " idx = ".$cart_ing['idx']);
            return 'cart_ing';
        }else if(($cart_ing['idx'] > 0 && $cart_ing['ct_opt_value']==$pt_option_select) && $cart_ing['ct_opt_qty']!=$arr['ct_opt_qty'] && $arr['order_act']!='direct' ){
            $this->db->update_query("cart_t", array('ct_opt_qty'=>$arr['ct_opt_qty']), " idx = ".$cart_ing['idx']);
            return 'qty_change';
        }

        

		if($product['pt_option_type']=='2'){	//옵션 조합형
			$options = $this->option_select($arr['pt_idx'], $product['pt_option_type'], $arr);
			$opt_price = $options['pot_price'] - ($options['pot_price'] * ($product['pt_discount_per'] / 100));
		}else{	//옵션 단독형
			$opt_price = $product['pt_price'];
		}        
		$delivery = $this->db->fetch_assoc("select * from policy_t");

        

        if($arr['order_act']=='direct'){
			$this->db->del_query('order_t', "ot_code='".$ot_code."' and ot_status=1");
            $this->db->del_query('cart_t', "ot_code='".$ot_code."' and ct_direct=1");
            $this->cart_reset();
            $ot_code = get_ot_code();
        }else{
            $cart = $this->db->fetch_assoc("select * from cart_t where mt_idx=".$this->mt_idx." and ct_direct=0 and ct_select=0 and ct_status=0 limit 1");  //기존 주문번호 조회
            $ot_code = ($cart['ot_code']) ? $cart['ot_code'] : get_ot_code();
        }

		//$options['pot_price'];
		$field_arr = array();
        $field_arr['ot_code'] = $ot_code;
		$field_arr['ot_pcode'] = get_ot_pcode();
		$field_arr['mt_idx'] = $this->mt_idx;
		$field_arr['pt_idx'] = $arr['pt_idx'];
		$field_arr['pt_code'] = $product['pt_code'];
		$field_arr['pt_title'] = $product['pt_title'];
		$field_arr['pt_price'] = $opt_price;	//상품가
		if($arr['pt_option_name1']) $option_name = $arr['pt_option_name1'];
		if($arr['pt_option_name2']) $option_name .= '/'.$arr['pt_option_name2'];
		if($arr['pt_option_name1']) $option_name .= '/'.$arr['pt_option_name3'];
		$field_arr['ct_opt_name'] = $option_name;	//선택 옵션명
		if($arr['pt_option_select1']) $option_select = $arr['pt_option_select1'];
		if($arr['pt_option_select2']) $option_select .= '/'.$arr['pt_option_select2'];
		if($arr['pt_option_select3']) $option_select .= '/'.$arr['pt_option_select3'];
		$field_arr['ct_opt_value'] = $option_select;	//선택 옵션값
		$field_arr['ct_opt_price'] = $opt_price;	//선택 옵션가
		$field_arr['ct_opt_qty'] = $arr['ct_opt_qty'];	//선택 수량
		$field_arr['ct_price'] = $opt_price*$arr['ct_opt_qty'];		//총 금액 = 상품가 + 옵션가
		$field_arr['ct_delivery_default_price'] = $delivery['pt_delivery_price'];	//기본배송비
		$field_arr['ct_delivery_price_add'] = '';	//추가배송비
		$field_arr['ct_wdate'] = date('Y-m-d H:i:s');	//등록일시
        if($arr['order_act']=='direct'){    //바로구매
            $field_arr['ct_direct'] = '1';  //바로구매
            $field_arr['ct_select'] = '1';  //장바구니
            $field_arr['ct_status'] = '0';  //arr_ct_status 참조
            $field_arr['ct_select_wdate'] = date('Y-m-d H:i:s');
        }
		if($this->db->insert_query("cart_t", $field_arr)){
			return true;
		}else{
			return false;
		}
	}


    //장바구니 선택필드 초기화
    public function cart_reset()
    {
        $this->db->update_query("cart_t", array('ct_select'=>'0'), "mt_idx=".$this->mt_idx." and ct_select=1");
    }

	//옵션 검색
	public function option_select($idx, $type, $arr)
	{

		if($type=='2'){
			if($arr['pt_option_select1']) $pot_name = $arr['pt_option_select1']."|:|";
			if($arr['pt_option_select2']){
                 $pot_name .= $arr['pt_option_select2']."|:|";
            }else{
                $pot_name .= "|:|";
            }
			if($arr['pt_option_select3']){
                $pot_name .= $arr['pt_option_select3']."|:|";
            }else{
                $pot_name .= "|:|";
            }
			return $this->db->fetch_assoc("select * from product_option_t where pt_idx='".$idx."' and pot_name='".$pot_name."'");
		}


	}

    public function act($act)
    {
        $list = $this->$act();
        if(is_array($list)){
            return $list;
        }else{
            return array();
        }
    }

    //배송지
    public function delivery($ot_code)
    {
        $query = "select * from order_delivery_t where ot_code='".$ot_code."'";
        $result = $this->db->fetch_query($query);
        return $result;
    }

    //인기 상품 최대 10개
    public function best()
    {
        $query = "select * from product_t where pt_show = 'Y' and pt_sale_now = 'Y' and pt_wdate BETWEEN DATE_ADD(NOW(),INTERVAL -1 MONTH ) AND NOW() order by pt_view desc limit 5";
        $list_view = $this->db->select_query($query);
        $query = "select product_t.* from wish_product_t left join product_t on product_t.idx = wish_product_t.pt_idx 
            where wpt_status = 'Y' and pt_show = 'Y' and pt_sale_now = 'Y' and pt_wdate BETWEEN DATE_ADD(NOW(),INTERVAL -1 MONTH ) AND NOW() group by pt_idx limit 5";
        $list_wish = $this->db->select_query($query);
        $list = array_merge($list_view, $list_wish);
        return $list;
    }

    //신규 상품 최대 10개
    public function new()
    {
        $query = "select * from product_t where pt_show='Y' and pt_sale_now = 'Y' order by idx desc limit 10";
        $list = $this->db->select_query($query);        
        return $list;
    }

    public function get_policy() {
        $query = "select * from policy_t";
        $list = $this->db->fetch_query($query);
        return $list;
    }

    public function get_banner() {
        $query = "select * from banner_t where bt_table = 'P' order by bt_orderby";
        $list = $this->db->select_query($query);
        return $list;
    }

    public function get_exhibition() {
        $query = "select * from banner_t where bt_table = 'E' and bt_main_status = 'Y' order by bt_orderby";
        $list = $this->db->fetch_query($query);
        return $list;
    }

    public function paging($url, $cur_page, $total_page, $detail) {
        $html = "";
        if($detail == true) {
            $detail = "#mall_sec3";
        } else {
            $detail = "";
        }
        if($cur_page > 1) {
            $html .= '<li><a href="'.$url.($cur_page-1).$detail.'"><img src="./img/m_btn_left.png" alt="왼쪽 버튼" style="width: 27px;"></a></li>';
        } else {
            $html .= '<li><a href="'.$url.$detail.'" aria-disabled="true"><img src="./img/m_btn_left.png" alt="왼쪽 버튼" style="width: 27px;"></a></li>';
        }
        $start_page = ( ( (int)( ($cur_page - 1 ) / 5 ) ) * 5 ) + 1;
        $end_page = $start_page + 5;
        if($end_page >= $total_page) $end_page = $total_page;
        if($total_page > 1){
            for ($i=$start_page;$i<=$end_page;$i++) {
                if($cur_page != $i) {
                    $html .= "<li><a href=\"".$url.$i.$detail."\">".$i."</a></li>";
                } else if($i > 0) {
                    $html .= "<li aria-current=\"page\"><a class=\"on\" href=\"".$url.$i.$detail."\">".$i."</a></li>";
                }
            }
        }
        if($cur_page < $total_page && $total_page > 1) {
            $html .= "<li><a aria-label=\"다음\" href=\"".$url.($cur_page+1).$detail."\"><img src=\"./img/m_btn_right.png\" alt=\"오른쪽 버튼\" style=\"width: 27px;\"></a></li>";
        } else {
            $html .= "<li><a href=\"".$url.$detail."\" tabindex=\"-1\" aria-disabled=\"true\"><img src=\"./img/m_btn_right.png\" alt=\"오른쪽 버튼\" style=\"width: 27px;\"></a></li>";
        }
        return $html;
    }
}
?>
