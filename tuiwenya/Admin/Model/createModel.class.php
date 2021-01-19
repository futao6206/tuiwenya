<?php

namespace Admin\Model;
use \Frame\Libs\baseModel;

final class createModel extends baseModel {
    protected $table = 'novel';

    public function recommend($id) {
        $sql = "SELECT * FROM novel WHERE id>={$id} LIMIT 0,8";
        return $this->pdo->fetchAll($sql);
    }
}