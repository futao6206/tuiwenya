<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/15
 * Time: 6:13 PM
 * 前端配置数组
 */
//前端配置数组
return array(
    //数据库配置
    'DB_TYPE' => '这里配置你数据库的类型',//比如 mysql
    'DB_HOST' => '这里配置你数据库地址',
    'DB_USER' =>  '这里配置你数据库用户名',
    'DB_PASS' =>  '这里配置你数据库的密码',
    'DB_NAME' =>  '这里配置你数据库的名字',

    //前端默认URL路由参数
    'default_platform'     => 'Home',//默认应用
    'default_controller'  => 'index',//默认控制器
    'default_action'      => 'index',//默认动作
);