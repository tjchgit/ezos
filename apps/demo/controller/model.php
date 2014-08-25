<?php
class demo_ctl_model extends controller{
    public function run(){
        $model = D('member');
        $users = $model->memberInfo();
        P($users);
    }
    public function demo(){
        $_POST['username'] = '123456789';
        $_POST['password'] = '1234555557';
        $model = D('member');
        $model->addOneUser();
    }
}