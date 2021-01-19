<?php
namespace Home\Controller;
use \Frame\Libs\baseController;

//定义最终的首页控制器类 继承基础控制器类
final class userController extends baseController {
    //首页显示方法
    public function index() {
        $this->smarty->display("except/404.html");
    }
}