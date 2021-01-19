<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/29
 * Time: 5:31 PM
 */

namespace Frame\Vendor;

final class Captcha {//图形验证码

    private $code;    //验证码字符串
    private $codelen; //验证码长度
    private $width;   //图形宽度
    private $height;
    private $img;     //图形资源
    private $fontsize;//字号大小
    private $fontfile;//字体文件

    //构造方法：对象初始化的方法
    public function __construct($codelen=4,$width=80,$height=40,$fontsize=20)
    {
        $this->codelen  = $codelen;
        $this->width    = $width;
        $this->height   = $height;
        $this->fontsize = $fontsize;
        $this->fontfile = "./Public/Admin/font/verdana.ttf";
        $this->code     = $this->createCode();
        $this->img      = $this->createImg();
        $this->createBg();//给画布添加背景
        $this->createText();//写入字符串
        $this->outPut();//输出图像
    }

    //生成验证码随机字符串
    private function createCode() {
        //产生随机字符串数组
        $arr_str = array_merge(range('a','z'),range('0','9'),range('A','Z'));
        //打乱数组
        shuffle($arr_str);
        //从数组中随机指定4个下标
        $arr_index = array_rand($arr_str,$this->codelen);
        //循环下标数组，构建随机数组
        $str = "";
        foreach ($arr_index as $i) {
            $str .= $arr_str[$i];
        }
        //验证码存入SESSION
        $_SESSION['captcha'] = $str;

        return $str;
    }

    //创建一个空画布
    private function createImg() {
        return imagecreatetruecolor($this->width,$this->height);
    }

    private function createBg() {
        //画布分配背景颜色
        $color = imagecolorallocate($this->img,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
        //绘制带背景色的矩形
        imagefilledrectangle($this->img,0,0,$this->width,$this->height,$color);
    }

    //写入验证码字符串
    private function createText() {
        //给文本分配颜色
        $color = imagecolorallocate($this->img,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
        //写入到字符串到图像上
        imagettftext($this->img,$this->fontsize,0,5,30,$color,$this->fontfile,$this->code);
        //增加干扰点：*
        for($i = 0;$i < 50;$i++){
            //随机颜色
            $dots_color = imagecolorallocate($this->img,mt_rand(140,190),mt_rand(140,190),mt_rand(140,190));
            //写入内容
            imagestring($this->img,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$dots_color);
        }

        //增加干扰线
//        for($i = 0;$i < 10;$i++){
//            //线段颜色
//            $line_color = imagecolorallocate($this->img, mt_rand(80,130), mt_rand(80,130), mt_rand(80,130));
//            //制作线段
//            imageline($this->img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$line_color);
//        }
    }

    private function outPut() {
        //声明输出内容的MIME类型
        header("content-type:image/png");
        //输出图像
        imagepng($this->img);
        //销毁图像资源
        imagedestroy($this->img);
    }
}