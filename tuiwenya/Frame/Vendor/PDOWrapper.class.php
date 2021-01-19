<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/18
 * Time: 12:25 PM
 * 声明命名空间
 */

namespace Frame\Vendor;
use \PDO;//引入全局PDO类 因为不是在Frame\Vendor这个空间下的类
use \PDOException;

//定义最终的PDOWrapper类
final class PDOWrapper {
    //数据库的配置属性
    private $db_type;
    private $db_host;
    private $db_user;
    private $db_pass;
    private $db_name;
    private $pdo = NULL;//保存PDO对象

    //构造方法
    public function __construct()
    {
        $this->db_type = $GLOBALS['config']['DB_TYPE'];
        $this->db_host = $GLOBALS['config']['DB_HOST'];
        $this->db_user = $GLOBALS['config']['DB_USER'];
        $this->db_pass = $GLOBALS['config']['DB_PASS'];
        $this->db_name = $GLOBALS['config']['DB_NAME'];

        $this->connectDb();//创建PDO对象 连通并选择数据库
        $this->setErrorMode();//设置PDO错误模式
    }

    private function connectDb() {
        try {
            $dsn = "{$this->db_type}:host={$this->db_host};dbname={$this->db_name};charset=utf8";
            $this->pdo = new PDO($dsn,$this->db_user,$this->db_pass);//php<5.3.6 $this->pdo->exec("set names utf8")
        } catch (PDOException $e) {
            $this->showError($e,"创建PDO对象失败！");
        }
    }

    private function setErrorMode() {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    }

    //PDO的自带的获取自增id
    public function fetchId() {
        return $this->pdo->lastInsertId();
    }

    //公共的执行SQL语句方法 insert update delete set等
    public function exec($sql) {
        try {
            return $this->pdo->exec($sql);
        } catch (PDOException $e) {
            $this->showError($e,"SQL语句错误！<br>".$sql);
        }
    }

    //获取单行数据方法 一维数组
    public function fetchOne($sql) {
        try {
            $PDOStatement = $this->pdo->query($sql);//返回结果集
            return $PDOStatement->fetch(PDO::FETCH_ASSOC);//结果集中取出一条数据
        } catch (PDOException $e) {
            $this->showError($e,"SQL语句错误！");
        }
    }

    //获取多行数据 二维数组
    public function fetchAll($sql) {
        try {
            $PDOStatement = $this->pdo->query($sql);//返回结果集
            return $PDOStatement->fetchAll(PDO::FETCH_ASSOC);//结果集中返回二维数组
        } catch (PDOException $e) {
            $this->showError($e,"SQL语句错误！");
        }
    }

    //获取记录数
    public function rowCount($sql) {
        try {
            $PDOStatement = $this->pdo->query($sql);//返回结果集
            return $PDOStatement->rowCount();//结果集中返回记录数
        } catch (PDOException $e) {
            $this->showError($e,"SQL语句错误！");
        }
    }

    private function showError($e,$message) {
//        echo "<h2>$message</h2>";
//        echo "错误状态码：".$e->getCode();
//        echo "<br>错误行号：".$e->getLine();
//        echo "<br>错误文件：".$e->getFile();
//        echo "<br>错误信息：".$e->getMessage();
        echo "服务器异常！";
        die();
    }


}








