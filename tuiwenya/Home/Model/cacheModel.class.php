<?php

//文章模型
namespace Home\Model;
use \Frame\Libs\baseModel;

final class cacheModel extends baseModel {

    //save
    public function saveNovel($title,$author,$total,$category,$introduce,$domain,$source) {
        $sql_novel = "INSERT INTO novel (title,author,total,category,introduce,domain,source) ";
        $sql_novel .= "VALUES('{$title}','{$author}','{$total}','{$category}','{$introduce}','{$domain}','{$source}')";
        $this->pdo->exec($sql_novel);
        $content_id = $this->pdo->fetchId();

        return $content_id;
    }

    public function saveChapter($title,$novel_id,$order_index,$source) {
        $order_index ++;//每个order_index 都自增1 防止出现0的情况
        $sql_chapter = "INSERT IGNORE INTO chapter (title,novel_id,order_index,source) ";
        $sql_chapter .= "VALUES('{$title}','{$novel_id}','{$order_index}','{$source}')";
        $this->pdo->exec($sql_chapter);
        $chapter_id = $this->pdo->fetchId();

        return $chapter_id;
    }

    public function saveChapter_add($domain,$novel_id,$chapter_titles,$chapters) {
        for ($i=0;$i<count($chapters);$i++) {
            $order_index = $i+1;//每个order_index 都+1 防止出现0的情况
            $title = $chapter_titles[$i];
            $source = $chapters[$i];
            $source = str_replace($domain,"",$source);
            $sql_chapter = "INSERT IGNORE INTO chapter (title,novel_id,order_index,source,content_id) ";
            $sql_chapter .= "VALUES('{$title}','{$novel_id}','{$order_index}','{$source}','0')";
            $this->pdo->exec($sql_chapter);
        }
    }

    public function saveContent($novel_id,$chapter_id,$content) {
        $content = addslashes($content);
        $sql_content = "INSERT INTO content(chapter_id,content) VALUES('{$chapter_id}','{$content}')";
        $this->pdo->exec($sql_content);
        $content_id = $this->pdo->fetchId();
        $sql_chapter_update = "UPDATE chapter set content_id = '{$content_id}' WHERE id = '{$chapter_id}'";

        return $this->pdo->exec($sql_chapter_update);
    }

    public function saveSource($novel_url,$category) {//重复数据就忽略不插入
        $sql = "INSERT IGNORE INTO source(novel,category) VALUES('{$novel_url}','{$category}')";
        return $this->pdo->exec($sql);
    }

    public function save_local_novel($sql) {
        $this->pdo->exec($sql);
        return $this->pdo->fetchId();
    }

    //get
    public function getSource() {
        $sql = "SELECT * FROM source WHERE isDownload ='0'";// LIMIT 0,1
        return $this->pdo->fetchAll($sql);
    }

    public function get_unfinished_novel() {
        $sql_novel = "SELECT * FROM novel WHERE isComplete = '0'";//  LIMIT 0,1
        return $this->pdo->fetchAll($sql_novel);
    }

    public function get_unfinished_content() {
        $sql_content = "SELECT * FROM chapter WHERE content_id = '0' LIMIT 0,2000";//
        return $this->pdo->fetchAll($sql_content);
    }

    public function get_novel($id) {
        $sql = "SELECT * FROM novel WHERE id = {$id}";
        return $this->pdo->fetchOne($sql);
    }

    public function get_chapter($id) {
        $sql = "SELECT * FROM chapter where id={$id}";
        return $this->pdo->fetchOne($sql);
    }

    public function get_contents($min,$length) {
        $sql = "SELECT * FROM content WHERE id > {$min} LIMIT 0,{$length}";
        return $this->pdo->fetchAll($sql);
    }

    public function save_contents($contents) {
        $content = addslashes($contents['content']);
        $sql = "INSERT IGNORE INTO content_tmp(chapter_id,content) VALUES('{$contents['chapter_id']}','{$content}')";
        return $this->pdo->exec($sql);
    }

