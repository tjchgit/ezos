<?php
class demo_ctl_auth extends controller{
    public function run(){
        $auth = new auth();
        P($auth);
        $d = $auth->check('admin-index-can,admin-index-run', 1, 1, 'url', 'or');
        $d2 = $auth->check('admin-index-can,admin-index-run', 1, 1, 'url', 'and');
        P($d);
        is_administrator();
        P($d2);
        $demo = array('1','2',3,4,5,6);
        debug('21321313132');
        debug($demo);
        debug(I('session.'));
        $this->display();
    }
}