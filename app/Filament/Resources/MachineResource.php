<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MachineResource\Pages;
use App\Filament\Resources\MachineResource\RelationManagers;
use App\Models\Machine;
use App\Models\Sale;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;

class MachineResource extends Resource
{
    protected static ?string $model = Machine::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Machine Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Machine Information')
                    ->schema([
                        Forms\Components\TextInput::make('serial_number')
                            ->label('Serial Number')
                            ->default(fn() => Machine::generateSerialNumber())
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('model')
                            ->label('Model')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('sale_id')
                            ->label('Sale')
                            ->options(Sale::pluck('sale_id', 'sale_id'))
                            ->searchable()
                            ->nullable()
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(User::whereNotNull('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\DatePicker::make('install_date')
                            ->label('Installation Date')
                            ->default(now())
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('status')
                            ->options(Machine::getStatuses())
                            ->default(Machine::STATUS_ACTIVE)
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Serial Number')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('sale_id')
                    ->label('Sale ID')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('install_date')
                    ->label('Install Date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => Machine::STATUS_ACTIVE,
                        'warning' => Machine::STATUS_MAINTENANCE,
                        'danger' => Machine::STATUS_INACTIVE,
                        'secondary' => Machine::STATUS_DECOMMISSIONED,
                    ]),
                
                Tables\Columns\TextColumn::make('machineFilters_count')
                    ->label('Active Filters')
                    ->counts([
                        'machineFilters' => fn (Builder $query) => $query->where('status', 'active')
                    ])
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('filters_needing_change')
                    ->label('Need Change')
                    ->getStateUsing(fn (Machine $record) => $record->getFiltersNeedingChange())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Machine::getStatuses()),
                
                Tables\Filters\SelectFilter::make('sale_id')
                    ->label('Sale')
                    ->options(Sale::pluck('sale_id', 'sale_id'))
                    ->searchable(),
                
                Tables\Filters\Filter::make('needs_maintenance')
                    ->label('Needs Maintenance')
                    ->query(fn (Builder $query) => $query->needsMaintenance()),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('add_usage')
                    ->label('Add Water Usage')
                    ->icon('heroicon-o-beaker')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('liters')
                            ->label('Liters Dispensed')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2),
                    ])
                    ->action(function (Machine $record, array $data) {
                        $record->addWaterUsage($data['liters'], $data['notes'] ?? null);
                        
                        Notification::make()
                            ->title('Water usage recorded')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers will be added after they are created
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMachines::route('/'),
            'create' => Pages\CreateMachine::route('/create'),
            'edit' => Pages\EditMachine::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
