<?php
namespace Home\Controller;

require 'vendor/autoload.php';
use \Frame\Libs\baseController;
use \Home\Model\cacheModel;
use GuzzleHttp\Psr7\Response;
use QL\QueryList;
use \Frame\Libs\db;


header('Content-category:text/json');

//定义最终的首页控制器类 继承基础控制器类
final class cacheController extends baseController {
    //首页显示方法
    public function index() {
//        ignore_user_abort(true);// 即使Client断开(如关掉浏览器),PHP脚本也可以继续执行.
//        ini_set('memory_limit','1000M');// 临时设置最大内存占用为3G 这里设置并没有用呀
//        ini_set("max_execution_time",0);//
//        set_time_limit(0);// 0 不限时
        //开始记录时间
//        $star_memory = memory_get_usage();
//        $this->record_log($star_memory);

//        $domain = "https://www.33yq.com";
        //开始抓取全部小说链接
//        $this->get_novels_category($domain,1,19);

//        $userAgent = $this->get_random_user_agent();
//        $userAgent = str_replace(array("/r/n", "/r", "/n"), "", $userAgent); //
//        $headers = [
//            'headers' => [
//                'User-Agent' => $userAgent,
//                'Accept'     => 'application/json'
//            ],
//            'timeout' => 3.14,
//            ];
//        $this->get_max_page($domain,1,19);
//        $this->get_novels($domain,8,2,10);
//        $novel_url = "https://www.33yq.com/read/68210/";//
//        $this->get_chapters($domain,$novel_url,1);

//        echo $novel_url;
//        echo $this->get_author("作    者：承流");
//        echo $this->get_introduce($str);

//        $chapter_urls[] = "https://www.33yq.com/read/50867/25023663.shtml";
//        $this->get_contents($chapter_urls);
//        cacheModel::getInstance()->saveChapter();
//        cacheModel::getInstance()->updateSource_chapter("where novel_id=1");

/*
        //根据source表抓取小说
        $arr = cacheModel::getInstance()->getSource();
        $novels = array();
        $categories = array();
        $ids = array();
        foreach ($arr as $item) {
            $novels[] = $item['novel'];
            $categories[] = $item['category'];
            $ids[] = $item['id'];
        }
//        print_r($arr);
        if (count($novels)>0) {
            $this->multi_get_novel_info($domain,$ids,$novels,$categories);
        }
*/

        //获取最近更新的小说
//        $this->get_postdate_novels($domain,1,10);
        //更新未抓取完全的小说
//        cacheModel::getInstance()->update_finished_novel();
//        $this->multi_update_chapters();

        //开始获取每一章节的内容
//        $this->multi_get_content_info();


//        $count = 1;
//        $min = 7516490;
//        $length = 2000;
//        while ($count>0) {
//            $contents = cacheModel::getInstance()->get_contents($min,$length);
//            $count = count($contents);
//            foreach ($contents as $content) {
//                cacheModel::getInstance()->save_contents($content);
//            }
//            $min += $length;
//            $this->record_log($min);
//        }
//        echo "success";




        $url = "https://mijisou.com/?q=表哥成天自打脸+小说+在线阅读";
        echo $url;
        // 定义采集规则
        $rules = [
            'title' => ['.result_header>a','text'],
            'url' => ['.result_header>a','href'],
            'content' => ['.result-content','text'],
        ];
        $rt = QueryList::get($url)->rules($rules)->query()->getData();

        print_r($rt->all());


    }

    //获取重定向以后的真实地址
    public function get_redirect_url($url){
        $header = get_headers($url, 1);
        var_dump($header);
        if (strpos($header[0], '301') !== false || strpos($header[0], '302') !== false) {
            if(is_array($header['Location'])) {
                return $header['Location'][count($header['Location'])-1];
            }else{
                return $header['Location'];
            }
        }else {
            return $url;
        }
    }

