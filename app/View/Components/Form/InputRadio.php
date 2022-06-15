<?php

namespace App\View\Components\Form;

use Illuminate\View\Component;

class InputRadio extends Component
{
    /**
     * Define properties from html attribution
     *
     * @var mixed
     */
    public $id;
    public $name;
    public $value;
    public $checked;
    public $disabled;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $id = null,
        $name = null,
        $value = null,
        $checked = null,
        $disabled = null
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
        $this->checked = $checked;
        $this->disabled = $disabled;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.form.input-radio');
    }
}
