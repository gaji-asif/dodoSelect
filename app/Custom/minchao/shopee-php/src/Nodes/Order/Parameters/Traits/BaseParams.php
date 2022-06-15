<?php

namespace Shopee\Nodes\Order\Parameters\Traits;

trait BaseParams
{
    /**
     * Get `pagination_offset` parameters
     *
     * @return int
     */
    public function getPaginationOffset()
    {
        return $this->parameters['pagination_offset'];
    }

    /**
     * Set `pagination_offset` parameters
     *
     * @param  int  $offset
     * @return self
     */
    public function setPaginationOffset(int $offset)
    {
        $this->parameters['pagination_offset'] = $offset;

        return $this;
    }

    /**
     * Get `pagination_entries_per_page` parameters
     *
     * @return int
     */
    public function getPaginationEntriesPerPage()
    {
        return $this->parameters['pagination_entries_per_page'];
    }

    /**
     * Set `pagination_entries_per_page` parameters
     *
     * @param  int  $perPage
     * @return self
     */
    public function setPaginationEntriesPerPage(int $perPage)
    {
        $this->parameters['pagination_entries_per_page'] = $perPage;

        return $this;
    }
}