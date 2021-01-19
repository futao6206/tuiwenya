<?php

//小说控制器
namespace Admin\Controller;
use \Frame\Libs\baseController;
use \Admin\Model\novelModel;
use Frame\Vendor\Pager\NewPager;
use \Frame\Vendor\Uploader;


class novelController extends baseController {

    //小说列表
    public function index() {
        //构建分页参数
        $pagesize = 20;
        $page     = $_GET['page'] ?? 1;
        $startrow = ($page-1)*$pagesize;
        $records  = novelModel::getInstance()->rowCount();
        $params   = array(
            'c'   => CONTROLLER,
            'a'   => ACTION,
        );
        $where='2>1';
        if (!empty($_POST['keyword']))
        {
            $params['keyword'] = $_POST['keyword'];
            $where = "title like '%{$_POST['keyword']}%' || author like '%{$_POST['keyword']}%'";
        }
        if (!empty($_POST['isComplete'])) {
            $params['isComplete'] = $_POST['isComplete'];
            $isComplete = $_POST['isComplete']-1;
            if ($isComplete!=2) {
                $where = "isComplete={$isComplete}";
            }
        }
        //获取连表查询数据
        $novels = novelModel::getInstance()->fetchAll($startrow,$pagesize,$where);
        //分页对象
        $pageStr = NewPager::clickPage($records,$pagesize,$page,$params);
        //显示
        $this->smarty->assign(array(
            'records'  => $records,
            'novels'   => $novels,
            'params'   => $params,
            'pageStr'  => $pageStr,
        ));
        $this->smarty->display("Novel/index.html");
    }

	//新增小说：显示表单
	public function add(){
        $rules = novelModel::getInstance()->getRules();
		//显示表单
        $this->smarty->assign("rules",$rules);
		$this->smarty->display('Novel/add.html');
	}

	public function search() {
        $keywords = novelModel::getInstance()->fetchAllKeyword();
        $this->smarty->assign("keywords",$keywords);
        $this->smarty->display('Novel/search.html');
    }

    //删除search表无效的小说名字
    public function delete_search() {
        //接收数据
        $id = (int)$_GET['id'];
        //删除数据
        if(novelModel::getInstance()->delete_search($id)){
            $this->jump('删除成功！','?c=novel&a=search',0);
        }else{
            $this->jump('删除失败！','?c=novel&a=search');
        }
    }

	//新增小说：数据入库
	public function insert(){
        $rule = $_POST['rule_ch'];
        $rule1 = $_POST['rule_reg'];
        $chapterReg = "/(第{$rule1}{1,10}{$rule})/";
        if (strpos($rule1,'*') !== false) {
            $chapterReg = "/(第{$rule1}{$rule})/";
        }
        $isComplete = $_POST['isCompelete'];
		//接收数据
        $files = $_FILES['txt'];
        //判断是否有重复的数据
        $names = $files['name'];
        $count = count($names);
        $errorStr = '';
        $file = array();
        $newFiles = array();
        //去除重复的数据
        for ($i=0;$i<$count;$i++) {
            $flag = true;
            foreach ($files as $k=>$v) {
                if ($k=="name") {
                    $name = str_replace(".txt","",$files[$k][$i]);
                    $name = preg_replace('/[（](.*?)[）]|[【](.*?)[】]|[「](.*?)[」]|[『](.*?)[』]|[(](.*?)[)]|[[][\W\w]+[]]|({.*?})/u', "",$name);
                    if (novelModel::getInstance()->rowCount("title='{$name}'")!=0) {
                        $errorStr .= $name."<br>";
                        $flag = false;
                        break;
                    }
                }
                $file[$k] = $files[$k][$i];
            }
            if ($flag) {
                $newFiles[]=$file;
            }
        }
        //上传数据 保存至数据库
        $filePaths = Uploader::uploadAll($newFiles);
        foreach ($filePaths as $filePath) {
            novelModel::getInstance()->saveNovel($filePath,true,$isComplete,true,$chapterReg);
        }
        //删除小说目录下所有文件
        $this->deldir(UPLOAD_PATH);
        //提示
        if ($errorStr != ''||count($filePaths)==0) {
            $this->jump("总共：".count($filePaths)."本小说保存成功<br>未保存的重复数据：<br>".$errorStr,"?c=novel",15);
        } else {
            $this->jump("总共：".count($filePaths)."本小说保存成功","?c=novel");
        }
	}

