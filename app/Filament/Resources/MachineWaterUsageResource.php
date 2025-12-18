<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MachineWaterUsageResource\Pages;
use App\Filament\Resources\MachineWaterUsageResource\RelationManagers;
use App\Models\MachineWaterUsage;
use App\Models\Machine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;

class MachineWaterUsageResource extends Resource
{
    protected static ?string $model = MachineWaterUsage::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Machine Management';
    
    protected static ?int $navigationSort = 5;
    
    protected static ?string $navigationLabel = 'Water Usage';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Water Usage Information')
                    ->schema([
                        Forms\Components\Select::make('machine_id')
                            ->label('Machine')
                            ->options(Machine::where('status', Machine::STATUS_ACTIVE)
                                ->pluck('serial_number', 'id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $machine = Machine::find($state);
                                    $set('machine_model', $machine?->model ?? '');
                                    $set('customer_name', $machine?->customer?->name ?? '');
                                }
                            })
                            ->columnSpan(1),
                        
                        Forms\Components\Placeholder::make('machine_model')
                            ->label('Machine Model')
                            ->content(fn ($get) => $get('machine_id') ? Machine::find($get('machine_id'))?->model : '-')
                            ->columnSpan(1),
                        
                        Forms\Components\DatePicker::make('usage_date')
                            ->label('Usage Date')
                            ->default(now())
                            ->required()
                            ->maxDate(now())
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('liters_dispensed')
                            ->label('Liters Dispensed')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->suffix('L')
                            ->helperText('How many liters of water were dispensed')
                            ->columnSpan(1),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Any observations, customer feedback, etc.')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('usage_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('machine.serial_number')
                    ->label('Machine')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('machine.model')
                    ->label('Model')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('machine.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('liters_dispensed')
                    ->label('Liters')
                    ->formatStateUsing(fn ($state) => number_format($state) . ' L')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->formatStateUsing(fn ($state) => number_format($state) . ' L'),
                    ]),
                
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(30)
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recorded')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('machine_id')
                    ->label('Machine')
                    ->options(Machine::pluck('serial_number', 'id'))
                    ->searchable(),
                
                Tables\Filters\Filter::make('usage_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('usage_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('usage_date', '<=', $date),
                            );
                    }),
                
                Tables\Filters\Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query) => $query->whereDate('usage_date', today())),
                
                Tables\Filters\Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn (Builder $query) => $query->whereBetween('usage_date', [now()->startOfWeek(), now()->endOfWeek()])),
                
                Tables\Filters\Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn (Builder $query) => $query->whereMonth('usage_date', now()->month)
                        ->whereYear('usage_date', now()->year)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('usage_date', 'desc');
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
            'index' => Pages\ListMachineWaterUsages::route('/'),
            'create' => Pages\CreateMachineWaterUsage::route('/create'),
            'edit' => Pages\EditMachineWaterUsage::route('/{record}/edit'),
        ];
    }
}
