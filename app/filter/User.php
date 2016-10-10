<?php
namespace filter;
class User
{
    public function handle()
    {
        if (!Session::has('user_id')) {
            $url = cookie('url');
            if (!$url) {
                cookie('url', request('get', 'url', __URL__));
            }
            if (IS_AJAX) {
                response('请先登录', 0, '/login');
            } else {
                redirect('/login');
            }
        }
    }
}