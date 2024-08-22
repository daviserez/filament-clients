<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TeamSettings extends Component
{
    #[Computed()]
    public function teamMembers()
    {
        return User::whereIn('id', auth()->user()->getOption('team', 'members'))->get();
    }

    public function render()
    {
        return view('livewire.team-settings');
    }
}
