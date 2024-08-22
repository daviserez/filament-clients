<?php

namespace App\Filament\Resources\ClientRessourceResource\RelationManagers;

use App\Helpers\Utils;
use App\Models\Appointment;
use App\Models\DetailsAppointment;
use App\Models\Service;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action as ActionsAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class AppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';

    protected static ?string $title = '';

    public function isReadOnly(): bool
    {
        return $this->ownerRecord->trashed();
    }

    public function form(Form $form): Form
    {
        $afterStateUpdatedFunc = function (Set $set, ?string $state) {
            $service = Service::withTrashed()->find($state);

            // Retrieve the last comment for this service on appointment details.
            $lastDetailsAppointment = DetailsAppointment::join(
                'appointments',
                'appointments.id',
                '=',
                'details_appointments.appointment_id'
            )
                ->where('service_id', $service->id ?? null)
                ->where('client_id', $this->ownerRecord->id)
                ->orderBy('details_appointments.id', 'desc')
                ->first();

            $set('price', $lastDetailsAppointment->price ?? $service->price ?? null);
            $set('comment', $lastDetailsAppointment->comment ?? null);
        };

        return $form
            ->schema([
                DateTimePicker::make('appointed_at')
                    ->native(false)
                    ->label(__('client.appointment.appointed_at.label'))
                    ->closeOnDateSelection()
                    ->suffixIcon('heroicon-m-calendar')
                    ->required()
                    ->extraAttributes(['class' => 'justify-self-start'])
                    ->seconds(false)
                    ->default(Carbon::now()->startOfDay()),
                Repeater::make('details')
                    ->relationship()
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        $data['service_price'] = Service::find($data['service_id'])->price;

                        return $data;
                    })
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data) {
                        $data['service_price'] = Service::withTrashed()->find($data['service_id'])->price;

                        return $data;
                    })
                    ->extraAttributes(['class' => 'details-appointments-repeater'])
                    ->label(__('client.appointment.details.label'))
                    ->addActionLabel(__('client.appointment.add_service'))
                    ->required()
                    ->schema([
                        Select::make('service_id')
                            ->allowHtml()
                            ->relationship(
                                name: 'service',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->leftJoinSub(
                                    DB::table('details_appointments')
                                        ->selectRaw('service_id, count(service_id) as details_count')
                                        ->leftjoin(
                                            'appointments',
                                            'appointments.id',
                                            '=',
                                            'details_appointments.appointment_id'
                                        )
                                        ->where('appointments.client_id', $this->ownerRecord->id)
                                        ->groupBy('service_id'),
                                    'count_appointments',
                                    fn (JoinClause $join) => $join->on('services.id', '=', 'count_appointments.service_id')
                                )
                                    ->orderBy('details_count', 'desc')
                                    ->orderBy('name'),
                            )
                            ->getOptionLabelUsing(function ($value): ?string {
                                $service = Service::withTrashed()->find($value);
                                $serviceName = $service?->name;
                                $archived = '';
                                if ($service?->deleted_at !== null) {
                                    $archived = '('.__('global.archived').')';
                                }

                                return implode(' ', [$archived, $serviceName]);
                            })
                            ->searchable()
                            ->preload()
                            ->afterStateUpdated($afterStateUpdatedFunc)
                            ->required()
                            ->hiddenLabel()
                            ->columnSpan([
                                'default' => 1,
                                'md' => 3,
                            ])
                            ->placeholder(__('client.select.service'))
                            ->live(onBlur: true),
                        TextInput::make('comment')
                            ->hiddenLabel()
                            ->autocomplete(false)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 4,
                            ])
                            ->suffixAction(
                                function ($state) {
                                    if (! $state) {
                                        return null;
                                    }

                                    return ActionsAction::make('clear-comment')
                                        ->icon('heroicon-s-x-circle')
                                        ->color('danger')
                                        ->tooltip(__('client.appointment.clear_comment.tooltip'))
                                        ->action(function (Set $set) {
                                            $set('comment', null);
                                        });
                                }
                            )
                            ->placeholder(__('global.comment'))
                            ->live(),
                        TextInput::make('price')
                            ->hiddenLabel()
                            ->numeric()
                            ->placeholder(__('global.price'))
                            ->inputMode('decimal')
                            ->prefix(
                                fn (Get $get) => Service::withTrashed()->find($get('service_id'))?->price
                            )
                            ->extraInputAttributes(['class' => 'text-right'], merge: true)
                            ->autocomplete(false)
                            ->columnSpan([
                                'default' => 1,
                                'md' => 1,
                            ])
                            ->required(),
                    ])
                    ->deletable(true)
                    ->columns([
                        'default' => 1,
                        'md' => 8,
                    ]),
            ])->columns(1);
    }

    public function table(Table $table): Table
    {
        $replicate = function (Appointment $appointment, Action $action): void {
            DB::transaction(function () use ($appointment, $action) {
                $newAppointment = $appointment->replicate();
                $newAppointment->appointed_at = Carbon::now()->startOfDay();
                $newAppointment->save();

                foreach ($appointment->details as $detail) {
                    $newDetail = $detail->replicate();
                    $newDetail->appointment_id = $newAppointment->id;
                    $newDetail->service_price = $detail->service()->withTrashed()->first()->price;
                    $newDetail->save();
                }
                $action->success();
            });
        };

        return $table
            ->emptyStateIcon('heroicon-c-calendar-days')
            ->emptyStateHeading(__('client.appointment.empty.heading'))
            ->emptyStateDescription(
                __(
                    'client.appointment.empty.description',
                    ['fullName' => $this->ownerRecord->fullName]
                ),
            )
            ->recordTitleAttribute('client_id')
            ->recordClasses('details-appointments-row')
            ->recordTitle(fn (Appointment $appointment) => __(
                'client.appointment.title',
                ['date' => Utils::formatDate($appointment->appointed_at)],
            ))
            ->columns([
                Stack::make([
                    ViewColumn::make('id')->view('filament.tables.columns.details'),
                ]),
            ])
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->defaultSort('appointed_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->modalSubmitActionLabel(__('client.appointment.modal.submit.label'))
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->modalHeading(__('client.appointment.create.model.label'))
                    ->modalWidth(MaxWidth::SixExtraLarge)
                    ->label(__('client.appointment.create.label'))
                    ->icon('heroicon-s-calendar')
                    ->createAnother(false)
                    ->slideOver(),
            ])
            ->actions([
                Action::make('clone')
                    ->label('')
                    ->icon(FilamentIcon::resolve('actions::replicate-action') ?? 'heroicon-m-square-2-stack')
                    ->size(ActionSize::Large)
                    ->tooltip(__('client.clone.tooltip'))
                    ->hiddenLabel()
                    ->action($replicate)
                    ->successNotificationTitle(__('client.clone.success'))
                    ->hidden(fn () => $this->isReadOnly()),
                EditAction::make()
                    ->label('')
                    ->size(ActionSize::Large)
                    ->tooltip(__('client.appointment.edit.tooltip'))
                    ->modalWidth(MaxWidth::SixExtraLarge)
                    ->modalFooterActionsAlignment(Alignment::End)
                    ->slideOver(),
                DeleteAction::make()
                    ->size(ActionSize::Large)
                    ->tooltip(__('client.appointment.delete.tooltip'))
                    ->label(''),
            ]);
    }
}
