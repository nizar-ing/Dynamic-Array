<?php

namespace App;

class UrlHelper
{

    public static function with_params(array $data, array $assoc_tab): string
    {
        return http_build_query(array_merge($data, $assoc_tab));
    }
}
