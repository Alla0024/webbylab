<?php

class Valid
{
    public static function validateInput($field, $filter = null, $msg)
    {
        if (!empty($field)) {
            if (!empty($filter) && !preg_match($filter, $field)) {
                return $msg;
            }
        }
        return null;
    }
}

