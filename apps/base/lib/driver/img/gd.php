<?php
class base_driver_img_gd{
    private $img;
    private $info;
    public function __construct($imgname=null){
        $imgname && $this->open($imgname);
    }

    /**
     * 打开一张图像
     * @param  string $imgname 图像路径
     */
    public function open($imgname){
        if(!is_file($imgname)) E('不存在的图像文件');
        $info = getimagesize($imgname);
        if(false === $info || (IMAGETYPE_GIF === $info[2] && empty($info['bits']))){
            E('非法图像文件');
        }
        $this->info = array(
            'width'     => $info[0],
            'height'    => $info[1],
            'type'      => image_type_to_extension($info[2], false),
            'mime'      => $info['mime']
        );
        // 销毁图像资源
        empty($this->img) || imagedestroy($this->img);
        // 打开图像
        if('gif' == $this->info['type']){
            $className  = 'base_driver_img_gif';
            $this->gif  = imagecreatefromstring($this->gif->image());
        }else{
            $fun        = "imagecreatefrom{$this->info['type']}";
            $this->img  = $fun($imgname);
        }
    }

    /**
     * 保存图像
     * @param  string  $imgname   图像保存名称
     * @param  string  $type      图像类型
     * @param  integer $quality   图像质量     
     * @param  boolean $interlace 是否对JPEG类型图像设置隔行扫描
     */
    public function save($imgname, $type=null, $quality=80, $interlace=true){
        if(empty($this->img)) E('没有可以保存的图像资源');
        // 获取图像类型
        if(is_null($type)){
            $type = $this->info['type'];
        }else{
            $type = strtolower($type);
        }
        // 保存图像
        if('jpeg' == $type || 'jpg' == $type){
            imageinterlace($this->img, $interlace);
            imagejpeg($this->img, $imgname, $quality);
        }elseif('git' == $type  && !empty($this->gif)){
            $this->gif->save($imgname);
        }else{
            $fun = 'image'.$type;
            $fun($this->img, $imgname);
        }
    }

    /**
     * 返回图像宽度
     * @return integer 图像宽度
     */
    public function width(){
        if(empty($this->img)) E('没有指定图像资源');
        return $this->info['width'];
    }

    /**
     * 返回图像高度
     * @return integer 图像高度
     */
    public function height(){
        if(empty($this->img)) E('没有指定图像资源');
        return $this->info['height'];
    }

    /**
     * 返回图像类型
     * @return string 图像类型
     */
    public function type(){
        if(empty($this->img)) E('没有指定图像资源');
        return $this->info['type'];
    }

    /**
     * 返回图像MIME类型
     * @return string 图像MIME类型
     */
    public function mime(){
        if(empty($this->img)) E('没有指定图像资源');
        return $this->info['mime'];
    }

    /**
     * 返回图像尺寸数组 0 - 图像宽度，1 - 图像高度
     * @return array 图像尺寸
     */
    public function size(){
        if(empty($this->img)) E('没有指定图像资源');
        return array($this->info['width'], $this->info['height']);
    }

    /**
     * 裁剪图像
     * @param  integer $w      裁剪区域宽度
     * @param  integer $h      裁剪区域高度
     * @param  integer $x      裁剪区域x坐标
     * @param  integer $y      裁剪区域y坐标
     * @param  integer $width  图像保存宽度
     * @param  integer $height 图像保存高度
     */
    public function crop($w, $h, $x = 0, $y = 0, $width = null, $height = null){
        if(empty($this->img)) E('没有可以被裁剪的图像资源');
        //设置保存尺寸
        empty($width)  && $width  = $w;
        empty($height) && $height = $h;
        do{
            $img = imagecreatetruecolor($width, $height);
            $color = imagecolorallocate($img, 255, 255, 255);
            imagefill($this->img, 0, 0, $color);
            imagecopyresampled($img, $this->img, 0, 0, $x, $y, $width, $height, $w, $h);
            imagedestroy($this->img);
            $this->img = $img;
        }while(!empty($this->gif) && $this->gifNext());
        $this->info['width']    = $width;
        $this->info['height']   = $height;
    }

