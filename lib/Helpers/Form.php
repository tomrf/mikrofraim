<?php

namespace Mikrofraim\Helpers;

class Form
{
    /**
     * Validate CSRF token
     * @param  string $token
     * @return boolean
     */
    public function csrfValidate(string $token): bool
    {
        if ($token !== \Session::get('csrfTokenPrevious')) {
            return false;
        }

        return true;
    }
}
