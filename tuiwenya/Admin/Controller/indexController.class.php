<?php

//后台首页功能
namespace Admin\Controller;
use \Frame\Libs\baseController;
use \Admin\Model\userModel;
use \Admin\Model\novelModel;

final class indexController extends baseController {

	//显示首页数据
	public function index(){
		//获取用户数量
		$users = userModel::getInstance()->rowCount();
		$novels = novelModel::getInstance()->rowCount();
		//显示首页
		$this->smarty->assign('users',$users);
		$this->smarty->assign('novels',$novels);
		$this->smarty->display('Index/index.html');
	}
}