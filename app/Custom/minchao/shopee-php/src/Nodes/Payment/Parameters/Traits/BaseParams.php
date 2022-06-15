<?php

namespace Shopee\Nodes\Payment\Parameters\Traits;

trait BaseParams
{
    public function getPaginationOffset()
    {
        return $this->parameters['pagination_offset'];
    }


    public function setPaginationOffset(int $offset)
    {
        $this->parameters['pagination_offset'] = $offset;

        return $this;
    }


    public function getPaginationEntriesPerPage()
    {
        return $this->parameters['pagination_entries_per_page'];
    }


    public function setPaginationEntriesPerPage(int $perPage)
    {
        $this->parameters['pagination_entries_per_page'] = $perPage;

        return $this;
    }
}