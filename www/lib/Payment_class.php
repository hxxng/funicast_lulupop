<?php
//결제
Class Payment_class extends Login_chk_class
{
    protected $config;

    public function __construct($config)
    {
        $this->db = $config['db'];
        $this->mt_idx = $config['mt_idx'];
        $this->act = $config['act'];  //콘텐츠 결제, 멤버쉽 결제, 상품결제등등
        $this->member = parent::login_chk();
    }

    //결제완료
    public function success($obj)
    {
        if($this->act == 'content') $result = $this->content_update('complete', $obj);
		if($this->act == 'product') $result = $this->product_update('complete', $obj);
    }

    //결제전
    public function before($obj)
    {
        if($this->act == 'content') $result = $this->content_update('before', $obj);
		if($this->act == 'product') $result = $this->product_update('before', $obj);
        return $result;
    }

    //상품 결제
    public function product_update($type, $obj)
    {
		global $chk_mobile;
        if($type=='before'){
            $member = $this->member;            
            if($member['idx'] < 1) return array('result'=>'false', 'key'=>'member');
            $product = $this->product_info($obj['ot_code']);            
            if(count($product) < 1) return array('result'=>'false', 'key'=>'product');
        }        
        $set = array();
        if($type=='before'){    //결제전
            $data_arr = array();            
            $data_arr['merchant_uid'] = 'product_'.$obj['ot_code'];
            $data_arr['name'] = $obj['ot_code'];
            $data_arr['amount'] = $obj['amount'];
            $data_arr['buyer_email'] = $member['mt_id'];
            $data_arr['buyer_name'] = $member['mt_nickname'];
			$data_arr['m_redirect_url'] = '';
			if($chk_mobile) $data_arr['m_redirect_url'] = STATIC_HTTP.'/models/payments/complete.php';
            return array('result'=>'true', 'key'=>'payment', 'data'=>$data_arr);
        }
        if($type=='complete'){  //결제후
            $set = $obj;
            $set['ot_status'] = '2';
			if($chk_mobile){
				if($this->db->update_query("order_t", $set, "ot_code='".$obj['ot_code']."'")){
					return array('result'=>'true', 'key'=>'payment');
				}else{
					return array('result'=>'false', 'key'=>'payment');
				}
			}else{
	            $set['mt_idx'] = $this->member['idx'];
				if($this->db->insert_query("order_t", $set)){
					return array('result'=>'true', 'key'=>'payment');
				}else{
					return array('result'=>'false', 'key'=>'payment');
				}
			}
        }
    }

    public function content_update($type, $obj)
    {
		global $chk_mobile;
		$member = $this->member;
        if($type=='before'){            
            if($member['idx'] < 1) return array('result'=>'false', 'key'=>'member');
            $content = $this->content_info($obj['ct_idx']);
            if($content['idx'] < 1) return array('result'=>'false', 'key'=>'content');
        }
        $set = array();
        if($type=='before'){    //결제전
            $set['mt_idx'] = $member['idx'];
            $set['cpt_state'] = 1;                  //1:결제대기, 2:완료, 3:취소
            $set['cpt_type'] = $obj['cpt_type'];    //1:자동결제, 2:일반
            $set['ct_id'] = $obj['ct_idx'];
            $set['cpt_name'] = $content['ct_title'];
            $set['cpt_price'] = $content['ct_price'];
            if($this->db->insert_query("contents_payment_t", $set)){
                $data_arr = array();
                $this->db->insert_id();
                $data_arr['merchant_uid'] = 'content_'.$this->db->insert_id();
                $data_arr['name'] = $content['ct_title'];
                $data_arr['amount'] = $content['ct_price'];
                $data_arr['buyer_email'] = $member['mt_id'];
                $data_arr['buyer_name'] = $member['mt_nickname'];
				$data_arr['m_redirect_url'] = '';
				if($chk_mobile) $data_arr['m_redirect_url'] = STATIC_HTTP.'/models/payments/complete.php';
                return array('result'=>'true', 'key'=>'payment', 'data'=>$data_arr);
            }else{
                return array('result'=>'false', 'key'=>'payment');
            }
        }
        if($type=='complete'){  //결제후
            $set = $obj;
            if($this->db->update_query("contents_payment_t", $set, "idx = ".$obj['idx'])){
				$row_cpt = $this->db->fetch_query("select * from contents_payment_t where idx=".$obj['idx']);
				$this->db->insert_query("contents_download_t", array('mt_idx'=>$row_cpt['mt_idx'] ,'ct_idx'=>$row_cpt['ct_id'] ,'cdt_wdate'=>date('Y-m-d H:i:s')));
                return array('result'=>'true', 'key'=>'payment');
            }else{
                return array('result'=>'false', 'key'=>'payment');
            }
        }
    }

    public function select_payment($idx)
    {
        if($this->act == 'content') return $this->db->fetch_query("select * from contents_payment_t where idx=".$idx);
        if($this->act == 'product') return $this->db->select_query("select * from cart_t where ot_code='".$idx."'");
    }

    public function product_info($ot_code)
    {
        return $this->db->select_query("select * from cart_t where ot_code='".$ot_code."'");
    }

    public function content_info($idx)
    {
        return $this->db->fetch_query("select * from contents_t where idx=".$idx." and ct_type=2");
    }
}