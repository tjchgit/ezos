<?php
class demo_ctl_index extends controller{
    public function run(){
        $var = "this is var";
        $this->assign('var', $var);
        $this->display();
    }
    public function demo3(){
        $arr = array(1,2,3,4,5,6,7,8,9);
        $this->assign('arr', $arr);
        $this->display();
    }
}