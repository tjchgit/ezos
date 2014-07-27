<?php
class home_wid_demo extends widget{
    public function run($data){
        $res = M('wechat_user')->select();
        $this->display();
    }
}