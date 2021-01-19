<?php

//权限管理
namespace Admin\Controller;
use Admin\Model\userModel;
use \Frame\Libs\baseController;
use \Admin\Model\privilegeModel;

class privilegeController extends baseController{

	//获取登录表单界面
    public function login() {
        $this->smarty->display("Privilege/login.html");
    }
	//验证用户信息
	public function check(){
		//接收数据
		$username = trim($_POST['username']);
		$password = trim($_POST['password']);
		$verify  = trim($_POST['verify']);

		//验证验证码的合法性
		if(empty($verify)){
			$this->jump('验证码不能为空！','?c=privilege&a=login',0);
		}

		//合法性验证（验证码先放着）
		if(empty($username) || empty($password)){
			//不对：应该重来
			$this->jump('用户名和密码都不能为空！','?c=privilege&a=login');
		}

		//验证验证码的有效性
		if(strtolower($verify)!=strtolower($_SESSION['captcha'])){
            //验证码不匹配
            $this->jump('验证码错误！','?c=privilege&a=login',0);
        }

		//验证用户名是否存在：调用模型
		$user = privilegeModel::getInstance()->fetchOne("username='{$username}'");

//		//判定用户是否存在以及是否有权限
        if(!$user||$user['role']!=1){
            //用户名不存在
            $this->jump('用户：' . $username . ' 不存在！','?c=privilege&a=login');
        }

        //用户密码验证
        if($user['password'] !== md5(md5($password))){
            //密码不正确
            $this->jump('密码错误！','?c=privilege&a=login');
        }

        //登录成功：更新用户信息
        $data['ip']          = $_SERVER["REMOTE_ADDR"];
        $data['login_times'] = $user['login_times'] + 1;
        if (!privilegeModel::getInstance()->update($data,$user['id'])) {
            $this->jump("用户信息获取失败！","？c=user&a=login");
        }

        //将用户登录后的信息保存到session中
        $_SESSION['user'] = $user;

        //7天免登录
        if(isset($_POST['rememberMe'])){
            //用户选择了记住用户信息
            setcookie('id',$user['id'],time() + 7 * 24 * 3600);
        }

        //跳转到首页
        $this->jump('欢迎登录网站后台系统！','?c=index');
	}

	//退出系统
	public function logout(){
		//删除session
        unset($_SESSION['user']);
        //删除session文件
		session_destroy();

		//清除session对应的cookie
		setcookie(session_name(),false);
		//提示：退出成功
		$this->jump('退出成功！','?c=privilege&a=login',0);
	}

    //获取验证码
    public function captcha() {
        //获取验证码对象
        $captchaObj = new \Frame\Vendor\Captcha();
        return $captchaObj;//不用return也能用
    }
}