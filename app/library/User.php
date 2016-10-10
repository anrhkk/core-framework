<?php namespace library;

class User
{

    public function changeScore($user_id, $score, $remark)
    {
        $user_id = (int)$user_id;
        $score = (int)$score;
        $log = [];
        $log['user_id'] = $user_id;
        $log['score'] = $score;
        $log['remark'] = $remark;
        $log['created_at'] = NOW;
        $result = DB::insert('user_score', $log);
        if ($result) {
            if ($score > 0) {
                $result = DB::update('user', ['score[+]' => $score], ['id' => $user_id]);
            } else {
                $result = DB::update('user', ['score[-]' => $score], ['id' => $user_id]);
            }
        }
        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}