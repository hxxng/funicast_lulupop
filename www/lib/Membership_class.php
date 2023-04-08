<?php
//결제 (멤버십)

Class Membership_class
{
    protected $config;

    public function __construct($config)
    {
        $this->db = $config['db'];
        $this->mt_idx = $config['mt_idx'];
        $this->act = $config['act'];  //콘텐츠 결제, 멤버쉽 결제, 상품결제등등
    }

    //결제완료
    public function success($obj)
    {
        if($this->act == 'content') $result = $this->content_update('complete', $obj);
    }

    //결제전
    public function before($obj)
    {
        if($obj['pay_type'] == "프리미엄") {
            $obj['name'] = "프리미엄 구독 가입";
        } else if($obj['pay_type'] == "일반") {
            $obj['name'] = "멤버십 구독 가입";
        } else {
            $obj['name'] = "첫 달 무료 멤버십 가입";
        }
        if($this->act == 'content') $result = $this->content_update('before', $obj);
        return $result;
    }

    public function content_update($type, $obj)
    {
        if($type=='before'){
            $member = $this->member_info();
            if($member['idx'] < 1) return array('result'=>'false', 'key'=>'member');
        }
        $set = array();
        if($type=='before'){    //결제전
            $set['mt_idx'] = $member['idx'];
            $set['cpt_state'] = 1;                  //1:결제대기, 2:완료, 3:취소
            $set['cpt_type'] = $obj['cpt_type'];    //1:자동결제, 2:일반
            $set['ct_id'] = $obj['ct_idx'];
            $set['cpt_name'] = $obj['name'];
            $set['cpt_price'] = $obj['amount'];
            if($this->db->insert_query("contents_payment_t", $set)){
                $data_arr = array();
                $this->db->insert_id();
                $data_arr['merchant_uid'] = 'content_'.$this->db->insert_id();
                $data_arr['customer_uid'] = "customer_".$obj['customer_uid'];
                $data_arr['name'] = $obj['name'];
                $data_arr['amount'] = $obj['amount'];
                $data_arr['buyer_email'] = $member['mt_id'];
                $data_arr['buyer_name'] = $member['mt_nickname'];
                return array('result'=>'true', 'key'=>'payment', 'data'=>$data_arr);
            }else{
                return array('result'=>'false', 'key'=>'payment');
            }
        }
        if($type=='complete'){  //결제후
            $set = $obj;
            if($this->db->update_query("contents_payment_t", $set, "idx = ".$obj['idx'])){
                return array('result'=>'true', 'key'=>'payment');
            }else{
                return array('result'=>'false', 'key'=>'payment');
            }
        }
    }

    public function member_info()
    {
        return $this->db->fetch_query("select * from member_t where idx=".$this->mt_idx);
    }

    public function PostMethodData($url, $query=array(), $mrefer='', $magent='',$mcookie='', $ssl=false, $buffAll=false, $access_token)
    {
        $header = array(
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Accept-Language: ja,en-US;q=0.8,en;q=0.6",
            "Accept-Charset: Shift_JIS,utf-8;q=0.7,*;q=0.3",
            "Content-Type: application/x-www-form-urlencoded",
            "Authorization: ".$access_token,
        );

        $fields_string='';

        foreach($query as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        $fields_string = rtrim($fields_string, '&');

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $header );
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
        if($ssl) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if($buffAll) curl_setopt($ch,CURLOPT_HEADER,1);
        else  curl_setopt($ch,CURLOPT_HEADER,0);
        if($magent) curl_setopt($ch, CURLOPT_USERAGENT, $magent);
        if($mrefer) curl_setopt($ch, CURLOPT_REFERER, $mrefer);
        if($mcookie) curl_setopt($ch, CURLOPT_COOKIE, $mcookie);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

        $result = curl_exec($ch);

        curl_close($ch);

        //echo $url."<br>".$result;


        return $result;
    }
    public function PostMethodData2($url, $query, $mrefer='', $magent='',$mcookie='', $ssl=false, $buffAll=false, $access_token)
    {
        $header = array(
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Accept-Language: ja,en-US;q=0.8,en;q=0.6",
            "Accept-Charset: Shift_JIS,utf-8;q=0.7,*;q=0.3",
            "Content-Type: application/json",
            "Authorization: ".$access_token,
        );

        $fields_string='';

//        foreach($query as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
//        $fields_string = rtrim($fields_string, '&');

        $body = json_encode($query);

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $header );
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
        if($ssl) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if($buffAll) curl_setopt($ch,CURLOPT_HEADER,1);
        else  curl_setopt($ch,CURLOPT_HEADER,0);
        if($magent) curl_setopt($ch, CURLOPT_USERAGENT, $magent);
        if($mrefer) curl_setopt($ch, CURLOPT_REFERER, $mrefer);
        if($mcookie) curl_setopt($ch, CURLOPT_COOKIE, $mcookie);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

        $result = curl_exec ($ch);
        curl_close($ch);

        return $result;
    }

}