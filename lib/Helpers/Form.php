<?php

namespace Mikrofraim;

class Form
{
    public static function csrfValidate($token)
    {
        if (!is_string($token) || !is_string(Session::get('csrfTokenPrevious'))) {
            return false;
        }

        if ($token !== Session::get('csrfTokenPrevious')) {
            return false;
        }

        return true;
    }

}
