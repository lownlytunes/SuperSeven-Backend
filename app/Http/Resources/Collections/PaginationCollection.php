<?php

namespace App\Http\Resources\Collections;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PaginationCollection extends ResourceCollection
{
    /**
     * Get pagination information
     *
     * @return array<int|string, mixed>
     */
    public function pagination()
    {
        return [
            'links' => [
                /** @var string $previous URL for previous page */
                'previous' => $this->currentPage() > 1 ? $this->url($this->currentPage() - 1) : '',

                /** @var string $next URL for next page */
                'next' => $this->hasMorePages() ? $this->url($this->currentPage() + 1) : '',
            ],
            'meta' => [
                /** @var int $current_page Current page number */
                'current_page' => $this->currentPage(),

                /** @var string $path URL for current page */
                'path' => $this->url($this->currentPage()),

                /** @var int $per_page Number of items to show per page */
                'per_page' => $this->perPage(),

                /** @var int $last_page Last page number */
                'last_page' => $this->lastPage(),

                /** @var bool $has_pages Flag to check if there is still a next page */
                'has_pages' => $this->hasPages(),

                /** @var bool $has_more_pages Flag to check if there are more pages */
                'has_more_pages' => $this->hasMorePages(),

                /** @var int $count Number of items currently shown or displayed */
                'count' => $this->count(),

                /** @var int $total Total number of items */
                'total' => $this->total(),
            ],
        ];
    }
}
