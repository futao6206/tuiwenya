<?php

//后台首页功能
namespace Admin\Controller;
use \Frame\Libs\baseController;
use \Admin\Model\createModel;

final class createController extends baseController {
    //显示首页数据
    public function index(){
        $rand_id = mt_rand(1,10714);
        $novels = createModel::getInstance()->recommend($rand_id);
        $results = array();
        foreach ($novels as $novel) {
            $introduce = $novel['introduce'];
            $introduce = str_replace(":关于{$novel['title']}：","：",$introduce);
//            $introduce = str_replace("。","。<br>",$introduce);
//            $introduce = str_replace("！","！<br>",$introduce);
//            $introduce = str_replace("？","？<br>",$introduce);
//            $introduce = str_replace("：","：<br>",$introduce);
            $results[] = [
                "title"    => $novel['title'],
                "url"      => "?c=novel&a=edit&id={$novel['id']}",
                "author"   => $novel['author'],
                "introduce"=> $introduce,
            ];
        }
        $this->smarty->assign('results',$results);
        $this->smarty->display('Index/create.html');
    }

    public function type() {
        $rand_id = mt_rand(1,10714);
        $novels = createModel::getInstance()->recommend($rand_id);
        $results = array();
        foreach ($novels as $novel) {
            $introduce = $novel['introduce'];
            $introduce = str_replace(":关于{$novel['title']}：","：",$introduce);
//            if (strpos($introduce,'”') !== false) {
//                $introduce = str_replace("”","”<br>",$introduce);
//            } else {
//                $introduce = str_replace("。","。<br>",$introduce);
//                $introduce = str_replace("！","！<br>",$introduce);
//                $introduce = str_replace("？","？<br>",$introduce);
//                $introduce = str_replace("：","：<br>",$introduce);
//            }
            $results[] = [
                "title"    => $novel['title'],
                "url"      => "?c=novel&a=edit&id={$novel['id']}",
                "author"   => $novel['author'],
                "introduce"=> $introduce,
            ];
        }
        $this->smarty->assign('time',date('Y年m月d日'));
        $this->smarty->assign('results',$results);
        $this->smarty->display('Index/create2.html');
    }
}