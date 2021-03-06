<?php
class auth {
    protected $_config = array();

    public function __construct(){
        $this->_config = C('AUTH_CONFIG');
        if($this->_config['AUTO_PREFIX']){
            $prefix = C('DB_PREFIX');
            $arr = array('AUTH_GROUP', 'AUTH_RULE', 'AUTH_USER', 'AUTH_GROUP_ACCESS');
            foreach($arr as $val){
                $this->_config[$val] = $prefix.$this->_config[$val];
            }
        }
    }

    /**
      * 检查权限
      * @param name string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
      * @param uid  int           认证用户的id
      * @param string mode        执行check的模式
      * @param relation string    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
      * @return boolean           通过验证返回true;失败返回false
     */
    public function check($name, $uid, $type=1, $mode='url', $relation='or'){
        if(!$this->_config['AUTH_ON']){
            return true;
        }
        $authList = $this->getAuthList($uid,$type);
        if(is_string($name)){
            $name = strtolower($name);
            if(strpos($name, ',')){
                $name = explode(',', $name);
            }else{
                $name = array($name);
            }
        }
        $list =  array();
        if($mode='url'){
            $REQUEST = unserialize(strtolower(serialize($_REQUEST)));
        }
        foreach($authList as $auth){
            $query = preg_replace('/^.+\?/U','',$auth);
            if($mode == 'url' && $query!=$auth){
                parse_str($query,$param);
                $intersect = array_intersect_assoc($REQUEST,$param);
                $auth = preg_replace('/\?.*$/U','',$auth);
                if ( in_array($auth,$name) && $intersect==$param ) {  //如果节点相符且url参数满足
                    $list[] = $auth ;
                }
            }else if(in_array($auth , $name)){
                $list[] = $auth ;
            }
        }
        if ($relation == 'or' and !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ($relation == 'and' and empty($diff)) {
            return true;
        }
        return false;
    }

    /**
     * 根据用户id获取用户组,返回值为数组
     * @param  uid int     用户id
     * @return array       用户所属的用户组 array(
     * array('uid'=>'用户id','group_id'=>'用户组id','title'=>'用户组名称','rules'=>'用户组拥有的规则id,多个,号隔开'),
     * ...)
     */
    public function getGroups($uid) {
        static $groups = array();
        if (isset($groups[$uid])){
            return $groups[$uid];
        }
        $user_groups = M()
            ->table($this->_config['AUTH_GROUP_ACCESS'] . ' a')
            ->where("a.uid='$uid' and g.status='1'")
            ->join($this->_config['AUTH_GROUP']." g on a.group_id=g.id")
            ->field('rules')->select();
        $groups[$uid] = $user_groups ? : array();
        return $groups[$uid];
    }

    /**
     * 获得权限列表
     * @param integer $uid  用户id
     * @param integer $type 
     */
    public function getAuthList($uid,$type){
        static $_authList = array();
        $t = implode(',',(array)$type);
        if (isset($_authList[$uid.$t])) {
            return $_authList[$uid.$t];
        }
        if( $this->_config['AUTH_TYPE']==2 && isset($_SESSION['_AUTH_LIST_'.$uid.$t])){
            return $_SESSION['_AUTH_LIST_'.$uid.$t];
        }
        $groups = $this->getGroups($uid);
        $ids = array();
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);
        if (empty($ids)) {
            $_authList[$uid.$t] = array();
            return array();
        }
        $map=array(
            'id'=>array('in',$ids),
            'type'=>$type,
            'status'=>1,
        );
        $rules = M()->table($this->_config['AUTH_RULE'])->where($map)->field('condition,name')->select();
        $authList = array();
        foreach ($rules as $rule) {
            if (!empty($rule['condition'])) {
                $user = $this->getUserInfo($uid);
                $command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $rule['condition']);
                @(eval('$condition=(' . $command . ');'));
                if ($condition) {
                    $authList[] = strtolower($rule['name']);
                }
            }else{
                $authList[] = strtolower($rule['name']);
            }
        }
        $_authList[$uid.$t] = $authList;
        if($this->_config['AUTH_TYPE']==2){
            $_SESSION['_AUTH_LIST_'.$uid.$t]=$authList;
        }
        return array_unique($authList);
    }

    /**
     * 获得用户资料,根据自己的情况读取数据库
     */
    protected function getUserInfo($uid) {
        static $userinfo=array();
        if(!isset($userinfo[$uid])){
            $userinfo[$uid]=M()->where(array('uid'=>$uid))->table($this->_config['AUTH_USER'])->find();
        }
        return $userinfo[$uid];
    }
}