<?php

namespace App;

class TableHelper
{

    public static function sort_field(string $field_name, string $field_label, array $data): string
    {
        $sort = $data['sort'] ?? null;
        $dir_tri = $data['dir'] ?? null;
        $icon = "";
        if ($sort === $field_name) {
            $icon = ($dir_tri === 'asc') ? '^' : 'v';
        }
        $url = UrlHelper::with_params($data, [
            'sort' => $field_name,
            'dir' => ($dir_tri === 'asc') && ($sort === $field_name) ? 'desc' : 'asc'
        ]);
        return <<<HTML
        <a href="?$url">$field_label $icon</a>
        HTML;
    }
}