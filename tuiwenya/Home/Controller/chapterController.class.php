<?php
namespace Home\Controller;

use Home\Model\chapterModel;
use \Frame\Libs\baseController;

//定义最终的首页控制器类 继承基础控制器类
final class chapterController extends baseController {
    //首页显示方法
    public function index() {
//        //开始时间
//        $startTime = microtime(true);
        //创建模型类对象
        if (empty($_GET['n'])||empty($_GET['name'])) {
            $this->smarty->display("except/404.html");
            return;
        }
        $novel_id = $_GET['n'];
        $name = $_GET['name'];
        $novel = chapterModel::getInstance()->fetchOneNovel($novel_id);
        //防止被人直接使用id遍历数据
        if ($novel['title']!=$name) {
            $this->smarty->display("except/404.html");
            return;
        }
        //更新小说阅读数
        $readCount = $novel['readCount'];
        chapterModel::getInstance()->updateRead($novel_id,$readCount);
        //向视图赋值 构建分页参数 并显示
        $pagesize = 100;
        $page     = $_GET['page'] ?? 1;
        $startrow = ($page-1)*$pagesize;
        $records  = $novel['total'];
        $pageCount = $records/$pagesize;
        //计算总页数
        if (is_float($pageCount)) {
            $pageCount++;
        }
        $pageCount = (int)$pageCount;
        //页数安全判断
        if ($page<1||$page>$pageCount) {
            $this->smarty->display("except/404.html");
            return;
        }
        $page_pre = $page - 1 < 1 ? 1 : $page - 1;
        $page_next = $page + 1 > $pageCount ? $pageCount : $page + 1;
        //分页数组
        $pages = array();
        for ($i=0;$i<$pageCount;$i++) {
            $pre = $i*$pagesize + 1;
            $now = ($i+1)*$pagesize > $records ? $records : ($i+1)*$pagesize;
            $pages[] = $pre.'-'.$now.'章';
        }
        $pages[] = $records;
        //分页对象
        $chapters = chapterModel::getInstance()->fetchChapter($startrow,$pagesize,$novel_id);
        $this->smarty->assign(array(
            'novel'      => $novel,
            'chapters'   => $chapters,
            'pageCount'  => $pageCount,
            'pages'      => $pages,
            'page'       => $page,
            'page_pre'   => $page_pre,
            'page_next'  => $page_next,
        ));
        $this->smarty->display("novels/chapter.html");


//        //结束时间
//        $endTime = microtime(true);
//        $time = round(($endTime-$startTime),2);
//        echo "耗时：".$time;
    }
}