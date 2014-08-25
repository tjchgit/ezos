<?php
class demo_mdl_member extends model{
    public function memberInfo(){
        $Member = M('web_user');
        $members = $Member->select();
        return $members;
    }
    public function addOneUser(){
        $Member = M('web_user');
        $_validate = array(
            array('username', '6,20', '用户名格式出错，长度应为6-20位。', model::MUST_VALIDATE, 'length'),
            array('password', '6,20', '密码格式错误，长度应为6-20位。', model::MUST_VALIDATE, 'length'),
        );
        $Member->setProperty('_validate', $_validate);
        if($Member->create()){
            P('YES');
        }else{
            P('NO');
        }
    }
}