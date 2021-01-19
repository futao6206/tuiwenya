<?php

//文章模型
namespace Admin\Model;
use \Frame\Libs\baseModel;

final class novelModel extends baseModel {//final不能被子类继承
	//属性：保存表名
	protected $table    = 'novel';   //小说表
	protected $table_ch = 'chapter'; //章节表
	protected $table_co = 'content'; //章节内容表

	//获取一条数据
    public function fetchOne($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = {$id}";
        return $this->pdo->fetchOne($sql);
    }

    //通过小说名获取一条数据
    public function fetchOneWithName($name) {
        $sql = "SELECT * FROM {$this->table} WHERE title = '{$name}'";
        return $this->pdo->fetchOne($sql);
    }

    //获取一条数据
    public function fetchOneContent($id) {
        $sql = "SELECT * FROM {$this->table_co} WHERE chapter_id = {$id}";
        return $this->pdo->fetchOne($sql);
    }

    //获取全部数据
    public function fetchAll($startrow=0,$pagesize=10,$where='2>1') {
        $sql = "SELECT * FROM {$this->table} ";
        $sql .= "WHERE {$where} ";
        $sql .= "ORDER BY id DESC ";
        $sql .= "LIMIT {$startrow},{$pagesize}";
        return $this->pdo->fetchAll($sql);
    }

    public function fetchAllChapter($id,$startrow=0,$pagesize=0) {
        $sql = "SELECT * FROM {$this->table_ch} ";
        $sql .= "WHERE {$this->table_ch}.novel_id={$id} ";
        $sql .= "ORDER BY order_index ASC ";
        if ($pagesize!==0) {
            $sql .= "LIMIT {$startrow},{$pagesize}";
        }
        return $this->pdo->fetchAll($sql);
    }

    //连表获取章节以及内容
    public function fetchAllChapterJoinContent($id) {
        $sql = "SELECT {$this->table_ch}.*,{$this->table_co}.content FROM {$this->table_ch} ";
        $sql .= "LEFT JOIN {$this->table_co} ON {$this->table_ch}.id = {$this->table_co}.chapter_id ";
        $sql .= "WHERE {$this->table_ch}.novel_id={$id}";
        return $this->pdo->fetchAll($sql);
    }

    //获取search表数据
    public function fetchAllKeyword() {
        $sql = "SELECT * FROM search";
        return $this->pdo->fetchAll($sql);
    }

    //更新数据
    public function updateOne($id,$data) {
        //构建字符名列表 以及值列表
        $fields = "";
        foreach ($data as $key=>$value) {
            $fields .= "$key='$value',";
        }
        //去除结尾逗号
        $fields = rtrim($fields,",");
        $sql = "UPDATE {$this->table} SET $fields WHERE id = {$id}";
        return $this->pdo->exec($sql);
    }

    //删除一本小说
    public function delete($id) {
        $sql = "DELETE {$this->table},{$this->table_ch},{$this->table_co} FROM {$this->table} ";
        $sql .= "LEFT JOIN {$this->table_ch} ON {$this->table}.id={$this->table_ch}.novel_id ";
        $sql .= "LEFT JOIN {$this->table_co} ON {$this->table}.id={$this->table_co}.novel_id ";
        $sql .= "WHERE {$this->table}.id={$id}";
        return $this->pdo->exec($sql);
    }

    public function delete_search($id) {
        $sql = "DELETE FROM search WHERE id={$id}";
        return$this->pdo->exec($sql);
    }

    public function hide($id,$delete) {
        $sql = "UPDATE {$this->table} SET isDelete={$delete} WHERE id = {$id}";
        return $this->pdo->exec($sql);
    }

    public function rowCount($where = "1=1") {
        $sql = "SELECT id FROM {$this->table} WHERE {$where}";
        return $this->pdo->rowCount($sql);
    }

    public function rowCount_chapter($novel_id) {
        $sql = "SELECT id FROM {$this->table_ch} WHERE novel_id = {$novel_id}";
        return $this->pdo->rowCount($sql);
    }

