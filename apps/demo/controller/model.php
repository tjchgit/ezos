<?php
class demo_ctl_model extends controller{
    public function run(){
        $model = D('member');
        $users = $model->getAllUser();
        P($users);
    }

    public function demo(){
        $model = D('member');
        $_POST['username'] = '123';
        $_POST['password'] = '444';
        $_POST['rpassword'] = '444';
        $_POST['dpassword'] = '4445';
        $model->insertOneUser();
    }
}