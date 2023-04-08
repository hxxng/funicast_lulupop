<?php
Class Point_class extends Login_chk_class
{
    protected $config;

    public function __construct($config)
    {
        $this->db = $config['db'];
        $this->mt_idx = $config['mt_idx'];
        $this->member = parent::login_chk();        
    }

    public function insert_point($arr)
    {
        if($this->mt_idx < 1){ return false; }
        if($arr['point']==0){ return false; }

        // 회원포인트
        $mt_point = $this->get_point_sum($this->mt_idx);

        //이미 등록된 내용확인

        //포인트 생성
        $plt_expire_date = date("Y-m-d",strtotime("+1 year"));
        $plt_expired = 0;
        if($arr['point'] < 0) {
            $plt_expired = 1;
            $plt_expire_date = date('Y-m-d');
        }
        $plt_mt_point = $mt_point + $arr['point'];

        $set = array();
        $set['mt_idx'] = $this->mt_idx;
        if($arr['pt_idx']) $set['pt_idx'] = $arr['pt_idx'];
        if($arr['ot_code']) $set['ot_code'] = $arr['ot_code'];
        if($arr['ot_pcode']) $set['ot_pcode'] = $arr['ot_pcode'];

        $set['plt_type'] = ($arr['point'] < 0 ) ? 'M' : 'P';//$arr['plt_type'];   //P적립, M 차감
        $set['plt_price'] = $arr['point'];
        $set['plt_use_point'] = 0;
        $set['plt_mt_point'] = $plt_mt_point;
        $set['plt_expired'] = $plt_expired;
        $set['plt_expire_date'] = $plt_expire_date;
        $set['plt_memo'] = $arr['plt_memo'];
        $set['plt_status'] = $arr['plt_status'];
        $set['plt_wdate'] = date('Y-m-d H:i:s');
        $this->db->insert_query('point_log_t', $set);

        // 포인트를 사용한 경우 포인트 내역에 사용금액 기록
        if($arr['point'] < 0) {
            $this->insert_use_point($this->mt_idx, $arr['point']);
        }

        $this->db->update_query('member_t', array('mt_point'=>$plt_mt_point), "idx=".$this->mt_idx);
        return true;
    }

    public function get_point_sum($mt_idx)
    {
        $expire_point = $this->get_expire_point($mt_idx);  //소멸 포인트
        if($expire_point > 0) {

            $content = '포인트 소멸';            
            $point = $expire_point * (-1);
            $plt_mt_point = $this->member['mt_point'] + $point;
            $plt_expire_date = date('Y-m-d');
            $plt_expired = 1;

            $sql = " insert into point_log_t
                        set mt_idx = ".$mt_idx.",
                            plt_type = 'M',
                            plt_wdate = '".date('Y-m-d H:i:s')."',
                            plt_memo = '".addslashes($content)."',
                            plt_price = '$point',
                            plt_use_point = '0',
                            plt_mt_point = '$plt_mt_point',
                            plt_expired = '$plt_expired',
                            plt_expire_date = '$plt_expire_date'
                     ";
            $this->db->db_query($sql);

            // 포인트를 사용한 경우 포인트 내역에 사용금액 기록
            if($point < 0) {
                $this->insert_use_point($mt_idx, $point);
            }
        }

        // 유효기간이 있을 때 기간이 지난 포인트 expired 체크
        $sql = " update point_log_t
                    set plt_expired = '1'
                    where mt_idx = ".$mt_idx."
                      and plt_expired <> '1'
                      and plt_expire_date <> '9999-12-31'
                      and plt_expire_date < '".date('Y-m-d')."' ";
        $this->db->db_query($sql);

        // 포인트합
        $sql = " select sum(plt_price) as sum_po_point
                    from point_log_t
                    where mt_idx = '$mt_idx' ";
        $row = $this->db->fetch_query($sql);

        return $row['sum_po_point'];

    }

    // 사용포인트 입력
    function insert_use_point($mt_idx, $point, $plt_id='')
    {                
        $sql_order = " order by plt_expire_date asc, idx asc ";
        
        $point1 = abs($point);
        $sql = " select idx, plt_price, plt_use_point
                    from point_log_t
                    where mt_idx = ".$mt_idx."
                    and idx <> '".$plt_id."'
                    and plt_expired = '0'
                    and plt_price > plt_use_point
                    $sql_order ";
        $result = $this->db->select_query($sql);
        foreach($result as $row){
            $point2 = $row['plt_price'];
            $point3 = $row['plt_use_point'];

            if(($point2 - $point3) > $point1) {
                $sql = " update point_log_t
                            set plt_use_point = plt_use_point + ".$point1."
                            where idx = ".$row['idx'];
                $this->db->db_query($sql);
                break;
            } else {
                $point4 = $point2 - $point3;
                $sql = " update point_log_t
                            set plt_use_point = plt_use_point + '$point4',
                                plt_expired = '100'
                            where idx = ".$row['idx'];
                $this->db->db_query($sql);
                $point1 -= $point4;
            }
        }
    }

    //소멸 포인트
    function get_expire_point($mt_idx)
    {
        $query = "select sum(plt_price - plt_use_point) as sum_point from point_log_t where mt_idx = ".$mt_idx." and plt_expired = '0' and plt_expire_date <> '9999-12-31' and plt_expire_date < '".date('Y-m-d')."' ";
        $row = $this->db->fetch_query($query);
        return $row['sum_point'];
    }

    

}
?>