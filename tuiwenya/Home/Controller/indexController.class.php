<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/16
 * Time: 2:59 PM
 * 声明命名空间
 */

namespace Home\Controller;
require 'vendor/autoload.php';
use \Frame\Libs\baseController;//引入基础控制器类
use QL\QueryList;
use \Home\Model\searchModel;

//定义最终的首页控制器类 继承基础控制器类
final class indexController extends baseController {
    //首页显示方法
    public function index() {
        $kw = $_GET['kw'];
        if (empty($kw)) {//如果没有kw 那么显示首页
            $this->recommend();
            $this->smarty->display("novels/index.html");
        } else {
            //开始时间
            $startTime = microtime(true);

            $novels = searchModel::getInstance()->fetchAllWithName($kw);
            $count = count($novels);
            //如果数据库中不存在此小说
//            if ($count == 0) {//33yq.com搜索结果 baidu.com 搜索结果
//                $this->mijisou($kw,$startTime);
//            } else {
                $results = array();
                foreach ($novels as $novel) {
                    $title = "{$novel['title']}，{$novel['author']}，全文免费在线阅读-推文鸭-tuiwenya.com";
                    $title = str_replace($kw,"<span class='highlight'>{$kw}</span>",$title);
                    $introduce = $novel['introduce'];
                    $content = "《{$novel['title']}》讲述了{$introduce}... 推文鸭-tuiwenya.com";
                    $content = str_replace($kw,"<span class='highlight'>{$kw}</span>",$content);
                    $results[] = [
                        "title"       =>$title,
                        "url"         =>"?c=chapter&n={$novel['id']}&name={$novel['title']}",
                        "netloc"      =>"www.tuiwenya.com",
                        "content"     =>$content,
                        "is_recommend"=>true,
                        "is_parse"    =>true,
                    ];
                }
                $this->show($kw,$results,$startTime);
//            }

            //如果数据库中不存在此小说 那么记录在search表中
            if ($count == 0) {
                searchModel::getInstance()->saveKeyword($kw);
            }
        }
    }

    public function mijisou($kw,$startTime) {
        $url = "https://mijisou.com/?q=".$kw."+小说+在线阅读";
        // 定义采集规则
        $rules = [
            'title' => ['.result_header>a','text'],
            'url' => ['.result_header>a','href'],
            'content' => ['.result-content','text'],
        ];
        $rt = QueryList::get($url)->rules($rules)->query()->getData();
        $novels = $rt->all();
        //显示
        $results = array();
        foreach ($novels as $novel) {
            $title = $novel['title'];
            $title = str_replace($kw,"<span class='highlight'>{$kw}</span>",$title);
            $content = $novel['content'];
            $content = str_replace($kw,"<span class='highlight'>{$kw}</span>",$content);
            $results[] = [
                "title"       =>$title,
                "url"         =>$novel['url'],
                "netloc"      =>$novel['url'],
                "content"     =>$content,
                "is_recommend"=>false,
                "is_parse"    =>false,
            ];
        }
        $this->show($kw,$results,$startTime);
    }

    public function recommend() {//总共10722本小说 每次随机取8本
        $rand_id = mt_rand(1,10714);
        $novels = searchModel::getInstance()->recommend($rand_id);
        $recommends = array();
        foreach ($novels as $novel) {
            $recommends[] = [
                "title" => $novel['title'],
                "url"   => "?c=chapter&n={$novel['id']}&name={$novel['title']}"
            ];
        }
        $this->smarty->assign('recommends',$recommends);
    }

    public function show($kw,$results,$startTime) {
        $count = count($results);
        //结束时间
        $endTime = microtime(true);
        $time = round(($endTime-$startTime),2);
        $this->smarty->assign('count',$count);
        $this->smarty->assign('time',$time);
        $this->smarty->assign('kw',$kw);
        $this->smarty->assign('results',$results);
        $this->smarty->display("novels/result.html");
    }
}