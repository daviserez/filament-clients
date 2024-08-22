<?php

namespace App\Livewire;

use Filament\Notifications\Notification;
use InvalidArgumentException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class TeamSelect extends Component
{
    #[Validate('required|exists:users,id')]
    public ?int $selectedTeamId;

    public function render()
    {
        return view('livewire.team-select');
    }

    public function mount()
    {
        $this->selectedTeamId = auth()->user()->team_id;
    }

    public function changeTeam()
    {
        $this->validate();

        // TODO use policy instead.
        if ($this->selectedTeamId !== auth()->user()->id && ! in_array($this->selectedTeamId, array_keys(auth()->user()->getTeamsAsMember()))) {
            throw new InvalidArgumentException('You cannot change to a team you are not a member of.');
        }

        auth()->user()->update([
            'team_id' => $this->selectedTeamId,
        ]);

        Notification::make()
            ->title(__('global.save.success'))
            ->success()
            ->send();
    }
}
