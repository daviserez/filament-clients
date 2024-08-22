<?php

namespace App\Livewire;

use Filament\Notifications\Notification;
use Livewire\Component;

class DisplaySettings extends Component
{
    public bool $showLatestClients = true;

    public bool $showNextClients = true;

    public bool $showSalesFigures = true;

    public bool $showAppointmentsNumber = true;

    public bool $showMedails = true;

    public bool $showTierlistClients = true;

    /**
     * Time in hours after the appointed time that appointments pass from
     * the next to the previous appointments widget.
     */
    public int $hoursAppointmentsStayInNext = 2;

    public function render()
    {
        return view('livewire.display-settings');
    }

    public function save()
    {
        auth()->user()->update([
            'options->dashboard->show_latest_clients' => $this->showLatestClients,
            'options->dashboard->show_next_clients' => $this->showNextClients,
            'options->dashboard->show_sales_figures' => $this->showSalesFigures,
            'options->dashboard->show_appointments_number' => $this->showAppointmentsNumber,
            'options->dashboard->show_medails' => $this->showMedails,
            'options->dashboard->show_tierlist_clients' => $this->showTierlistClients,
            'options->dashboard->hours_appointments_stay_in_next' => $this->hoursAppointmentsStayInNext,
        ]);

        Notification::make()
            ->title(__('global.save.success'))
            ->success()
            ->send();
    }

    public function mount()
    {
        $this->showLatestClients = auth()->user()->getOption(
            'dashboard',
            'show_latest_clients',
        );

        $this->showNextClients = auth()->user()->getOption(
            'dashboard',
            'show_next_clients',
        );

        $this->showSalesFigures = auth()->user()->getOption(
            'dashboard',
            'show_sales_figures',
        );

        $this->showAppointmentsNumber = auth()->user()->getOption(
            'dashboard',
            'show_appointments_number',
        );

        $this->showMedails = auth()->user()->getOption(
            'dashboard',
            'show_medails',
        );

        $this->showTierlistClients = auth()->user()->getOption(
            'dashboard',
            'show_tierlist_clients',
        );

        $this->hoursAppointmentsStayInNext = auth()->user()->getOption(
            'dashboard',
            'hours_appointments_stay_in_next',
        );
    }
}
