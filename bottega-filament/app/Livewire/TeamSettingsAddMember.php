<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Notifications\Notification;
use InvalidArgumentException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class TeamSettingsAddMember extends Component
{
    #[Validate('required|email|exists:users,email')]
    public ?string $newTeamMember;

    public function render()
    {
        return view('livewire.team-settings-add-member');
    }

    public function addTeamMember()
    {
        $this->validate();

        $user = User::where('email', $this->newTeamMember)->first();

        // TODO use policy / validation instead.
        if ($user->id === auth()->id()) {
            throw new InvalidArgumentException('You cannot add yourself to your team.');
        }

        $members = auth()->user()->getOption('team', 'members');

        // TODO use validation instead.
        if (array_search($user->id, $members) !== false) {
            throw new InvalidArgumentException('User is already a member of the team.');
        }

        array_push($members, $user->id);

        auth()->user()->update([
            'options->team->members' => $members,
        ]);

        $this->reset();

        $this->dispatch('added');

        Notification::make()
            ->title(__('global.save.success'))
            ->success()
            ->send();
    }
}
