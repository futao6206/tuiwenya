<?php
namespace Home\Controller;

require 'vendor/autoload.php';

use QL\QueryList;
use \Frame\Libs\baseController;
use \Home\Model\contentModel;

//定义最终的首页控制器类 继承基础控制器类
final class contentController extends baseController {
    //首页显示方法
    public function index() {
        //开始时间
//        $startTime = microtime(true);
        //创建模型类对象
        if (empty($_GET['n'])||empty($_GET['ch'])||empty($_GET['name'])||empty($_GET['o'])) {
            $this->smarty->display("except/404.html");
            return;
        }
        $novel_id = $_GET['n'];
        $chapter_id = $_GET['ch'];
        $order_index = $_GET['o'];
        $chapter_title = $_GET['title'];
        $name = $_GET['name'];
        $chapter = contentModel::getInstance()->fetchChapter($chapter_id);
        if ($chapter['novel_id']!=$novel_id) {
            $this->smarty->display("except/404.html");
            return;
        }
        $content = contentModel::getInstance()->fetchContent($chapter_id);
        $pre_chapter = contentModel::getInstance()->fetchPreChapter($order_index,$novel_id);
        $next_chapter = contentModel::getInstance()->fetchNextChapter($order_index,$novel_id);
        //当前章节 上一章 下一章地址
        $cur_chapter_url = "?c=content&n={$novel_id}&name={$name}&ch={$chapter_id}&o={$order_index}&title={$chapter_title}";
        if (!empty($pre_chapter)) {
            $pre_chapter_url = "?c=content&n={$novel_id}&name={$name}&ch={$pre_chapter['id']}&o={$pre_chapter['order_index']}&title={$pre_chapter['title']}";
            $this->smarty->assign("pre_chapter_url",$pre_chapter_url);
        }
        if (empty($next_chapter)) {//当前是最后一章 开始抓取
            $this->update();
        } else {
            $next_chapter_url = "?c=content&n={$novel_id}&name={$name}&ch={$next_chapter['id']}&o={$next_chapter['order_index']}&title={$next_chapter['title']}";
            $this->smarty->assign("next_chapter_url",$next_chapter_url);
        }

        //获取上一章和下一章
        $this->smarty->assign("name",$name);
        $this->smarty->assign("chapter",$chapter);
        $this->smarty->assign("content",$content);
        $this->smarty->assign("cur_chapter_url",$cur_chapter_url);
        $this->smarty->display("novels/content.html");

//        //结束时间
//        $endTime = microtime(true);
//        $time = round(($endTime-$startTime),2).'s';
//        echo "耗时：".$time;
    }

    /*
     * 如果当前有下一章 ：抓取下一章，并且更新novel表total
     * 如果没有下一章：提示没有下一章
     * if（source包含此字符串）说明已经是最后一章了
     */
    public function update() {
        //如果当前章节就是刚刚更新的章节 那么还会到update方法里来(浏览器预加载) ?c=content&a=update&ch={%$chapter.id%}
        $chapter_id = $_GET['ch'];
        if (empty($chapter_id)||!is_numeric($chapter_id)) {
            return;
        }
        $chapter = contentModel::getInstance()->get_chapter($chapter_id);
        $source = $chapter['source'];
        if (empty($source)) {
            return;
        }
        $novel_id = $chapter['novel_id'];
        $novel = contentModel::getInstance()->get_novel($novel_id);
        $total = $novel['total'];
        $domain = $novel['domain'];
        $name = $novel['title'];
        $this->get_next_chapter($domain,$source,$novel_id,$total,$name);
    }

    public function get_next_chapter($domain,$source,$novel_id,$total,$name) {
        $chapter_url = $domain.$source;
        $ql = QueryList::get($chapter_url)
            ->rules([
                array('.bottem1>a:eq(3)','href'),//小说下一章链接
            ]);
        $items = $ql->query()->getData()->all();
        $next_source = $items[0][0];
        $next_chapter_url = $domain.$next_source;
        if (strpos($source,$next_source)!==false) {//已经是最后一章了 弹出alert
            return;
        } else {//不包含，非最后一章
            $rules = [
                'title'  =>array('.zhangjieming>h1','text'),
                'content'=>array('#content>p','text'),
            ];
            $headers = [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.1.6) Gecko/20091201 SUSE/3.5.6-1.1.1 Firefox/3.5.6',
                    'Accept'     => 'application/json'
                ],
            ];
            $ql = QueryList::get($next_chapter_url,[],$headers);//小说下一章的内容
            $ql->rules($rules);
            $data = $ql->queryData();
            $content = '';
            $next_chapter_title = $data[0]['title']."\n\n";
            foreach ($data as $item) {
                if (empty($item['content'])) {
                    $content .= "<br><br>";
                } else {
                    $content .= $item['content'];
                }
            }
            //保存抓取到的内容 更新下一章地址
            $next_chapter_url = contentModel::getInstance()->save_next_chapter($name,$novel_id,$total,$next_source,$next_chapter_title,$content);
            $this->smarty->assign("next_chapter_url",$next_chapter_url);
        }
    }

    public function record_log($memory=0) {
        $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
        $fp = fopen("$DOCUMENT_ROOT/time_task.txt","a+");
        $str = date("Y-m-d h:i:s")."\n".$memory."\n\r";
        fwrite($fp,$str);
        fclose($fp);
    }
}