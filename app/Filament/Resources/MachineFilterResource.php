<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MachineFilterResource\Pages;
use App\Filament\Resources\MachineFilterResource\RelationManagers;
use App\Models\MachineFilter;
use App\Models\Machine;
use App\Models\Filter;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Section;

class MachineFilterResource extends Resource
{
    protected static ?string $model = MachineFilter::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Machine Management';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $navigationLabel = 'Machine Filters';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Information')
                    ->schema([
                        Forms\Components\Select::make('machine_id')
                            ->label('Machine')
                            ->options(Machine::all()->pluck('serial_number', 'id'))
                            ->searchable()
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('filter_id')
                            ->label('Filter Type')
                            ->options(Filter::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\DatePicker::make('install_date')
                            ->label('Installation Date')
                            ->required()
                            ->default(now())
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('status')
                            ->options(MachineFilter::getStatuses())
                            ->default(MachineFilter::STATUS_ACTIVE)
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('used_liters')
                            ->label('Used Liters')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->columnSpan(1),
                        
                        Forms\Components\Textarea::make('notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('machine.serial_number')
                    ->label('Machine')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('filter.name')
                    ->label('Filter Type')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('install_date')
                    ->label('Installed')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('days_used')
                    ->label('Days Used')
                    ->getStateUsing(fn (MachineFilter $record) => $record->getDaysUsed())
                    ->badge()
                    ->color(function (MachineFilter $record) {
                        $days = $record->getDaysUsed();
                        $maxDays = $record->filter->max_days;
                        if (!$maxDays) return 'gray';
                        $percentage = ($days / $maxDays) * 100;
                        if ($percentage >= 90) return 'danger';
                        if ($percentage >= 70) return 'warning';
                        return 'success';
                    }),
                
                Tables\Columns\TextColumn::make('used_liters')
                    ->label('Used')
                    ->formatStateUsing(fn ($state, MachineFilter $record) => 
                        number_format($state) . ' / ' . number_format($record->filter->max_liters ?? 0) . ' L'
                    )
                    ->color(function (MachineFilter $record) {
                        if (!$record->filter->max_liters) return 'gray';
                        $percentage = ($record->used_liters / $record->filter->max_liters) * 100;
                        if ($percentage >= 90) return 'danger';
                        if ($percentage >= 70) return 'warning';
                        return 'success';
                    }),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => MachineFilter::STATUS_ACTIVE,
                        'danger' => MachineFilter::STATUS_NEED_CHANGE,
                        'secondary' => MachineFilter::STATUS_CHANGED,
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(MachineFilter::getStatuses()),
                
                Tables\Filters\SelectFilter::make('machine_id')
                    ->label('Machine')
                    ->options(Machine::all()->pluck('serial_number', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('replace')
                    ->label('Replace')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (MachineFilter $record) => $record->status !== MachineFilter::STATUS_CHANGED)
                    ->form([
                        Forms\Components\Select::make('technician_id')
                            ->label('Technician')
                            ->options(User::whereNotNull('name')->pluck('name', 'id'))
                            ->searchable()
                            ->default(auth()->id())
                            ->required(),
                        
                        Forms\Components\Textarea::make('note')
                            ->label('Notes')
                            ->rows(3),
                    ])
                    ->action(function (MachineFilter $record, array $data) {
                        $record->replace($data['technician_id'], $data['note']);
                        
                        Notification::make()
                            ->title('Filter replaced successfully')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMachineFilters::route('/'),
            'create' => Pages\CreateMachineFilter::route('/create'),
            'edit' => Pages\EditMachineFilter::route('/{record}/edit'),
        ];
    }
}
