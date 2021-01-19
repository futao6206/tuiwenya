<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/19
 * Time: 5:27 PM
 */

namespace Frame\Vendor;
//包含原始的Smarty类 smarty-3.1.33
require_once(FRAME_PATH . "Vendor" . DS . "smarty-3.1.33" . DS . "libs" . DS . "Smarty.class.php");

//继承原始Smarty类 在访问系统内部或不包含在命名空间中的类名称时，必须使用完全限定名称
final class Smarty extends \Smarty {
    //这里啥也不用写 只需继承 就为了使用命名空间
}