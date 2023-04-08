<?php
//로그인 체크 클래스
Class Login_chk_class
{
    protected $config;

    public function __construct($config)
    {
        $this->db = $config['db'];
        $this->mt_idx = $config['mt_idx'];
    }

    public function act($act)
    {
        $list = $this->$act();
        return $list;
    }

    public function login_chk()
    {
        if($this->mt_idx < 1) {
            return array();
        } else {
            $list = $this->db->fetch_assoc("select * from member_t where mt_level = 2 and idx=".$this->mt_idx);
            return $list;
        }
    }

    public function get_info($idx) {
        $query = "select * from member_t where idx = ".$idx;
        return $this->db->fetch_assoc($query);
    }


    /* 로그인체크 사용 예제
    $objLogin = new Login_chk_class(array('db'=>$DB, 'mt_idx'=>$_SESSION['_mt_idx']));
    $login_chk = $objLogin->act('login_chk');
    if($login_chk > 0) {
       로그인된 사용자 있음
    }
    */

    }
?>