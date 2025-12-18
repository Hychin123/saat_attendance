<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FilterReplacementResource\Pages;
use App\Filament\Resources\FilterReplacementResource\RelationManagers;
use App\Models\FilterReplacement;
use App\Models\MachineFilter;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;

class FilterReplacementResource extends Resource
{
    protected static ?string $model = FilterReplacement::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Machine Management';
    
    protected static ?int $navigationSort = 4;
    
    protected static ?string $navigationLabel = 'Filter Replacements';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Replacement Information')
                    ->schema([
                        Forms\Components\Select::make('machine_filter_id')
                            ->label('Machine Filter')
                            ->options(MachineFilter::with(['machine', 'filter'])
                                ->get()
                                ->mapWithKeys(fn ($mf) => [
                                    $mf->id => "{$mf->machine->serial_number} - {$mf->filter->name}"
                                ]))
                            ->searchable()
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\DatePicker::make('replaced_date')
                            ->label('Replacement Date')
                            ->default(now())
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('replaced_by')
                            ->label('Replaced By (Technician)')
                            ->options(User::whereNotNull('name')->pluck('name', 'id'))
                            ->searchable()
                            ->default(auth()->id())
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('old_used_liters')
                            ->label('Old Used Liters')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('days_used')
                            ->label('Days Used')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                        
                        Forms\Components\Textarea::make('note')
                            ->label('Notes / Reason for Replacement')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('replaced_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('machineFilter.machine.serial_number')
                    ->label('Machine')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('machineFilter.filter.name')
                    ->label('Filter Type')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('machineFilter.filter.position')
                    ->label('Position')
                    ->badge(),
                
                Tables\Columns\TextColumn::make('old_used_liters')
                    ->label('Used Liters')
                    ->formatStateUsing(fn ($state) => number_format($state) . ' L')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('days_used')
                    ->label('Days Used')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('technician.name')
                    ->label('Replaced By')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('note')
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
                Tables\Filters\Filter::make('replaced_date')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('replaced_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('replaced_date', '<=', $date),
                            );
                    }),
                
                Tables\Filters\SelectFilter::make('replaced_by')
                    ->label('Technician')
                    ->options(User::whereNotNull('name')->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('replaced_date', 'desc');
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
            'index' => Pages\ListFilterReplacements::route('/'),
            'create' => Pages\CreateFilterReplacement::route('/create'),
            'edit' => Pages\EditFilterReplacement::route('/{record}/edit'),
        ];
    }
}
