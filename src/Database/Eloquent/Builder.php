<?php

namespace Stepanenko3\LaravelApiSkeleton\Database\Eloquent;

use Closure;
use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Stepanenko3\LaravelPagination\Pagination;

class Builder extends BaseBuilder
{
    public function paginate(
        $perPage = null,
        $columns = ['*'],
        $pageName = 'page',
        $page = null,
        $total = null,
    ): Pagination {
        $page = $page ?: Pagination::resolveCurrentPage($pageName);

        $perPage = (
            $perPage instanceof Closure
            ? $perPage($total)
            : $perPage
        ) ?: $this->model->getPerPage();

        $total ??= $this->toBase()->getCountForPagination();

        $results = $total
            ? $this->forPage($page, $perPage)->get($columns)
            : $this->model->newCollection();

        return new Pagination(
            items: $results,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => Pagination::resolveCurrentPath(),
                'pageName' => $pageName,
            ],
        );
    }

    public function toRawSql(): string
    {
        return get_query_raw($this);
    }
}
