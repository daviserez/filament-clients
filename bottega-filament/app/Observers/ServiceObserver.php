<?php

namespace App\Observers;

use App\Models\Service;

class ServiceObserver
{
    public function creating(Service $service)
    {
        if (auth()->check()) {
            $service->team_id = auth()->user()->team_id;
        }
    }
}
