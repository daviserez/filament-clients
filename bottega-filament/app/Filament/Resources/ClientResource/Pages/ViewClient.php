<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Filament\Resources\ClientResource\Widgets\ClientOverview;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected static string $view = 'filament.pages.view-client';

    public function getTitle(): string|Htmlable
    {
        return $this->getRecordTitle();
    }

    public function getHeading(): string|Htmlable
    {
        $firstname = $this->record->firstname[0] ?? '';

        return new HtmlString(<<<HTML
        <div class="flex items-center gap-10 sm:mt-10">
            <div class="flex h-24 w-24 text-4xl items-center justify-center rounded-full uppercase text-white"
                style="background-color: {$this->record->avatar_color}">
                <div>$firstname{$this->record->name[0]}</div>
            </div>
            <div>{$this->getRecordTitle()}</div>
        </div>
        HTML);
    }

    protected function getHeaderActions(): array
    {

        $force_delete_1 = __('client.view.action.delete.modal.force_delete_1');
        $force_delete_2 = __('client.view.action.delete.modal.force_delete_2');
        $force_delete_3 = __('client.view.action.delete.modal.force_delete_3');

        return [
            ClientResource::actionEdit(Action::make('edit_client')),
            ClientResource::actionArchive(DeleteAction::make()),
            ForceDeleteAction::make()
                ->icon('heroicon-s-trash')
                ->successNotificationTitle(__('client.view.action.force_delete.modal.notification.success'))
                ->modalDescription(
                    new HtmlString(
                        <<<HTML
                            <div class="text-sm text-slate-400">
                                <div>$force_delete_1</div>
                                <div class="my-2">$force_delete_2</div>
                                <div>$force_delete_3</div>
                            </div>
                        HTML
                    )
                ),
            RestoreAction::make()
                ->color('warning')
                ->icon('heroicon-s-arrow-uturn-left')
                ->successRedirectUrl(
                    route(
                        'filament.admin.resources.clients.view',
                        ['record' => $this->record->id]
                    ),
                ),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Split::make([
                    Section::make(__('client.view.infos.header'))
                        ->schema([

                            TextEntry::make('fullName')
                                ->hiddenLabel()
                                ->icon('heroicon-s-user'),

                            TextEntry::make('email')
                                ->hiddenLabel()
                                ->icon('heroicon-s-envelope')
                                ->hidden(empty($this->record->email)),

                            TextEntry::make('fullphone')
                                ->hiddenLabel()
                                ->icon('heroicon-s-phone')
                                ->hidden(empty($this->record->fullphone))
                                ->html(),

                            TextEntry::make('fulladress')
                                ->hiddenLabel()
                                ->icon('heroicon-s-map-pin')
                                ->hidden(empty($this->record->fulladress))
                                ->html(),

                            TextEntry::make('created_at')
                                ->hiddenLabel()
                                ->date()
                                ->icon('heroicon-m-user-plus')
                                ->tooltip(__('client.view.created_at.tootltip')),

                            TextEntry::make('lastAppointment')
                                ->hiddenLabel()
                                ->date()
                                ->icon('heroicon-c-calendar-days')
                                ->placeholder(__('client.appointment.never_came'))
                                ->tooltip(__('client.view.latest_appointment.tootltip')),
                        ])
                        ->grow(false),

                    Grid::make(['default' => 1])
                        ->schema([
                            Section::make(__('client.view.notes'))
                                ->schema([
                                    TextEntry::make('notes')
                                        ->hiddenLabel(true)
                                        ->default(__('client.view.notes.empty'))
                                        ->extraAttributes(['class' => 'reset-tailwinds'])
                                        ->html(),
                                ]),
                            Livewire::make(ClientOverview::class),
                        ]),
                ])->from('lg'),

            ])->columns(1);
    }

    protected function getWidgets(): array
    {
        return [
            ClientResource\Widgets\ClientOverview::class,
        ];
    }
}