    function curlGet($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, true);
        //函数中加入下面这条语句
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        return curl_exec($ch);
    }

    function get_redirect_url_curl($url, $referer='', $timeout = 10) {
        $redirect_url = false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);//不返回请求体内容
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);//允许请求的链接跳转
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: */*',
            'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
            'Connection: Keep-Alive'));
        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);//设置referer
        }
        $content = curl_exec($ch);
        if(!curl_errno($ch)) {
            $redirect_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);//获取最终请求的url地址
        }
        return $redirect_url;
    }

    function get_redirect_url_muti($links) {
        //curl模拟多线程
        $mh = curl_multi_init();
        foreach ($links as $i => $link) {
            $conn[$i] = curl_init($link);
            curl_setopt($conn[$i], CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($conn[$i], CURLOPT_REFERER, $link);
            curl_setopt($conn[$i], CURLOPT_TIMEOUT, 60); //超时时间
            curl_setopt($conn[$i], CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($conn[$i], CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($conn[$i], CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3551.3 Safari/537.36');

            curl_multi_add_handle($mh, $conn[$i]);
        }

        $active = null;

        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
        } while ($running > 0);

        $result = [];
        //获取page内容
        foreach ($links as $i => $link) {
            $redirect_url = curl_getinfo($conn[$i], CURLINFO_EFFECTIVE_URL);
            curl_close($conn[$i]);
            if ($redirect_url != $link && strpos($redirect_url,'baidu')==false) {
                $result[] = $redirect_url;
            }
        }
        return $result;
    }

    //根据类抓取小说
    public function get_novels_category($domain,$current_category,$max_category) {
        if ($current_category>$max_category) {
            echo "最大类别，第：".$max_category."类抓取结束\n";
        } else {
            if ($current_category == 8) {
                $this->get_novels_category($domain,$current_category+1,$max_category);
            } else {
                $url = $domain."/sort/{$current_category}/1/";
                $ql = QueryList::get($url)//,[], $headers
                ->rules([
                    array('.ngroup','href'),//array('.pagelink>a','href'),
                ]);
                $ql->query(function ($item) use ($domain,$current_category,$max_category){
                    $str = $item[0];
                    $arr = explode('/',$str);
                    $max_page = $arr[3];
                    //获取当前类别下所有小说
                    $this->get_novels($domain,$current_category,1,$max_page);
                });
                //递归抓取所有列表下的小说
                $this->get_novels_category($domain,$current_category+1,$max_category);
            }
        }
    }

    //抓取最新小说
    public function get_postdate_novels($domain,$current_page,$max_page) {
        //获取新增加的书籍
        if ($current_page>$max_page) {
            echo "最大页数,第：".$max_page."页抓取结束\n";
        } else {
            $url = $domain."/postdate/{$current_page}/";
            $ql = QueryList::get($url)//,[], $headers
            ->rules([
                array('.title>h2>a','href'),//array('.pagelink>a','href'),
            ]);
            $ql->query(function ($item) use ($domain,$current_page,$max_page){
                //抓取当前页小说
                $novel_url = $domain.$item[0];
//                $this->get_chapters($domain,$novel_url,$current_category);
                //保存小说链接
                $category = "";
                cacheModel::getInstance()->saveSource($novel_url,$category);
            });
            //递归抓取下一页小说
            $this->get_postdate_novels($domain,$current_page+1,$max_page);
        }
    }
    public function get_novels($domain,$current_category,$current_page,$max_page) {
        if ($current_page>$max_page) {
            echo "最大页数,第：".$max_page."页抓取结束\n";
        } else {
            $url = $domain."/sort/{$current_category}/{$current_page}/";
            $ql = QueryList::get($url)//,[], $headers
                ->rules([
                    array('.title>h2>a','href'),//array('.pagelink>a','href'),
                ]);
            $ql->query(function ($item) use ($domain,$current_category,$current_page,$max_page){
                //抓取当前页小说
                $novel_url = $domain.$item[0];
//                $this->get_chapters($domain,$novel_url,$current_category);
//                echo $novel_url."<br>";
                //保存小说链接
                $category = $this->get_category($current_category);
                cacheModel::getInstance()->saveSource($novel_url,$category);
            });
            //递归抓取下一页小说
            $this->get_novels($domain,$current_category,$current_page+1,$max_page);
        }
    }

    //更新抓取失败的小说的章节
    public function multi_update_chapters() {
        $novels = cacheModel::getInstance()->get_unfinished_novel();
        $urls = array();
        $ids = array();
        $domain = $novels[0]['domain'];
        foreach ($novels as $novel) {
            $urls[] = $novel['domain'].$novel['source'];
            $ids[] = $novel['id'];
        }
        $rules = [
            'chapter_title'=>array('dd>a','text'),//章节标题
            'chapter'      =>array('dd>a','href'),//章节链接
        ];
        $range = '';
        QueryList::rules($rules)
            ->range($range)
            ->multiGet($urls)
            // 设置并发数
            ->concurrency(5)
            // 设置GuzzleHttp的一些其他选项
            ->withOptions([
                'timeout' => 180
            ])
            // 设置HTTP Header
            ->withHeaders([
                'User-Agent' => 'Mozilla/1.22 (compatible; MSIE 10.0; Windows 3.1)',
            ])
            // HTTP success回调函数
            ->success(function (QueryList $ql, Response $response, $index) use ($domain,$ids,$urls){
                $items = $ql->queryData();
                $chapter_titles = array();
                $chapters = array();
                foreach ($items as $item) {
                    if (!empty($item['chapter_title'])) {
                        $chapter_titles[] = $item['chapter_title'];
                    }
                    if (!empty($item['chapter'])) {
                        $chapters[] = $domain.$item['chapter'];
                    }
                }
                $novel_id = $ids[$index];
                //此时小说可能又更新了 需要更新总章节数
                $total = count($chapters);
                cacheModel::getInstance()->update_total_novel($novel_id,$total);
                //更新 novel isDownload 的值
                cacheModel::getInstance()->update_isComplete_novel($novel_id);
                //更新章节表
                cacheModel::getInstance()->saveChapter_add($domain,$novel_id,$chapter_titles,$chapters);
            })
            // HTTP error回调函数
            ->error(function (QueryList $ql, $reason, $index) use ($urls){
                // ...
                echo "error:".$urls[$index]."爬取失败";
            })
            ->send();
    }

    //获取小说章节
    public function multi_get_chapters($domain,$ids,$novels,$categories) {
        $rules = [
            'title'        =>array('#info>a>h1','text'),//小说标题
            'introduce'    =>array('div>p','text'),//描述 作者 类别
            'chapter_title'=>array('dd>a','text'),//章节标题
            'chapter'      =>array('dd>a','href'),
        ];
        $range = '';
        QueryList::rules($rules)
            ->range($range)
            ->multiGet($novels)
            // 设置并发数
            ->concurrency(5)
            // 设置GuzzleHttp的一些其他选项
            ->withOptions([
                'timeout' => 180
            ])
            // 设置HTTP Header
            ->withHeaders([
                'User-Agent' => 'Mozilla/1.22 (compatible; MSIE 10.0; Windows 3.1)',
            ])
            // HTTP success回调函数
            ->success(function (QueryList $ql, Response $response, $index) use ($domain,$ids,$novels,$categories){
                $items = $ql->queryData();
                $title = $items[0]['title'];
                $introduces = array();
                $chapter_titles = array();
                $chapters = array();
                foreach ($items as $item) {
                    if (!empty($item['introduce'])) {
                        $introduces[] = $item['introduce'];
                    }
                    if (!empty($item['chapter_title'])) {
                        $chapter_titles[] = $item['chapter_title'];
                    }
                    if (!empty($item['chapter'])) {
                        $chapters[] = $domain.$item['chapter'];
                    }
                }
                //保存一本小说
                $novel_url = $novels[$index];
                $novel_url = str_replace($domain,"",$novel_url);
                $author = $this->get_author($introduces[0]);
                $total = count($chapters);
                $category = $categories[$index];
                $introduce = $this->get_introduce($introduces[6]);
                $novel_id = cacheModel::getInstance()->saveNovel($title,$author,$total,$category,$introduce,$domain,$novel_url);
                //更新isDownload的值
                $source_id = $ids[$index];
                cacheModel::getInstance()->updateDownload($source_id);
                //记录更新isDownload的时间
                $end_memory = memory_get_usage();
                $this->record_log($end_memory);
                //保存章节内容
                $this->get_contents($domain,$chapters,$novel_id);
            })
            // HTTP error回调函数
            ->error(function (QueryList $ql, $reason, $index) use ($novels){
                // ...
                echo "error:".$novels[$index]."爬取失败";
            })
            ->send();
    }

    //获取小说章节 1、保存小说 2、保存章节 3、保存内容
    public function multi_get_novel_info($domain,$ids,$novels,$categories) {
        $rules = [
            'title'        =>array('#info>a>h1','text'),//小说标题
            'introduce'    =>array('div>p','text'),//描述 作者 类别
            'chapter_title'=>array('dd>a','text'),//章节标题
            'chapter'      =>array('dd>a','href'),
        ];
        $range = '';
        QueryList::rules($rules)
            ->range($range)
            ->multiGet($novels)
            // 设置并发数
            ->concurrency(1)
            // 设置GuzzleHttp的一些其他选项
            ->withOptions([
                'timeout' => 180
            ])
            // 设置HTTP Header
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 7.1; Trident/5.0)',
            ])
            // HTTP success回调函数
            ->success(function (QueryList $ql, Response $response, $index) use ($domain,$ids,$novels,$categories){
                $items = $ql->queryData();
                $title = $items[0]['title'];
                $introduces = array();
                $chapter_titles = array();
                $chapters = array();
                foreach ($items as $item) {
                    if (!empty($item['introduce'])) {
                        $introduces[] = $item['introduce'];
                    }
                    if (!empty($item['chapter_title'])) {
                        $chapter_titles[] = $item['chapter_title'];
                    }
                    if (!empty($item['chapter'])) {
                        $chapters[] = $domain.$item['chapter'];
                    }
                }
                //1、保存一本小说
                $novel_url = $novels[$index];
                $novel_url = str_replace($domain,"",$novel_url);
                $author = $this->get_author($introduces[0]);
                $total = count($chapters);
                $category = $categories[$index];
                $introduce = $this->get_introduce($introduces[6]);
                $novel_id = cacheModel::getInstance()->saveNovel($title,$author,$total,$category,$introduce,$domain,$novel_url);
                echo $novel_id." <br>";
                //更新isDownload的值
                $source_id = $ids[$index];
                cacheModel::getInstance()->updateDownload($source_id);

                //2、保存章节信息
                //更新章节表
                cacheModel::getInstance()->saveChapter_add($domain,$novel_id,$chapter_titles,$chapters);

                //回收内存
                unset($introduce);
                unset($chapter_titles);
                unset($chapters);
            })
            // HTTP error回调函数
            ->error(function (QueryList $ql, $reason, $index) use ($novels,$ids){
                // ...
                echo "error:".$novels[$index]."爬取失败，根本不会保存";
//                cacheModel::getInstance()->update_failure_novel($ids[$index]);
            })
            ->send();
    }

    //3、单纯的保存未完成的内容抓取
    public function multi_get_content_info() {
        $contents = cacheModel::getInstance()->get_unfinished_content();
        $urls = array();
        $chapter_ids = array();
        $novel_ids = array();
        $domain = "https://www.33yq.com";


        if (count($contents)==0) {
            echo "no";
            return;
        }
        foreach ($contents as $content) {
            $urls[] = $domain.$content['source'];
            $chapter_ids[] = $content['id'];
            $novel_ids[] = $content['novel_id'];
        }
        $rules = [
            'content'=>array('#content>p','text'),
        ];
        $range = '';
        $this->record_log(1);
        //appKey 信息
        $appKey = 'Basic '. 'RElOWVUxZmpEQTFuWXk4MzpPUEhzcU9MSERjMzJWeG9t';

        //接下来使用蘑菇隧道代理进行访问（也可以使用curl方式)
        $opts = array(
//            'http' => array(
//                'proxy' => 'secondtransfer.moguproxy.com:9001',
//                'request_fulluri' => true,
//                'header' => "Proxy-Authorization: {$appKey}",
//            ),
            'timeout' => 180,
        );
        QueryList::rules($rules)
            ->range($range)
            ->multiGet($urls)
            // 设置并发数
            ->concurrency(10)
            // 设置GuzzleHttp的一些其他选项 代理等
            ->withOptions($opts)
            // 设置HTTP Header
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.1.6) Gecko/20091201 SUSE/3.5.6-1.1.1 Firefox/3.5.6',
            ])
            // HTTP success回调函数
            ->success(function (QueryList $ql, Response $response, $index) use ($domain,$chapter_ids,$novel_ids){
                $data = $ql->queryData();
                $content = '';
                foreach ($data as $item) {
                    if (empty($item['content'])) {
                        $content .= "<br><br>";
                    } else {
                        $content .= $item['content'];
                    }
                }
                $novel_id = $novel_ids[$index];
                $chapter_id = $chapter_ids[$index];
                //保存content 并更新content_id
                cacheModel::getInstance()->saveContent($novel_id,$chapter_id,$content);

                //回收内存
                unset($novel_id);
                unset($chapter_id);
                unset($content);
            })
            // HTTP error回调函数
            ->error(function (QueryList $ql, $reason, $index) use ($urls,$chapter_ids){
                // ...
                echo "error:".$urls[$index]."爬取失败,根本不会保存";
//                cacheModel::getInstance()->update_failure_chapter($chapter_ids[$index]);
            })
            ->send();
        $this->record_log(2);
        echo "success!";
    }

    public function get_contents($domain,$chapter_urls,$novel_id) {
        $rules = [
            'title'  =>array('.zhangjieming>h1','text'),
            'content'=>array('#content>p','text'),
        ];
        $range = '';
        QueryList::rules($rules)
            ->range($range)
            ->multiGet($chapter_urls)
            // 设置并发数
            ->concurrency(5)
            // 设置GuzzleHttp的一些其他选项
            ->withOptions([
                'timeout' => 180
            ])
            // 设置HTTP Header
            ->withHeaders([
                'User-Agent' => 'Mozilla/1.22 (compatible; MSIE 10.0; Windows 3.1)',
            ])
            // HTTP success回调函数
            ->success(function (QueryList $ql, Response $response, $index) use ($domain,$chapter_urls,$novel_id){
                $data = $ql->queryData();
                $content = '';
                $title = $data[0]['title']."\n\n";
                foreach ($data as $item) {
                    if (empty($item['content'])) {
                        $content .= "<br><br>";
                    } else {
                        $content .= $item['content'];
                    }
                }
                $chapter_url = $chapter_urls[$index];
                $chapter_url = str_replace($domain,"",$chapter_url);
                $chapter_id = cacheModel::getInstance()->saveChapter($title,$novel_id,$index,$chapter_url);
//                if (!empty($chapter_id)) {//TODO：如果忽略插入应该会返回啥啊  这里的错误导致部分内容表跟章节表分开抓取了！！！
//                    cacheModel::getInstance()->saveContent($novel_id,$chapter_id,$content);
//                }
            })
            // HTTP error回调函数
            ->error(function (QueryList $ql, $reason, $index) use ($chapter_urls){
                // ...
                echo "error:".$chapter_urls[$index]."爬取失败";
            })
            ->send();
        echo "novel_id为：".$novel_id."保存成功\n";
    }

    public function get_author($str) {
        $str = substr($str, 17);//由于中文编码问题 这里匹配作者需要特殊处理
        if (strpos($str, "漫步名山110911064125050") !== false) {
            $str = "党云清";
        }
        return $str;
    }

    public function update_error_author() {
        $arr = cacheModel::getInstance()->get_unfinished_novel();
        $domain = "https://www.33yq.com";
        $sources = array();
        $ids = array();
        foreach ($arr as $item) {
            $ids[] = $item['id'];
            $sources[] = $domain.$item['source'];
        }
        $rules = [
            'introduce'    =>array('div>p','text'),//描述 作者 类别
        ];
        $range = '';
        QueryList::rules($rules)
            ->range($range)
            ->multiGet($sources)
            // 设置并发数
            ->concurrency(32)
            // 设置GuzzleHttp的一些其他选项
            ->withOptions([
                'timeout' => 180
            ])
            // 设置HTTP Header
            ->withHeaders([
                'User-Agent' => 'Mozilla/1.22 (compatible; MSIE 10.0; Windows 3.1)',
            ])
            // HTTP success回调函数
            ->success(function (QueryList $ql, Response $response, $index) use ($domain,$ids) {
                $items = $ql->queryData();
                $introduces = array();
                foreach ($items as $item) {
                    if (!empty($item['introduce'])) {
                        $introduces[] = $item['introduce'];
                    }
                }
                //更新错误的小说作者
                $author = $this->get_author($introduces[0]);
                cacheModel::getInstance()->update_error_author($ids[$index], $author);
                echo "id:".$ids[$index]."<br>";
            })
            // HTTP error回调函数
            ->error(function (QueryList $ql, $reason, $index) use ($ids){
                // ...
                echo "error:".$ids[$index]."爬取失败";
            })
            ->send();
    }

    public function get_introduce($str) {
        if (strpos($str, "μ's")) {
            $str = str_replace("μ's","us",$str);
        }
        if (strpos($str, "群号")) {
            $str = substr($str, 0, strpos($str, "群号"));
        }
        if (strpos($str, "…")) {
            return substr($str, 0, strpos($str, "…"));
        } else {
            return $str;
        }
    }

    public function get_category($index) {
        $index = $index-1;
        $index = $index<0?0:$index;
        $index = $index>19?19:$index;
        $categorys = ['玄幻','奇幻','武侠','都市','历史','军事','悬疑','游戏','科幻','体育','官场','古言','现言','幻言','仙侠','青春','穿越','女生','N次元'];
        return $categorys[$index];
    }

    public function get_random_user_agent() {
        $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
        $arr = file($DOCUMENT_ROOT . DS . 'user_agents.txt');
        $n = rand(0,count($arr)-1);
        $ua = $arr[$n];
        if (empty($ua)) {
            $ua = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)';
        }
        return $ua;
    }

    public function update_source() {
        cacheModel::getInstance()->updateSource();
    }

    public function output() {//打印tmp.php中的数组
        $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
        $arr = include($DOCUMENT_ROOT . DS . "tmp.php");
        print_r($arr);
    }

    public function get_chapters($domain,$novel_url,$current_category) {
        $ql = QueryList::get($novel_url)
            ->rules([
                'title'        =>array('#info>a>h1','text'),//小说标题
                'introduce'    =>array('div>p','text'),//描述 作者 类别
                'chapter_title'=>array('dd>a','text'),//章节标题
                'chapter'      =>array('dd>a','href'),
            ]);
        $query = $ql->query(function ($item){
            return $item;
        });
        $items = $query->getData()->all();
        $title = $items[0]['title'];
        $introduces = array();
        $chapter_titles = array();
        $chapters = array();
        foreach ($items as $item) {
            if (!empty($item['introduce'])) {
                $introduces[] = $item['introduce'];
            }
            if (!empty($item['chapter_title'])) {
                $chapter_titles[] = $item['chapter_title'];
            }
            if (!empty($item['chapter'])) {
                $chapters[] = $domain.$item['chapter'];
            }
        }
        //保存一本小说
        $author = $this->get_author($introduces[0]);
        $total = count($chapters);
        $category = $this->get_category($current_category);
        $introduce = $this->get_introduce($introduces[6]);
        $novel_id = cacheModel::getInstance()->saveNovel($title,$author,$total,$category,$introduce,$novel_url);
        //保存章节内容
        $this->get_contents($chapters,$novel_id);
    }

    public function record_log($memory=0) {
        $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
        $fp = fopen("$DOCUMENT_ROOT/time_task.txt","a+");
        $str = date("Y-m-d h:i:s")."\n".$memory."\n\r";
        fwrite($fp,$str);
        fclose($fp);
    }

    public function saveConfig($content) {
        $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
        $filePath = $DOCUMENT_ROOT.DS."tmp.php";
        file_put_contents($filePath, "<?php\n\nreturn " . var_export($content, true) . ';');
    }

    public function finished_test() {

//        $this->update_error_author();
//        $url = "https://www.33yq.com/read/18931/46563624.shtml";
//        $this->get_next_chapter($url);

        //1、从本地获取数据
        $sql = "SELECT * FROM novel LIMIT 5,30";
        $arr = db::getInstance()->fetchAll($sql);

        //2、将本地数据保存到阿里云 每次保存一本小说的方式 防止关联失效
        //SELECT * FROM novel
        //SELECT * FROM chapter WHERE novel_id = 6
        //SELECT * FROM content WHERE chapter_id = 379

        foreach ($arr as $items) {//获取每一本小说

            $novel_id = $items['id'];
            //构建字符名列表 以及值列表
            $fields = "";
            $values = "";
            foreach ($items as $key=>$value) {
                if (strpos($key,"id")===false) {
                    $fields .= "$key,";
                    $values .= "'$value',";
                }
            }
            $fields = rtrim($fields,",");
            $values = rtrim($values,",");
            //插入novel 获取novel_id
            $sql = "INSERT INTO novel($fields) values ($values)";
            $new_novel_id = cacheModel::getInstance()->save_local_novel($sql);

            //获取chapter
            $sql_chapter = "SELECT * FROM chapter where novel_id={$novel_id}";
            $chapters = db::getInstance()->fetchAll($sql_chapter);
            foreach ($chapters as $chapter) {
                $fields = "";
                $values = "";
                foreach ($chapter as $key=>$value) {//每一条chapter
                    if (strpos($key,"id")===false) {
                        $fields .= "$key,";
                        $values .= "'$value',";
                    }
                }
                $fields .= "novel_id,";
                $values .= "'{$new_novel_id}',";
                $fields = rtrim($fields,",");
                $values = rtrim($values,",");
                $sql = "INSERT INTO chapter($fields) values ($values)";
                $new_chapter_id = cacheModel::getInstance()->save_local_novel($sql);

                $chapter_id = $chapter['id'];
                $sql_content = "SELECT * FROM content where chapter_id={$chapter_id}";
                $contents = db::getInstance()->fetchOne($sql_content);

                $content = $contents['content'];
                $content = addslashes($content);
                $new_sql_content = "INSERT INTO content(chapter_id,content) VALUES('{$new_chapter_id}','{$content}')";
                $new_content_id = cacheModel::getInstance()->save_local_novel($new_sql_content);
                $sql_chapter_update = "UPDATE chapter set content_id = '{$new_content_id}' WHERE id = '{$new_chapter_id}'";
                cacheModel::getInstance()->save_local_novel($sql_chapter_update);
            }
        }
    }

    public function test1() {

    }

}