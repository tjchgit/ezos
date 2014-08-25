<?php
class demo_mdl_member extends model{
    public function memberInfo(){
        $Member = M('web_user');
        $members = $Member->select();
        return $members;
    }
}