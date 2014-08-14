<?php
class demo_ctl_index extends controller{
    public function run(){
        $var = "this is var";
        $this->assign('var', $var);
        $this->display();
    }
}