<?php
class base_ctl_install extends controller{
    public function run(){
        if(is_dir(APP_DIR.'home')){
            $this->display();
        }else{
            $arr = array(
                'conf',
                'controller',
                'hooks',
                'model',
                'static',
                'static/css',
                'static/css/images',
                'static/js',
                'view',
                'widget',
            );
            foreach($arr as $dir){
                $dir = APP_DIR.'home/'.$dir;
                if(!is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }
            }
            $this->writeIndexControler();
            $this->writeIndexView();
            $this->writeIndexConf();
            redirect(__HOST__);
        }
        die();
    }
    public function writeIndexControler(){
        $controllerStr = file_get_contents(G_DIR.'view/indexController.php');
        $fileName = APP_DIR.'home/controller/index.php';
        if(!is_file($fileName))
            file_put_contents($fileName, $controllerStr);
        return true;
    }
    public function writeIndexView(){
        $viewStr = file_get_contents(G_DIR.'view/indexView.php');
        $fileName = APP_DIR.'home/view/index_run.htm';
        if(!is_file($fileName))
            file_put_contents($fileName, $viewStr);
        return true;
    }
    public function writeIndexConf(){
        $confStr = file_get_contents(G_DIR.'view/indexConf.php');
        $fileName = APP_DIR.'home/conf/config.php';
        if(!is_file($fileName))
            file_put_contents($fileName, $viewStr);
        return true;
    }
}