<?php

namespace App\Services;

class SalesService extends ApiService
{
    /**
     * Get sales statistics
     */
    public function getStats(): array
    {
        return $this->get('admin/sales');
    }
}
