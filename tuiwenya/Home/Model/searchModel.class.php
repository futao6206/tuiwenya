<?php
namespace Home\Model;
use \Frame\Libs\baseModel;


final class searchModel extends baseModel
{
    protected $table    = 'novel';   //小说表

    //通过小说名获取一条数据
    public function fetchAllWithName($name) {
        $sql = "SELECT * FROM {$this->table} WHERE title LIKE '%{$name}%'";
        return $this->pdo->fetchAll($sql);
    }

    public function saveKeyword($kw) {
        $sql = "INSERT IGNORE INTO search(kw) VALUES('{$kw}')";
        return $this->pdo->exec($sql);
    }

    public function recommend($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id>={$id} LIMIT 0,8";
        return $this->pdo->fetchAll($sql);
    }
}