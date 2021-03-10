<?php

namespace App;

class Table
{

    private $query;
    private $get;
    private $sortable = [];
    private $columns = [];
    private $formatters = [];
    private $limit = 20;

    public function __construct(QueryBuilder $query, array $get)
    {
        $this->query = $query;
        $this->get = $get;
    }

    public function sortable(string ...$sortableFields): self
    {
        $this->sortable = $sortableFields;
        return $this;
    }

    public function columns(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function format(string $key, callable $closure): self
    {
        $this->formatters [$key] = $closure;
        return $this;
    }

    private function td(string $key, array $item): string {
        if (isset($this->formatters[$key])){
            return $this->formatters[$key]($item[$key]);
        }
        return $item[$key];
    }

    private function th(string $field): string {
        if (!in_array($field, $this->sortable)) {
            return $this->columns[$field];
        }
        $sort = $this->get['sort'] ?? null;
        $dir_tri = $this->get['dir'] ?? null;
        $icon = "";
        if ($sort === $field) {
            $icon = ($dir_tri === 'asc') ? '^' : 'v';
        }
        $url = UrlHelper::with_params($this->get, [
            'sort' => $field,
            'dir' => ($dir_tri === 'asc') && ($sort === $field) ? 'desc' : 'asc'
        ]);
        return <<<HTML
        <a href="?$url">{$this->columns[$field]} $icon</a>
        HTML;

    }


    public function render()
    {
        $page = (int) ($this->get['p'] ?? '1');
        $count = $this->query->count();
        if (!empty($this->get['sort']) && in_array($this->get['sort'], $this->sortable)) {
            $this->query->orderBy($this->get['sort'], $this->get['dir'] ?? 'asc');
        }
        $items = $this->query
            ->select(array_keys($this->columns))
            ->limit($this->limit)
            ->page($page)
            ->fetchAll();

        $pages = (int) ceil($count / $this->limit);
?>
<table class="table table-striped">
    <thead>
        <tr>
            <?php foreach($this->columns as $key => $column): ?>
                <td><?= $this->th($key) ?>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item) : ?>
        <tr>
            <?php foreach($this->columns as $key => $column): ?>
                <td><?= $this->td($key, $item) ?>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if (($pages > 1) && $page > 1) : $assoc_tab = ['p' => $page - 1]; ?>
<a href="?<?= UrlHelper::with_params($this->get, $assoc_tab); ?>" class="btn btn-primary">Page précedente</a>
<?php endif; ?>
<?php if (($pages > 1) && $page < $pages) : $assoc_tab = ['p' => $page + 1]; ?>
<a href="?<?= UrlHelper::with_params($this->get, $assoc_tab); ?>" class="btn btn-primary">Page suivante</a>
<?php endif;
    }
}