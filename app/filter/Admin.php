<?php
namespace filter;
class Admin
{
    public function handle()
    {
        if (!Session::has('user_id')) {
            redirect('/sign-in');
        }
    }
}