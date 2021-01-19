<?php
namespace Home\Model;
use \Frame\Libs\baseModel;


final class chapterModel extends baseModel
{
    protected $table = 'novel';   //小说表
    protected $table_ch = 'chapter'; //章节表

    //获取指定数据
    public function fetchChapter($startrow=0,$pagesize=10,$id) {
        $sql = "SELECT * FROM {$this->table_ch} ";
        $sql .= "WHERE {$this->table_ch}.novel_id={$id} ";
        $sql .= "ORDER BY order_index ASC ";
        $sql .= "LIMIT {$startrow},{$pagesize} ";
        return $this->pdo->fetchAll($sql);
    }

    public function fetchOneNovel($id)
    {
        $sql = "SELECT * FROM {$this->table} ";
        $sql .= "WHERE {$this->table}.id={$id}";
        return $this->pdo->fetchOne($sql);
    }

    public function updateRead($id,$readCount) {
        $sql = "UPDATE {$this->table} SET readCount={$readCount}+1 WHERE id = {$id}";
        return $this->pdo->exec($sql);
    }

    public function rowCount($id) {
        $sql = "SELECT id FROM {$this->table_ch} WHERE {$this->table_ch}.novel_id={$id}";
        return $this->pdo->rowCount($sql);
    }
}