    //逐行读取 保存小说
    public function saveNovel($filePath,$isUpload=true,$isComplete=true,$flag = true,$chapterReg) {
        set_time_limit(0);// 0 不限时
        //获取值
        $title = $this->getTitle($filePath);
        $content = $this->getTextContent($filePath);
        $author = $this->getAuthor($content);
        //插入novel表
        $sql_novel = "INSERT INTO {$this->table}(title,author,isUpload,isComplete) VALUES('{$title}','{$author}','{$isUpload}','{$isComplete}')";
        $this->pdo->exec($sql_novel);
        //chapter表初始值
        $novel_id = $this->pdo->fetchId();
        $total = 0;//章节数
        $content = '';
        $chapter_id = 0;
        $isStart = false;
        $file = fopen($filePath,"r");
        //逐行检索
        while (true) {
            if (feof($file)) {//文章结尾
                //插入content
                $content = addslashes($content);
                $sql_content = "INSERT INTO content(chapter_id,content) VALUES('{$chapter_id}','{$content}')";
                $this->pdo->exec($sql_content);
                $sql_novel_update = "UPDATE novel set total = '{$total}' WHERE id = '{$novel_id}'";
                $this->pdo->exec($sql_novel_update);
                break;
            } else {
                $now = fgets($file);
                $now = mb_convert_encoding($now,'UTF-8','UTF-8,GBK,GB2312,BIG5');
                //匹配 第*章
                if (strpos($now,'第') !== false && strpos($now,'章') !== false) {
                    $now = preg_replace("/\s|　/","",$now);
                }
                preg_match($chapterReg, $now, $matches);
                $len = mb_strlen($now,'utf8');//标题一般不会超过50个中文字符
                if ($len<50&&!empty($matches[0])) {
                    $isStart = true;//第一章的位置开始记录
                    $chapterTitle = trim($now);//去除两侧空格
                    //删除"第"字前面的字符
                    $count = strpos($chapterTitle,"第");
                    $chapterTitle = substr_replace($chapterTitle,"",0,$count);
                    $total++;
                    //插入chapter
                    $sql_chapter = "INSERT INTO chapter(novel_id,title,order_index) VALUES('{$novel_id}','{$chapterTitle}','{$total}')";
                    $this->pdo->exec($sql_chapter);
                    $chapter_id = $this->pdo->fetchId();
                    //插入content
                    if ($content!='') {
                        $content = addslashes($content);
                        $chapter_id_tmp = $chapter_id-1;//这里不能直接id-1
                        $sql_content = "INSERT INTO content(chapter_id,content) VALUES('{$chapter_id_tmp}','{$content}')";
                        $this->pdo->exec($sql_content);
                        $content = '';
                    }
                } else {
                    if ($isStart && mb_strlen($now)!==2) {
                        $content .= str_replace("\r\n","<br><br>",$now);
                    }
                }
            }
        }
        fclose($file);
        if ($total==0&&$flag) {//获取不到章节 or 文本编码有毛病
            //删除已经保存的部分 然后重新保存至数据库
            if ($this->delete($novel_id)) {
                $this->reSaveNovel($filePath,$isUpload,$isComplete,$chapterReg);
            }
        }
    }

    //匹配所有章节标题
    public function getChapterName($filePath) {
        $text = $this->getTextContent($filePath);
//        $chapterReg = "/(第\s*[0-9]{1,6}\s*[章]\s*.*?)[_,-]?\n/";
//        $chapterReg = "/(第[\u4e00-\u9fa5\u767e\u5343\u96f6]{1,10}章)/";
//        $chapterReg = "/(第[\u96f6\u4e00\u4e8c\u4e09\u56db\u4e94\u516d\u4e03\u516b\u4e5d\u5341\u767e\u5343\u4e07]{1,10}章)/";
        $chapterReg = "/(第[零一二三四五六七八九十百千万]{1,10}章)/";
        preg_match_all($chapterReg, $text, $matches);//第1章
        if (!empty($matches[0])) {
            $chapters = $matches[0];
            foreach ($chapters as $value) {
                echo $value."</br>";
            }
        } else {
            echo "没有匹配数据";
        }
    }

