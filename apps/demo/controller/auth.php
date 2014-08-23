<?php
class demo_ctl_auth extends controller{
    public function run(){
        $auth = new auth();
        $d = $auth->check('admin-index-can', 1);
        P($d);
    }
}