<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Notifications\Notification;
use Livewire\Component;

class TeamSettingsDeleteMember extends Component
{
    public User $teamMember;

    public function remove()
    {
        $members = auth()->user()->getOption('team', 'members');

        unset($members[array_search($this->teamMember->id, $members)]);

        auth()->user()->update([
            'options->team->members' => $members,
        ]);

        User::find($this->teamMember->id)->update([
            'team_id' => $this->teamMember->id,
        ]);

        $this->dispatch('deleted');

        Notification::make()
            ->title(__('global.save.success'))
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.team-settings-delete-member');
    }
}
