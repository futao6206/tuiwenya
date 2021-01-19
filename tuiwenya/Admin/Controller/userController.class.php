<?php

//后台用户管理
namespace Admin\Controller;
use \Frame\Libs\baseController;
use \Admin\Model\userModel;
use Frame\Vendor\Pager\Pager;


class userController extends baseController {

	//新增用户：显示表单
	public function add(){
		//显示表单
		$this->smarty->display('User/add.html');
	}

	//新增用户：数据入库
	public function insert(){
		//合法性验证
		if(empty(trim($_POST['username'])) || empty(trim($_POST['password']))){
			$this->jump('用户名和密码都不能为空！','?c=user&a=add');
		}

        //防止SQL注入：通过特殊符号改变SQL指令
        $data['username'] = addslashes($_POST['username']);
        $data['password'] = $_POST['password'];
        //说明用户名可以用：组织数据入库
        $data['name']     = !empty($_POST['name']) ? $_POST['name'] : $_POST['username'];
        $data['password'] = md5(md5($data['password']));
        $data['role']   = !empty($_POST['role']) ? 1 : 0;

		//合理性验证
		$u = userModel::getInstance();
		if($u->rowCount("username='{$data['username']}'")){
			$this->jump('当前用户名：' . $data['username'] . ' 已经被注册！','?c=user&a=add');
		}

		if($u->insert($data)){
			$this->jump('用户新增成功！','?c=user',0);
		}else{
			$this->jump('用户新增失败！','?c=user&a=add');
		}
	}

	//显示所有用户信息
	public function index(){
        //构建分页参数
        $pagesize = 10;
        $page     = $_GET['page'] ?? 1;
        $startrow = ($page-1)*$pagesize;
        $records  = userModel::getInstance()->rowCount();
        $params   = array(
            'c'   => CONTROLLER,
            'a'   => ACTION,
        );

		//获取分页信息
        $pageObj = new Pager($records,$pagesize,$page,$params);
        $pageStr = $pageObj->showPage();

		//获取所有用户
		$u = userModel::getInstance();
        $users = $u->fetchAll($startrow,$pagesize);
//		//分配给模板
		$this->smarty->assign('pageStr',$pageStr);
		$this->smarty->assign('users',$users);
		$this->smarty->display('User/index.html');
	}

	//显示编辑页
	public function showEdit() {
        ;
        //获取当前用户
        $id = $_GET['id'];
        $u = userModel::getInstance();
        $user = $u->fetchOne($id);
        $this->smarty->assign('user',$user);
        $this->smarty->assign('id',$id);
	    $this->smarty->display('User/edit.html');
    }

    //编辑用户
    public function edit() {

        $id = $_GET['id'];
        $name = $_POST['name'];
        $role = $_POST['role'];
        $password = $_POST['password'];
        //判断昵称是否为空
        if (empty($name)) {
            echo $name;
            $name = $_SESSION['user']['username'];
        }

        //判断是否重设密码
        if(!empty($password)){
            $data = array(
                'name'     => $name,
                'role'     => $role,
                'password' => $password
            );
        } else {
            $data = array(
                'name'     => $name,
                'role'     => $role
            );
        }
        if (userModel::getInstance()->updateOne($data,$id)) {
            $this->jump("id={$id}用户编辑成功！","?c=user",0);
        } else {
            $this->jump("id={$id}用户编辑失败，3秒后自动返回！","?c=user");
        }
    }

	//删除用户
	public function delete(){
		//接收数据
		$id = (int)$_GET['id'];
		//删除
		$u = userModel::getInstance();
		if($u->delete($id)){
			$this->jump("id={$id}用户删除成功！","?c=user",0);
		}else{
			$this->jump("id={$id}用户删除失败，3秒后自动返回！","?c=user");
		}
	}
}