    //重设不正确的章节标题
    public function resetChapterName($filePath) {
        $content = $this->getTextContent($filePath);
        if (file_put_contents($filePath,var_export($content,true)) > 0) {
            $chapterReg = "/(第\s*[0-9]{1,6}\s*[章]\s*.*?)[_,-]?\n/";
            $file = fopen($filePath,"r");
            $chapter = "1";
            while (true) {
                if (feof($file)) {
                    break;
                } else {
                    $now = fgets($file);
                    $now = mb_convert_encoding($now,'UTF-8','UTF-8,GBK,GB2312,BIG5');
                    preg_match($chapterReg, $now, $matches);
                    if (!empty($matches[0])||strpos($now, $chapter)) {
                        echo $chapter.$now."<br>";
                        $chapter++;
                    }
                }
            }
            fclose($file);
        }
    }

    //重新编码 获取文本内容
    public function getTextContent($filePath) {
        $text = file_get_contents($filePath);
        $first2 = substr($text, 0, 2);
        $first3 = substr($text, 0, 3);
        $first4 = substr($text, 0, 3);
        $encodType = mb_detect_encoding($text);
        if ($first3 == UTF8_BOM)
            $encodType = 'UTF-8 BOM';
        else if ($first4 == UTF32_BIG_ENDIAN_BOM)
            $encodType = 'UTF-32BE';
        else if ($first4 == UTF32_LITTLE_ENDIAN_BOM)
            $encodType = 'UTF-32LE';
        else if ($first2 == UTF16_BIG_ENDIAN_BOM)
            $encodType = 'UTF-16BE';
        else if ($first2 == UTF16_LITTLE_ENDIAN_BOM)
            $encodType = 'UTF-16LE';

        //下面的判断主要还是判断ANSI编码的·
        if ($encodType == '') {//即默认创建的txt文本-ANSI编码的
            $content = iconv("GBK", "UTF-8//TRANSLIT//IGNORE", $text);
        } else if ($encodType == 'UTF-8 BOM') {//本来就是UTF-8不用转换
            $content = $text;
        } else {//其他的格式都转化为UTF-8就可以了
            $content = iconv($encodType, "UTF-8", $text);
        }
        return $content;
    }

    //获取标题
    public function getTitle($filePath) {
        setlocale(LC_ALL,"zh_CN.UTF8");
        $title = basename($filePath,".txt");
        //清除文件名中包含的括号以及括号内的内容
        $title = preg_replace('/[（](.*?)[）]|[【](.*?)[】]|[「](.*?)[」]|[『](.*?)[』]|[(](.*?)[)]|[[][\W\w]+[]]|({.*?})/u', "",$title);
        return trim($title);
    }

    //获取作者名字
    private function getAuthor($text) {
        $author = "佚名";
        preg_match("/作者：(.*?)\n/", $text, $matches);
        if (!empty($matches[0])) {
            $author = $matches[0];
            $index = 3;
            $author = mb_substr($author,$index,strlen($author)-$index);
        }
        return trim($author);
    }

    //文本编码有问题 重新保存文本
    public function reSaveNovel($filePath,$isUpload,$isComplete,$chapterReg) {
        $content = $this->getTextContent($filePath);
        if (file_put_contents($filePath,var_export($content,true)) > 0) {
            $this->saveNovel($filePath,$isUpload,$isComplete,false,$chapterReg);
        }
    }

    //获取章节匹配格式
    public function getRules() {
        $filePath = ROOT_PATH."Frame".DS."Rules".DS."rules.txt";
        $string = file_get_contents($filePath);
        return unserialize($string);
    }

    //新增章节匹配格式
    public function addRules($newRule) {
        $filePath = ROOT_PATH."Frame".DS."Rules".DS."rules.txt";
        $newString = file_get_contents($filePath);
        $rules = unserialize($newString);
        $rules[] = $newRule;
        //缓存
        if(false!==fopen($filePath,'w+')){
            file_put_contents($filePath,serialize($rules));//写入缓存
        }
    }

    //重设章节匹配格式
    public function resetRules() {
        $rules = array(//章节匹配格式
            '章','回','卷','节','折','篇','幕','集',
        );
        $filePath = ROOT_PATH."Frame".DS."Rules".DS."rules.txt";
        if(false!==fopen($filePath,'w+')){
            file_put_contents($filePath,serialize($rules));//写入缓存
        }
    }
}