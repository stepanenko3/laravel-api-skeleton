<?php

namespace Stepanenko3\LaravelLogicContainers\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Stepanenko3\LaravelPagination\Pagination;

class Builder extends BaseBuilder
{
    public function paginate(
        $perPage = null,
        $columns = ['*'],
        $pageName = 'page',
        $page = null
    ): Pagination {
        $page = $page ?: Pagination::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->model->getPerPage();
        $results = ($total = $this->toBase()->getCountForPagination())
            ? $this->forPage($page, $perPage)->get($columns)
            : $this->model->newCollection();

        return new Pagination($results, $total, $perPage, $page, [
            'path' => Pagination::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    public function toRawSql()
    {
        return get_query_raw($this);
    }
}
