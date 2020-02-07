<?php


namespace Source\Models;

use Source\Dengo\Builder;

class Podcast extends Builder
{
    public function __construct()
    {
        parent::__construct('podcasts', ['id'], ['title', 's']);
    }

    public function create(array $attributes): ?int
    {
        return parent::create($attributes);
    }
}