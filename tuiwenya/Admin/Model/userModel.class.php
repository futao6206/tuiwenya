<?php

//针对用户表的模型（后台）
namespace Admin\Model;
use \Frame\Libs\baseModel;

final class userModel extends baseModel{
	//属性：保存表名（不带前缀）
	protected $table = 'user';

	public function fetchAll($startrow=0,$pagesize=10,$where='2>1') {
        $sql = "SELECT * FROM {$this->table} ";
        $sql .= "WHERE {$where} ";
        $sql .= "LIMIT {$startrow},{$pagesize}";
        return $this->pdo->fetchAll($sql);
    }

    public function fetchOne($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = {$id}";
        addslashes($sql);
        return $this->pdo->fetchOne($sql);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id={$id}";
        return $this->pdo->exec($sql);
    }

    public function rowCount($where = "2>1") {//默认是真
        $sql = "SELECT * FROM {$this->table} WHERE {$where}";
        return $this->pdo->rowCount($sql);
    }

    public function insert($data) {
        //构建字符名列表 以及值列表
        $fields = "";
        $values = "";
        foreach ($data as $key=>$value) {
            $fields .= "$key,";
            $values .= "'$value',";
        }
        //去除结尾逗号
        $fields = rtrim($fields,",");
        $values = rtrim($values,",");
        $sql = "INSERT INTO {$this->table}($fields) values ($values)";
        return $this->pdo->exec($sql);
    }

    public function updateOne($data,$id) {
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
}