<?php namespace service\page;

class Page
{

    public function show($count, $row = 20)
    {
        $url = __URL__; //读取链接
        $page = $_GET['page'] ? $_GET['page'] : 1; //读取分页坐标
        $total = ceil($count / $row); //分页数
        $url = $_GET['page'] ? str_replace(['?page=' . $_GET['page'], '&page=' . $_GET['page']], '', $url) : $url; //去除page参数
        $url = (strpos($url, '?') !== false) ? $url . '&page=[url]' : $url . '?page=[url]';
        $limit = [($page - 1) * $row, $row]; //limit调用
        $show = '<div class="ui right floated pagination menu"><a class="item">[info]</a>[first] [link] [last]</div>'; //链接坐标模板
        //信息
        $info = $count . config('page.unit') . ' ' . $page . '/' . $total . config('page.page');

        //首页
        $first = ($page != 1) ? '<a class="icon item" href="' . str_replace('[url]', 1, $url) . '"><i class="angle double left icon"></i></a>' : '';

        //尾页
        $last = ($page != $total) ? '<a class="icon item" href="' . str_replace('[url]', $total, $url) . '"><i class="angle double right icon"></i></a>' : '';

        //上一页
        $prev = ($page - 1) < 1 ? '' : $page - 1;
        $prev = $prev ? '<a class="icon item" href="' . str_replace('[url]', $prev, $url) . '"><i class="angle left icon"></i></a>' : $prev;

        //下一页
        $next = ($page + 1) > $total ? '' : $page + 1;
        $next = $next ? '<a class="icon item" href="' . str_replace('[url]', $next, $url) . '"><i class="angle right icon"></i></a>' : $next;

        //链接坐标
        $link = '';
        for ($i = 1; $i < $total + 1; $i++) {
            if ($i == $page) {
                $link .= "<a class='item active blue'>$i</a>";
            } else {
                $link .= "<a class='item' href='" . str_replace('[url]', $i, $url) . "'>$i</a>";
            }
        }
        $link = $prev . $link . $next;
        $link = ($total > 1) ? $link : '';
        $show = str_replace('[info]', $info, $show);
        $show = str_replace('[first]', $first, $show);
        $show = str_replace('[link]', $link, $show);
        $show = str_replace('[last]', $last, $show);

        return ['page' => $show, 'limit' => $limit];
    }
}