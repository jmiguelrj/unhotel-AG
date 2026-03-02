<?php
use Illuminate\Pagination\Paginator;

Paginator::currentPageResolver(function ($pageName = 'page') {
    return $_GET[$pageName] ?? 1;
});