<?php
//마이페이지
Class Mypage_class
{
    protected $config;

    public function __construct($config)
    {
        $this->db = $config['db'];
        $this->mt_idx = $config['mt_idx'];
    }

    public function get_point($idx) {
        $list = $this->db->fetch_assoc("select * from member_t where idx = ".$idx);

//        $query = "select * from point_log_t where mt_idx = ".$idx;
//        $list['list'] = $this->db->select_query($query);
//
//        $query = "select sum(plt_price) as plus from point_log_t where plt_type = 'P' and mt_idx = ".$idx;
//        $plus = $this->db->fetch_query($query);
//        $query = "select sum(plt_price) as minus from point_log_t where plt_type = 'M' and mt_idx = ".$idx;
//        $minus = $this->db->fetch_query($query);
//        $sum = $plus['plus'] - $minus['minus'];
//        $list['sum'] = $sum;

        return $list;
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

}
?>
