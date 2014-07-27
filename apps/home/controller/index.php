<?php
class home_ctl_index extends controller {
    public function run() {
        $this->display();
    }
    public function verify() {
        import("util.image");
        $img = new image();
        $img->buildImageVerify();
    }
    public function upload(){
        import("net.upload");
        $upload = new upload();
        $upload->maxSize = 32992200;
        $upload->allowExts = explode(',', "jpg,gif,png");
        $upload->savePath = "./public/upload/".date("Ymd").'/';
        $upload->saceRule = "uniqid";

        $upload->thumb = true;
        $upload->thumbPrefix = "600_,400_,200_,100_,90_,45_,30_";
        $upload->thumbMaxWidth = "600,400,200,100,90,45,30";
        $upload->thumbMaxHeight = "600,400,200,100,90,45,30";

        if(!$upload->upload()){
            $this->error($upload->getErrorMsg());
        }else{
            $uploadList = $upload->getUploadFileInfo();
            P($uploadList);
            import("util.image");
            $img = new image();
            $img::water($uploadList[0]['savepath'].'600_'.$uploadList[0]['savename'], "./public/water.png");   
        }
    }
    public function tree(){
        $this->display();
    }
}