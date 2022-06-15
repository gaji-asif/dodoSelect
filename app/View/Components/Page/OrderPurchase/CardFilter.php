<?php

namespace App\View\Components\Page\OrderPurchase;

use Illuminate\View\Component;

class CardFilter extends Component
{
    /**
     * Define properties
     *
     * @var mixed
     */
    public $label;
    public $dataStatus;

    /**
     * Create a new component instance.
     *
     * @param string $label
     * @param string $dataStatus
     * @return void
     */
    public function __construct($label, $dataStatus)
    {
        $this->label = $label;
        $this->dataStatus = $dataStatus;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.page.order-purchase.card-filter');
    }
}
