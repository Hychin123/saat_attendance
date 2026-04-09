<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FilterResource\Pages;
use App\Filament\Resources\FilterResource\RelationManagers;
use App\Models\Filter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FilterResource extends Resource
{
    protected static ?string $model = Filter::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationGroup = 'Machine Management';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filter Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('code')
                            ->default(fn() => Filter::generateCode())
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('position')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(7)
                            ->helperText('Position in the 7-filter system (1-7)')
                            ->columnSpan(1),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpan(1),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Replacement Criteria')
                    ->schema([
                        Forms\Components\TextInput::make('max_liters')
                            ->label('Max Liters')
                            ->numeric()
                            ->nullable()
                            ->helperText('Maximum liters before replacement (e.g., 10000)')
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('max_days')
                            ->label('Max Days')
                            ->numeric()
                            ->nullable()
                            ->helperText('Maximum days before replacement (e.g., 180)')
                            ->columnSpan(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('position')
                    ->sortable()
                    ->badge(),
                
                Tables\Columns\TextColumn::make('max_liters')
                    ->label('Max Liters')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state) . ' L' : '-')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('max_days')
                    ->label('Max Days')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' days' : '-')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('machineFilters_count')
                    ->label('Installations')
                    ->counts('machineFilters')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('position', 'asc');
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
            'index' => Pages\ListFilters::route('/'),
            'create' => Pages\CreateFilter::route('/create'),
            'edit' => Pages\EditFilter::route('/{record}/edit'),
        ];
    }
}
