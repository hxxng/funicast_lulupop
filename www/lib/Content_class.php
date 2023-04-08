<?php
//콘텐츠 

Class Content_class
{
    protected $config;

    public function __construct($config)
    {
        $this->db = $config['db'];
        $this->mt_idx = $config['mt_idx'];
    }

    //세션
    public function player($ct_idx, $csdt_idx='')
    {
        $result['detail'] = $this->contents_info($ct_idx);
        $result['section'] = $this->section($ct_idx, 'array');
        $cnt = 0;        
        foreach($result['section'] as $row){
            $section_detail_result = $this->section_detail($row['idx'], 'array');
            if($cnt == 0) $section_player = $section_detail_result;
            $cnt += count($section_detail_result);
        }
        $result['section_detail_count'] = $cnt;
        if($csdt_idx > 0){
            $result['section_player'] = $this->section_detail($csdt_idx);
        }else{
            $result['section_player'] = $section_player[0];
        }
        $result['play_log'] = $this->play_info(array('ct_idx'=> $ct_idx, 'cst_idx'=> $result['section_player']['cst_idx'], 'csdt_idx'=>$result['section_player']['idx']));
        //감상가능 여부확인
        $result['play_on'] = false;        
        if($this->mt_idx){
            $member = $this->db->fetch_query("select * from member_t where idx=".$this->mt_idx);
            if($member['mt_grade']=='1'){ //일반
                $row = $this->db->fetch_query("select * from contents_payment_t where mt_idx='".$this->mt_idx."' and ct_id='".$ct_idx."' and cpt_state=2");
                if($row['idx'] > 0) $result['play_on'] = true;
            }else{
                $result['play_on'] = true;
            }
        }
        return $result;
    }

    //log
    public function play_log($arr)
    {
        $row = $this->play_info($arr);
        $data_set = array();        
        if($row['idx'] < 1){
            $data_set['mt_idx'] = $this->mt_idx;
            $data_set['ct_idx'] = $arr['ct_idx'];
            $data_set['cst_idx'] = $arr['cst_idx'];
            $data_set['csdt_idx'] = $arr['csdt_idx'];            
            $data_set['cplt_stop'] = floor($arr['currentTime']);
            $data_set['cplt_time'] = floor($arr['currentTime']);
            $date_set['cplt_cnt'] = 1;
            $data_set['cplt_wdate'] = date('Y-m-d H:i:s');
            $this->db->insert_query("contents_play_log_t", $data_set);
        }else{
            $data_set['cplt_stop'] = floor($arr['currentTime']);
            $data_set['cplt_time'] = $row['cplt_time'] + floor($arr['currentTime']);
            $data_set['cplt_lastdate'] = date('Y-m-d H:i:s');
            if($arr['play_click'] == 'true') $data_set['cplt_cnt'] = $row['cplt_cnt'] + 1;
            $this->db->update_query("contents_play_log_t", $data_set, " idx = ".$row['idx']);
        }
        return $row['cplt_time'];
    }

    public function play_info($arr)
    {   
        if($this->mt_idx < 1) return false;
        return $this->db->fetch_query("select * from contents_play_log_t where mt_idx=".$this->mt_idx." and ct_idx=".$arr['ct_idx']." and cst_idx=".$arr['cst_idx']." and csdt_idx=".$arr['csdt_idx']);
    }

    public function section($idx, $type='')
    {
        if($type=='array'){
            return $this->db->select_query("select * from contents_section_t where ct_idx=".$idx);
        }else{
            return $this->db->fetch_query("select * from contents_section_t where idx=".$idx);
        }
    }

    //상세
    public function detail($idx)
    {
        if(!$idx || $idx=='') return false;
        $result = $this->contents_info($idx);
        if(count($result) < 1) return false;
        $result['section'] = $this->section($idx, 'array');
        
        //비슷한
        if($this->mt_idx > 0){
        $result['similar'] = ($result['cc_similar']) ? $this->db->select_query("select *, (SELECT wct_status FROM `wish_contents_t` WHERE mt_idx=".$this->mt_idx." AND ct_idx=contents_t.idx LIMIT 1) AS wct_status from contents_t where idx in(".$result['cc_similar'].")") : array();
        }else{
            $result['similar'] = ($result['cc_similar']) ? $this->db->select_query("select * from contents_t where idx in(".$result['cc_similar'].")") : array();
        }
        //리뷰
        $result['review'] = $this->db->select_query("select *,(SELECT mt_nickname FROM member_t WHERE idx=mt_idx) AS nickname from review_audio_t where ct_idx=".$idx." order by idx desc");		
        if($this->mt_idx > 0){
            //찜하기
		    $row = $this->db->fetch_query("select count(0) as cnt from wish_contents_t where ct_idx = ".$idx." and mt_idx=".$this->mt_idx." and wct_status='Y'");
		    $result['wish'] = $row['cnt'];
            //회원정보
            $result['member'] = $this->db->fetch_query("select * from member_t where idx=".$this->mt_idx);
            //감상가능 여부확인
            $result['play_on'] = false;
            if($result['member']['mt_grade']=='1'){ //일반
                $row = $this->db->fetch_query("select * from contents_payment_t where mt_idx='".$this->mt_idx."' and ct_id='".$idx."' and cpt_state=2");
                if($row['idx'] > 0) $result['play_on'] = true;
            }else{
                $result['play_on'] = true;
            }
            $result['playlog'] = $this->playlog($idx);
        }else{
            $result['wish'] = 0;
        }        
        return $result;
    }

    //플레이 log
    public function playlog($ct_idx)
    {
        return $this->db->fetch_query("select * from contents_play_log_t where ct_idx=".$ct_idx." and mt_idx=".$this->mt_idx);
    }

    //리뷰 확인
	public function review_select($mt_idx, $ct_idx)
	{
		$review = $this->db->fetch_query("select idx from review_audio_t where mt_idx=".$mt_idx." and ct_idx=".$ct_idx);
		return $review['idx'];
	}

    //리뷰 저장
    public function review_insert($set_arr)
    {
        if($this->mt_idx < 1) return false;
		//리뷰 확인
		if($this->review_select($this->mt_idx, $set_arr['ct_idx']) > 0){
			return 'review_overlap';
		}else{

			unset($set_arr['act']);
			$set_arr['mt_idx'] = $this->mt_idx;
			$set_arr['rat_wdate'] = date('Y-m-d H:i:s');
			$this->db->insert_query('review_audio_t', $set_arr);

			//리뷰 점수 update
			$review = $this->db->fetch_query("select SUM(rat_score) AS sum_score, COUNT(rat_score) AS cnt_score from review_audio_t where ct_idx=".$set_arr['ct_idx']);
			$score = round($review['sum_score'] / $review['cnt_score'] , 1);
			$this->db->update_query("contents_t", array("ct_score"=>$score), " idx = ".$set_arr['ct_idx']);

			return 'review_insert';
		}
    }

    //리뷰 리스트
    public function review_act($obj=array())
    {
        $page = ($obj['page']=='') ? 1:$obj['page'] ;
        $limit_cnt = 8;
        $from_cnt = ($page - 1) * $limit_cnt;

        $_where = " where ";
        $query = "select *,(SELECT mt_nickname FROM member_t WHERE idx=mt_idx) AS nickname from review_audio_t";

        $where_query = $_where . " ct_idx=".$obj['ct_idx']." ";
        $_where = " and ";
        $where_query .= $_where . " ((SELECT rt_status FROM report_t WHERE review_idx = review_audio_t.idx limit 1) IS NULL or (SELECT rt_status FROM report_t WHERE review_idx = review_audio_t.idx limit 1) not in (1,2)) ";
        if($this->mt_idx < 1){
            $order_by = " order by idx desc ";
        }else{
            $order_by = " order by mt_idx=".$this->mt_idx." desc , idx desc ";
        }
        $limit = " limit ".$from_cnt.", ".$limit_cnt;
        $sql_query = $query.$where_query.$order_by.$limit;        
        $list = $this->db->select_query($sql_query);        
        $count = $this->db->count_query($query . $where_query);

        $result['list'] = $list;
        $result['count'] = $count - (($page - 1) * $limit_cnt);;
        $result['page'] = ceil($count / $limit_cnt);;
        return $result;
    }

    //연관
    public function relation($idx, $act='relation')
    {
        if(!$idx) return false;
        if($act=='relation'){
            $idx_arr = $idx;
        }else{
            $contents = $this->contents_info($idx);
            $idx_arr = $contents['cc_relation'];
        }
        $result = $this->db->select_query("select *, (SELECT GROUP_CONCAT(pc_name SEPARATOR ',') FROM `product_category_t` WHERE idx IN(1,3)) as pc_name from product_t where idx in(".$idx_arr.")");
        return $result;
    }

    //콘텐츠 정보
    public function contents_info($idx)
    {
        return $this->db->fetch_query("select *, idx as ct_idx from contents_t where idx=".$idx);
    }

    //목차
    public function section_detail($idx, $type='')
    {
        if($type=='array'){
            $result = $this->db->select_query("select * from contents_section_detail_t where cst_idx=".$idx);
        }else{
            $result = $this->db->fetch_query("select * from contents_section_detail_t where idx=".$idx);
        }
        return $result;
    }

    //리스트
    public function contents($obj=array())
    {                 
        $page = $obj['page'] + 1;
        $limit_cnt = 12;
        $from_cnt = ($page - 1) * $limit_cnt;
        
        $_where = " where ";
        $query = "select *, a1.idx as ct_idx from contents_t a1";

        $where_query = $_where . "a1.ct_type = 2 ";
        $_where = " and ";

        if($obj['cate_major']=='free'){
            $where_query .= $_where . " ct_price_type = 1 ";
        }else if($obj['cate_major'] && !($obj['cate_major'] == 'all')) {            
            $cc_category = $this->category_search($obj['cate_major']);            
            $where_query .= $_where . "(instr(a1.cc_category, '" . $cc_category . "'))";
        }

        if($obj['cate_detail']){
            $cc_byage = $this->category_search($obj['cate_detail']);
            $where_query .= $_where . "(instr(a1.cc_byage, '" . $cc_byage . "'))";
        }

        $order_by = " order by a1.idx desc ";
        $limit = " limit ".$from_cnt.", ".$limit_cnt;
        $sql_query = $query.$where_query.$order_by.$limit;
        //echo $sql_query;
        $list = $this->db->select_query($sql_query);        
        $count = $this->db->count_query($query . $where_query);

        $result['list'] = $list;
        $result['count'] = $count;
        $result['page'] = $page;
        return $result;
    }

    public function category_search($idx)
    {
        if($idx < 1) return false;
        $query = $this->db->fetch_query("select cc_name from contents_category_t where idx=".$idx." and cc_view=2");
        return $query['cc_name'];
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

    //오늘의 추천 콘텐츠 구매내용의 카테고리, 연령, 출연자 연관 콘텐츠
    public function today()
    {
        $query = "select * from contents_t where ct_type=2 and ct_push=1 order by idx desc limit 10";
        $list = $this->db->select_query($query);
        return $list;
    }

    //최근 재생한 콘텐츠
    public function lately()
    {
        if($this->mt_idx < 1) return array();
        $query = "select * from contents_play_log_t as a1 left join contents_t as b1 on(a1.ct_idx=b1.idx) where ct_type=2 and mt_idx='".$this->mt_idx."' order by cplt_lastdate desc";
        $list = $this->db->select_query($query);        
        return $list;
    }

    //인기 콘텐츠 가장 높은 재생수, 다운로드 수, 평점수 최대 10개
    public function best()
    {
        $query = "select * from contents_t where ct_type=2 order by ct_play limit 4";
        $list_play = $this->db->select_query($query);
        $query = "select * from contents_t where ct_type=2 order by ct_download limit 3";
        $list_download = $this->db->select_query($query);
        $query = "select * from contents_t where ct_type=2 order by ct_score limit 3";
        $list_score = $this->db->select_query($query);
        $list = array_merge($list_play, $list_download, $list_score);
        return $list;
    }

    //신규 콘텐츠
    public function new()
    {
        $query = "select * from contents_t where ct_type=2 order by idx desc limit 10";
        $list = $this->db->select_query($query);        
        return $list;
    }

}
?>