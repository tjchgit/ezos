<?php
class demo_ctl_model extends controller{
    public function run(){
        $model = D('member');
        $users = $model->memberInfo();
        P($users);
    }
}