    /**
     * 生成缩略图
     * @param  integer $width  缩略图最大宽度
     * @param  integer $height 缩略图最大高度
     * @param  integer $type   缩略图裁剪类型
     */
    public function thumb($width, $height, $type = image::IMAGE_THUMB_SCALE){
        if(empty($this->img)) E('没有可以被缩略的图像资源');
        //原图宽度和高度
        $w = $this->info['width'];
        $h = $this->info['height'];
        switch($type){
            case image::IMAGE_THUMB_SCALE:
                if($w<$width && $h<$height) return ;
                $scale  = min($width/$w, $height/$h);
                $x = $y = 0 ;
                $width  = $w * $scale;
                $height = $h * $scale;
            break;
            case image::IMAGE_THUMB_CENTER:
                $scale = max($width/$w, $height/$h);
                $w = $width/$scale;
                $h = $height/$scale;
                $x = ($this->info['width'] - $w)/2;
                $y = ($this->info['height'] - $h)/2;
            break;
            case image::IMAGE_THUMB_NORTHWEST:
                $scale = max($width/$w, $height/$h);
                $x = $y = 0;
                $w = $width/$scale;
                $h = $height/$scale;
            break;
            case image::IMAGE_THUMB_SOUTHEAST:
                $scale = max($width/$w, $height/$h);
                $w = $width/$scale;
                $h = $height/$scale;
                $x = $this->info['width'] - $w;
                $y = $this->info['height'] - $h;
            break;
            case image::IMAGE_THUMB_FILLED:
                if($w<$width && $h<$height){
                    $scale = 1 ;
                }else{
                    $scale = min($width/$w, $height/$h);
                }
                $neww = $w * $scale;
                $newh = $h * $scale;
                $posx = ($width  - $w * $scale)/2;
                $posy = ($height - $h * $scale)/2;
                do{
                    //创建新图像
                    $img = imagecreatetruecolor($width, $height);
                    // 调整默认颜色
                    $color = imagecolorallocate($img, 255, 255, 255);
                    imagefill($img, 0, 0, $color);
                    //裁剪
                    imagecopyresampled($img, $this->img, $posx, $posy, $x, $y, $neww, $newh, $w, $h);
                    imagedestroy($this->img); //销毁原图
                    $this->img = $img;
                } while(!empty($this->gif) && $this->gifNext());
                $this->info['width']  = $width;
                $this->info['height'] = $height;
                return;
            case image::IMAGE_THUMB_FIXED:
                $x = $y = 0 ;
            break;
            default:
                E('不支持的缩略图裁剪类型');
        }
        $this->crop($w, $h, $x, $y, $width, $height);
    }

