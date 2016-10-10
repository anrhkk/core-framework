<?php
namespace hook;
class CsrfHook
{
    public function run(&$content)
    {
        if (config('security.csrf_protection')) {
            //表单添加令牌
            if (preg_match_all('/<form.*?>(.*?)<\/form>/is', $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $id => $m) {
                    if (strpos($m[0], 'no-ajax') === FALSE) {
                        $php = "<input type='hidden' name='" . Security::getCsrfTokenName() . "' value='" . Security::getCsrfHash() . "'>";
                        $content = str_replace($m[1], $m[1] . $php, $content);
                        Security::csrfSetCookie();
                    }
                }
            }
        }
    }
}