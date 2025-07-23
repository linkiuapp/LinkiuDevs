<?php

namespace App\Shared\Views\Components;

use Illuminate\View\Component;
use App\Shared\Models\Store;

class TenantAdminLayout extends Component
{
    public Store $store;
    
    /**
     * Create a new component instance.
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('shared::layouts.tenant-admin');
    }
} 