    /**
     * 添加水印
     * @param  string  $source 水印图片路径
     * @param  integer $locate 水印位置
     * @param  integer $alpha  水印透明度
     */
    public function water($source, $locate = Image::IMAGE_WATER_SOUTHEAST,$alpha=80){
        if(empty($this->img)) E('没有可以被添加水印的图像资源');
        if(!is_file($source)) E('水印图像不存在');
        $info = getimagesize($source);
        if(false === $info || (IMAGETYPE_GIF === $info[2] && empty($info['bits']))){
            E('非法水印文件');
        }
        //创建水印图像资源
        $fun   = 'imagecreatefrom' . image_type_to_extension($info[2], false);
        $water = $fun($source);
        //设定水印图像的混色模式
        imagealphablending($water, true);
        switch ($locate) {
            case Image::IMAGE_WATER_SOUTHEAST:  /* 右下水印 */
                $x = $this->info['width'] - $info[0];
                $y = $this->info['height'] - $info[1];
            break;
            case Image::IMAGE_WATER_SOUTHWEST:  /* 左下水印 */
                $x = 0;
                $y = $this->info['height'] - $info[1];
            break;
            case Image::IMAGE_WATER_NORTHWEST:  /* 左上水印 */
                $x = $y = 0;
            break;
            case Image::IMAGE_WATER_NORTHEAST:  /* 右上水印 */
                $x = $this->info['width'] - $info[0];
                $y = 0;
            break;
            case Image::IMAGE_WATER_CENTER:     /* 居中水印 */
                $x = ($this->info['width'] - $info[0])/2;
                $y = ($this->info['height'] - $info[1])/2;
            break;
            case Image::IMAGE_WATER_SOUTH:      /* 下居中水印 */
                $x = ($this->info['width'] - $info[0])/2;
                $y = $this->info['height'] - $info[1];
            break;
            case Image::IMAGE_WATER_EAST:       /* 右居中水印 */
                $x = $this->info['width'] - $info[0];
                $y = ($this->info['height'] - $info[1])/2;
            break;
            case Image::IMAGE_WATER_NORTH:      /* 上居中水印 */
                $x = ($this->info['width'] - $info[0])/2;
                $y = 0;
            break;
            case Image::IMAGE_WATER_WEST:       /* 左居中水印 */
                $x = 0;
                $y = ($this->info['height'] - $info[1])/2;
            break;
            default:
                if(is_array($locate)){
                    list($x, $y) = $locate;
                } else {
                    E('不支持的水印位置类型');
                }
        }
        do{
            $src = imagecreatetruecolor($info[0], $info[1]);
            $color = imagecolorallocate($src, 255, 255, 255);
            imagefill($src, 0, 0, $color);
            imagecopy($src, $this->img, 0, 0, $x, $y, $info[0], $info[1]);
            imagecopy($src, $water, 0, 0, 0, 0, $info[0], $info[1]);
            imagecopymerge($this->img, $src, $x, $y, 0, 0, $info[0], $info[1], $alpha);
            imagedestroy($src);
        }while(!empty($this->gif) && $this->gifNext());
        imagedestroy($water);
    }
    /**
     * 图像添加文字
     * @param  string  $text   添加的文字
     * @param  string  $font   字体路径
     * @param  integer $size   字号
     * @param  string  $color  文字颜色
     * @param  integer $locate 文字写入位置
     * @param  integer $offset 文字相对当前位置的偏移量
     * @param  integer $angle  文字倾斜角度
     */
    public function text($text, $font, $size, $color = '#00000000', $locate = Image::IMAGE_WATER_SOUTHEAST, $offset = 0, $angle = 0){
        if(empty($this->img)) E('没有可以被写入文字的图像资源');
        if(!is_file($font)) E("不存在的字体文件：{$font}");
        //获取文字信息
        $info = imagettfbbox($size, $angle, $font, $text);
        $minx = min($info[0], $info[2], $info[4], $info[6]); 
        $maxx = max($info[0], $info[2], $info[4], $info[6]); 
        $miny = min($info[1], $info[3], $info[5], $info[7]); 
        $maxy = max($info[1], $info[3], $info[5], $info[7]);
        /* 计算文字初始坐标和尺寸 */
        $x = $minx;
        $y = abs($miny);
        $w = $maxx - $minx;
        $h = $maxy - $miny;
        /* 设定文字位置 */
        switch ($locate) {
            case image::IMAGE_WATER_SOUTHEAST:  /* 右下角文字 */
                $x += $this->info['width']  - $w;
                $y += $this->info['height'] - $h;
            break;
            case image::IMAGE_WATER_SOUTHWEST:  /* 左下角文字 */
                $y += $this->info['height'] - $h;
            break;
            case image::IMAGE_WATER_NORTHWEST:  /* 左上角文字 */
                // 起始坐标即为左上角坐标，无需调整
            break;
            case image::IMAGE_WATER_NORTHEAST:  /* 右上角文字 */
                $x += $this->info['width'] - $w;
            break;
            case image::IMAGE_WATER_CENTER:     /* 居中文字 */
                $x += ($this->info['width']  - $w)/2;
                $y += ($this->info['height'] - $h)/2;
            break;
            case image::IMAGE_WATER_SOUTH:      /* 下居中文字 */
                $x += ($this->info['width'] - $w)/2;
                $y += $this->info['height'] - $h;
            break;
            case image::IMAGE_WATER_EAST:       /* 右居中文字 */
                $x += $this->info['width'] - $w;
                $y += ($this->info['height'] - $h)/2;
            break;
            case image::IMAGE_WATER_NORTH:      /* 上居中文字 */
                $x += ($this->info['width'] - $w)/2;
            break;
            case image::IMAGE_WATER_WEST:       /* 左居中文字 */
                $y += ($this->info['height'] - $h)/2;
            break;
            default:                            /* 自定义文字坐标 */
                if(is_array($locate)){
                    list($posx, $posy) = $locate;
                    $x += $posx;
                    $y += $posy;
                } else {
                    E('不支持的文字位置类型');
                }
        }
        /* 设置偏移量 */
        if(is_array($offset)){
            $offset = array_map('intval', $offset);
            list($ox, $oy) = $offset;
        } else{
            $offset = intval($offset);
            $ox = $oy = $offset;
        }
        /* 设置颜色 */
        if(is_string($color) && 0 === strpos($color, '#')){
            $color = str_split(substr($color, 1), 2);
            $color = array_map('hexdec', $color);
            if(empty($color[3]) || $color[3] > 127){
                $color[3] = 0;
            }
        } elseif (!is_array($color)) {
            E('错误的颜色值');
        }
        do{     /* 写入文字 */
            $col = imagecolorallocatealpha($this->img, $color[0], $color[1], $color[2], $color[3]);
            imagettftext($this->img, $size, $angle, $x + $ox, $y + $oy, $col, $font, $text);
        } while(!empty($this->gif) && $this->gifNext());
    }
    /* 切换到GIF的下一帧并保存当前帧，内部使用 */
    private function gifNext(){
        ob_start();
        ob_implicit_flush(0);
        imagegif($this->img);
        $img = ob_get_clean();
        $this->gif->image($img);
        $next = $this->gif->nextImage();
        if($next){
            $this->img = imagecreatefromstring($next);
            return $next;
        } else {
            $this->img = imagecreatefromstring($this->gif->image());
            return false;
        }
    }
    /**
     * 析构方法，用于销毁图像资源
     */
    public function __destruct() {
        empty($this->img) || imagedestroy($this->img);
    }
}