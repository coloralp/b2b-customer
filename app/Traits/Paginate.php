<?php

namespace App\Traits;

trait Paginate
{
    public function getPerPage(): int
    {
        return $this->input('per_page') ?? config('general_settings.default_page');
    }

    public function getCurrentPage(): int
    {
        return $this->input('current_page') ?? 1;
    }
}
