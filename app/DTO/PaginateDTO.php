<?php

namespace App\DTO;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class PaginateDTO
{
    public int $currentPage;
    public int $perPage;

    public function __construct(Request|FormRequest $formRequest)
    {
        $this->perPage = $formRequest->input('per_page') ?? config('general_settings.default_page');
        $this->currentPage = $formRequest->input('current_page') ?? 1;
    }


    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage(int $currentPage): void
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**s
     * @param int $perPage
     */
    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }

}