    public function update_utime($id) {
        $date_time = date('Y-m-d H:i:s');
        $sql = "UPDATE chapter SET utime='{$date_time}' WHERE id={$id}";
        return $this->pdo->exec($sql);
    }

    //update
    public function update_error_author($novel_id,$author) {
        $sql = "UPDATE novel SET author = '{$author}',isComplete = '1' WHERE id = '{$novel_id}'";
        return $this->pdo->exec($sql);
    }

    public function update_finished_novel() {
        $sql_novel = "SELECT * FROM novel";
        $novels = $this->pdo->fetchAll($sql_novel);
        foreach ($novels as $novel) {
            $novel_id = $novel['id'];
            $sql_chapter = "SELECT * FROM chapter WHERE novel_id = '{$novel_id}'";
            $chapter_count = $this->pdo->rowCount($sql_chapter);
            $novel_total = $novel['total'];
            if ($chapter_count!=$novel_total) {
                $sql_novel_update = "UPDATE novel SET isComplete = '0' WHERE id = '{$novel_id}'";
                $this->pdo->exec($sql_novel_update);
                echo "update_finished_novel:".$novel_id."\n";
            }
        }
    }

    public function update_failure_chapter($chapter_id) {
        $sql = "UPDATE chapter SET novel_id = '0' WHERE id = '{$chapter_id}'";
        $this->pdo->exec($sql);
    }

    public function update_failure_novel($novel_id) {
        $sql = "UPDATE novel SET isComplete = '0' WHERE id = '{$novel_id}'";
        $this->pdo->exec($sql);
    }

    public function update_finished_source() {
        $sql = "SELECT * FROM source WHERE isDownload !='0'";// LIMIT 0,1
//        $sources = $this->pdo->fetchAll($sql);
//        foreach ($sources as $source) {
//            $novel = $source['novel'];
//            $novel = str_replace("https://www.33yq.com","",$novel);
//            $sql_novel = "SELECT * FROM novel WHERE source = '{$novel}'";
//            $novel_count = $this->pdo->rowCount($sql_novel);
//            if ($novel_count == 1) {
//                echo "y";
//            } else {
//                echo "n";
//            }
//        }
        $sql_dd = "SELECT * FROM novel";
        echo $this->pdo->rowCount($sql)."\n";
        echo $this->pdo->rowCount($sql_dd)."\n";
    }

    public function updateSource() {
        $sql_sources = "SELECT source FROM novel";
        $sources = $this->pdo->fetchAll($sql_sources);
        foreach ($sources as $source) {
            $sql_update = "UPDATE source SET isDownload='1' WHERE novel = '{$source['source']}'";
            $this->pdo->exec($sql_update);
        }
    }

    public function updateDownload($id) {
        $sql = "UPDATE source SET isDownload = '1' WHERE id = '{$id}'";
        $this->pdo->exec($sql);
    }

    public function update_isComplete_novel($id) {
        $sql = "UPDATE novel SET isComplete = '1' WHERE id = '{$id}'";
        $this->pdo->exec($sql);
    }

    public function update_total_novel($id,$total) {
        $sql = "UPDATE novel SET total = '{$total}' WHERE id = '{$id}'";
        $this->pdo->exec($sql);
    }

    public function updateSource_novel() {
        $sql_sources = "SELECT source,id FROM novel";
        $sources = $this->pdo->fetchAll($sql_sources);
        foreach ($sources as $source) {
            $newStr = str_replace("https://www.33yq.com","",$source['source']);
            $sql_update = "UPDATE novel SET source='{$newStr}' WHERE id = '{$source['id']}'";
            $this->pdo->exec($sql_update);
        }
    }

    public function updateSource_chapter($where = 'where 2>1') {
        $sql_sources = "SELECT source,id FROM chapter ";
        $sql_sources .= $where;
        $sources = $this->pdo->fetchAll($sql_sources);
        foreach ($sources as $source) {
            $newStr = str_replace("https://www.33yq.com","",$source['source']);
            $sql_update = "UPDATE chapter SET source='{$newStr}' WHERE id = '{$source['id']}'";
            $this->pdo->exec($sql_update);
        }
    }
}