<?php
namespace Home\Model;
use \Frame\Libs\baseModel;


final class contentModel extends baseModel
{
    protected $table = 'novel';   //小说表
    protected $table_ch = 'chapter'; //章节表
    protected $table_co = 'content'; //内容表
    protected $table_so = 'source'; //来源表
    //连表获取章节
    public function fetchChapter($chapter_id) {
        $sql = "SELECT * FROM {$this->table_ch} WHERE id={$chapter_id}";
        return $this->pdo->fetchOne($sql);
    }

    //连表获取内容
    public function fetchContent($chapter_id) {
        $sql = "SELECT * FROM {$this->table_co} WHERE chapter_id={$chapter_id}";
        return $this->pdo->fetchOne($sql);
    }

    //获取上一章的id title
    public function fetchPreChapter($order_index,$novel_id) {
        $sql = "SELECT * FROM {$this->table_ch} ";
        $sql .= "WHERE chapter.novel_id={$novel_id} AND chapter.order_index<{$order_index} ORDER BY chapter.order_index DESC LIMIT 0,1";
        return $this->pdo->fetchOne($sql);
    }
    //获取下一章的id title
    public function fetchNextChapter($order_index,$novel_id) {
        $sql = "SELECT * FROM {$this->table_ch} ";
        $sql .= "WHERE chapter.novel_id={$novel_id} AND chapter.order_index>{$order_index} ORDER BY chapter.order_index ASC LIMIT 0,1";
        return $this->pdo->fetchOne($sql);
    }
    //获取抓取的小说来源 手动上传的小说分别处理！！
    public function fetchSource($chapter_id) {
        $sql = "SELECT {$this->table_so} FROM {$this->table_ch} ";
        $sql .= "WHERE id = {$chapter_id}";
        return $this->pdo->fetchOne($sql);
    }

    //用于更新下一章
    public function get_novel($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = {$id}";
        return $this->pdo->fetchOne($sql);
    }

    public function get_chapter($id) {
        $sql = "SELECT * FROM {$this->table_ch} where id={$id}";
        return $this->pdo->fetchOne($sql);
    }

    public function save_next_chapter($name,$novel_id,$total,$next_source,$next_chapter_title,$content) {
        //保存下一章
//        $content = $this->filterEmoji($content);//使用mb_utf8就可以保存表情了
        $content = addslashes($content);
        $next_order_index = $total+1;
        $sql_chapter = "INSERT INTO {$this->table_ch}(novel_id,title,order_index,source) VALUES('{$novel_id}','{$next_chapter_title}','{$next_order_index}','{$next_source}')";
        $this->pdo->exec($sql_chapter);
        $next_chapter_id = $this->pdo->fetchId();
        $sql_content = "INSERT INTO {$this->table_co}(chapter_id,content) VALUES('{$next_chapter_id}','{$content}')";
        $this->pdo->exec($sql_content);
        $sql_novel_update = "UPDATE {$this->table} set total = '{$next_order_index}' WHERE id = '{$novel_id}'";
        $this->pdo->exec($sql_novel_update);
        $url = "?c=content&n={$novel_id}&name={$name}&ch={$next_chapter_id}&o={$next_order_index}&title={$next_chapter_title}";
        return $url;
    }
    //过滤掉emoji表情
    public function filterEmoji($str) {
        $str = preg_replace_callback(
            '/./u', function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            }, $str);
        return $str;
    }
}