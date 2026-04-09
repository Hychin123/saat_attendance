<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Attendance Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Shift Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., Morning Shift, Night Shift'),
                                
                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('e.g., MS, NS')
                                    ->helperText('Unique code for the shift'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TimePicker::make('start_time')
                                    ->required()
                                    ->seconds(false)
                                    ->label('Start Time'),
                                
                                Forms\Components\TimePicker::make('end_time')
                                    ->required()
                                    ->seconds(false)
                                    ->label('End Time'),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('grace_period_minutes')
                                    ->required()
                                    ->numeric()
                                    ->default(15)
                                    ->minValue(0)
                                    ->maxValue(120)
                                    ->suffix('minutes')
                                    ->helperText('Late tolerance period'),
                                
                                Forms\Components\TextInput::make('minimum_work_hours')
                                    ->required()
                                    ->numeric()
                                    ->default(8)
                                    ->minValue(1)
                                    ->maxValue(24)
                                    ->suffix('hours')
                                    ->helperText('Required work hours'),
                                
                                Forms\Components\ColorPicker::make('color')
                                    ->label('Display Color')
                                    ->helperText('Color for UI display'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Optional description for this shift'),
                    ]),

                Forms\Components\Section::make('Schedule Settings')
                    ->schema([
                        Forms\Components\CheckboxList::make('working_days')
                            ->options([
                                'monday' => 'Monday',
                                'tuesday' => 'Tuesday',
                                'wednesday' => 'Wednesday',
                                'thursday' => 'Thursday',
                                'friday' => 'Friday',
                                'saturday' => 'Saturday',
                                'sunday' => 'Sunday',
                            ])
                            ->columns(3)
                            ->helperText('Select working days for this shift (leave empty for all days)'),
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_overnight')
                                    ->label('Overnight Shift')
                                    ->helperText('Check if shift spans midnight')
                                    ->default(false),
                                
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->helperText('Only active shifts can be assigned'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start')
                    ->time('H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_time')
                    ->label('End')
                    ->time('H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('grace_period_minutes')
                    ->label('Grace Period')
                    ->suffix(' min')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('minimum_work_hours')
                    ->label('Work Hours')
                    ->suffix(' hrs')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_overnight')
                    ->label('Overnight')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Assigned Users')
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Shifts')
                    ->default(true),
                
                Tables\Filters\TernaryFilter::make('is_overnight')
                    ->label('Overnight Shifts'),
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
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'view' => Pages\ViewShift::route('/{record}'),
            'edit' => Pages\EditShift::route('/{record}/edit'),
        ];
    }
}
