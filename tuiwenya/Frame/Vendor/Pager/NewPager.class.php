<?php

//分页工具：不带数据
namespace Frame\Vendor\Pager;


final class NewPager{

	//生成分页字符串
	public static function clickPage($counts,$pagecount = 5,$page = 1,$cond = array()){
		//计算页码
		$pages = ceil($counts / $pagecount);
		$prev = $page > 1 ? $page - 1 : 1;
		$next = $page < $pages ? $page + 1 : $pages;

		//组织条件：额外条件
		$pathinfo = '';
		foreach ($cond as $key => $value) {
			# code...
			$pathinfo .= $key . '=' . $value . '&';
		}

        //首页
        $click = "<li><a href='?{$pathinfo}page=1'>首页</a></li>";
		//组织上一页功能
		$click .= "<li><a href='?{$pathinfo}page={$prev}'>上一页</a></li>";

		//页码点击判定
        $show_max_pages = 18;
        $center_page = 8;
		if($pages <= $show_max_pages){
			//有多少页点多少页
			for($i = 1;$i <= $pages;$i++){
				$click .= "<li><a href='?{$pathinfo}page={$i}'>{$i}</a></li>";
			}
		}else{//$pages大于$show_max_pages页 并且没有超过$pages
            //页码大于$show_max_pages页
            if ($page >= $center_page + 1) {
                if ($page + $center_page < $pages) {
                    $click .= "<li><a href='#'>...</a></li>";
                    for($i = $page - 7;$i <= $page+7 ;$i++){
                        $click .= "<li><a href='?{$pathinfo}page={$i}'>{$i}</a></li>";
                    }
                    $click .= "<li><a href='#'>...</a></li>";
                } else {
                    $click .= "<li><a href='#'>...</a></li>";
                    for($i = $pages - 14;$i <= $pages ;$i++){
                        $click .= "<li><a href='?{$pathinfo}page={$i}'>{$i}</a></li>";
                    }
                }
            } else {
                for($i = 1;$i <= $show_max_pages;$i++){
                    $click .= "<li><a href='?{$pathinfo}page={$i}'>{$i}</a></li>";
                }
                $click .= "<li><a href='#'>...</a></li>";
            }
		}

		//补充下一页
		$click .= "<li><a href='?{$pathinfo}page={$next}'>下一页</a></li>";
		//尾页
		$click .= "<li><a href='?{$pathinfo}page={$pages}'>尾页</a></li>";

		//返回给调用处
		return $click;
	}
}