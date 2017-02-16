<?php

class base_static_lang  
{

    static function is_need_conv() {
        if (defined('LANG')) {
            return true;
        }
        return false;
    }

    static public function _($lang_dir, $key, $args=null)
    {
        if (self::is_need_conv())
        {
            $language = kernel::get_lang();
            putenv("LANG=$language");
            setlocale(LC_ALL, $language);

            $domain = 'lang';
            bindtextdomain($domain, $lang_dir);
            textdomain($domain);
            bind_textdomain_codeset($domain, 'utf-8');
            return gettext($key);
        }
        else
        {
            return $key;
        }
    }
}//End Class
