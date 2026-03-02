<?php

namespace JFB\SelectAutocomplete\Vendor\Auryn;

interface ReflectionCache
{
    public function fetch($key);
    public function store($key, $data);
}
