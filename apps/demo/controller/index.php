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
    public function doUpload(){
        $up = new upload();
        $str = (string)time();
        $up->saveName = $str;
        $res = $up->upload();
        if($res){
            echo '<pre>';
            print_r($res);
            echo '</pre>';
            $img = new image();
            $img->open($res['files']['allpath']);
            $img->thumb(150, 150, 1)->save('thumb123.jpg');
        }else{
            echo $up->getError();
        }
    }
    public function upload(){
        $this->display();
    }
}