	//删除小说
	public function delete() {
		//接收数据
		$id = (int)$_GET['id'];
		//删除数据
//		if(novelModel::getInstance()->delete($id)){
//			$this->jump('删除成功！','?c=novel',0);
//		}else{
//			$this->jump('删除失败！','?c=novel');
//		}
        echo "这里需要改！！";
	}

    //软删除 标记删除
    public function hide() {
        //接收数据
        $id = (int)$_GET['id'];
        $delete = $_GET['delete'];
        //删除数据
        novelModel::getInstance()->hide($id,$delete);
        $this->jump('删除成功！','?c=novel',0);
    }

	//编辑文章：显示表单
	public function edit() {
		//接收数据
		$id = (int)$_GET['id'];
		//获取小说信息
        $novel = novelModel::getInstance()->fetchOne($id);
		//分配给模板显示数据
		$this->smarty->assign('novel',$novel);
		$this->smarty->display('Novel/edit.html');
	}

	//更新数据
    public function update() {
        $id = (int)$_GET['id'];
        $title = $_POST['title'];
        $author = $_POST['author'];
        $isComplete = $_POST['isComplete'];
        $introduce = $_POST['introduce'];
        echo $introduce;
        $data = array(
            'title'      => $title,
            'author'     => $author,
            'isComplete' => $isComplete,
            'introduce'  => $introduce,
        );
        if (novelModel::getInstance()->updateOne($id,$data)) {
            $this->jump("{$title} 编辑成功！","?c=novel",0);
        } else {
            $this->jump("{$title} 编辑失败，3秒后自动返回！","?c=novel");
        }
        $this->index();
    }

	//显示章节详情
    public function chapter() {
        $id = (int)$_GET['id'];
        //构建分页参数
//        $pagesize = 50;
//        $page     = $_GET['page'] ?? 1;
//        $startrow = ($page-1)*$pagesize;
//        $records  = novelModel::getInstance()->rowCount_chapter($id);
//        //获取小说章节信息
        $chapters = novelModel::getInstance()->fetchAllChapter($id);
//        //分页对象
//        $params   = array(
//            'c'   => CONTROLLER,
//            'a'   => ACTION,
//            'id'  => $id,
//        );
//        $pageStr = NewPager::clickPage($records,$pagesize,$page,$params);
        //分配给模板显示数据
        $this->smarty->assign('chapters',$chapters);
//        $this->smarty->assign('pageStr',$pageStr);
        $this->smarty->display('Novel/chapter.html');
    }

    //显示内容详情
    public function content() {
        $id = (int)$_GET['ch'];
        //获取小说章节信息
        $content = novelModel::getInstance()->fetchOneContent($id);
        //分配给模板显示数据
        $this->smarty->assign('content',$content);
        $this->smarty->display('Novel/content.html');
    }

    //添加正则匹配的条件
    public function addReg() {
        $newRule = $_POST['newRule'];
        if (isset($newRule)) {
            novelModel::getInstance()->addRules($newRule);
            $this->add();
        } else {
            //显示表单
            $this->smarty->display('Novel/addReg.html');
        }
    }

    //重设正则匹配的条件
    public function resetReg() {
        novelModel::getInstance()->resetRules();
        $this->add();
    }

    //删除指定目录下的文件
    public function deldir($dir) {
        //先删除目录下的文件：
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->deldir($fullpath);
                }
            }
        }
        closedir($dh);
        //删除当前文件夹：
//        if(rmdir($dir)) {
//            return true;
//        } else {
//            return false;
//        }
    }
}