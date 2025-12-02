<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperAdmin() 
            || auth()->user()?->role?->name === 'HR Manager';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Stock Manager, Sales Representative')
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('department')
                            ->options([
                                'Stock' => 'Stock',
                                'Sales' => 'Sales',
                                'Finance' => 'Finance',
                                'Accounting' => 'Accounting',
                                'Human Resources' => 'Human Resources',
                                'IT' => 'IT',
                                'Marketing' => 'Marketing',
                                'Operations' => 'Operations',
                                'Customer Service' => 'Customer Service',
                                'Management' => 'Management',
                                'Other' => 'Other',
                            ])
                            ->searchable()
                            ->required()
                            ->placeholder('Select a department'),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->placeholder('Describe the responsibilities and duties of this role')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'display_name')
                            ->columns(3)
                            ->gridDirection('row')
                            ->bulkToggleable()
                            ->searchable()
                            ->helperText('Select the permissions this role should have')
                            ->visible(fn() => auth()->user()?->isSuperAdmin()),
                    ])
                    ->visible(fn() => auth()->user()?->isSuperAdmin())
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->size('md')
                    ->color(fn ($record): string => match ($record->department) {
                        'Stock' => 'info',
                        'Sales' => 'success',
                        'Finance' => 'success',
                        'Accounting' => 'warning',
                        'Human Resources' => 'danger',
                        'IT' => 'primary',
                        'Marketing' => 'danger',
                        'Operations' => 'warning',
                        'Customer Service' => 'info',
                        'Management' => 'primary',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('department')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->size('lg')
                    ->icon(fn (string $state): string => match ($state) {
                        'Stock' => 'heroicon-o-cube',
                        'Sales' => 'heroicon-o-chart-bar',
                        'Finance' => 'heroicon-o-banknotes',
                        'Accounting' => 'heroicon-o-calculator',
                        'Human Resources' => 'heroicon-o-users',
                        'IT' => 'heroicon-o-computer-desktop',
                        'Marketing' => 'heroicon-o-megaphone',
                        'Operations' => 'heroicon-o-cog',
                        'Customer Service' => 'heroicon-o-chat-bubble-left-right',
                        'Management' => 'heroicon-o-briefcase',
                        default => 'heroicon-o-tag',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Stock' => 'info',
                        'Sales' => 'success',
                        'Finance' => 'success',
                        'Accounting' => 'warning',
                        'Human Resources' => 'danger',
                        'IT' => 'primary',
                        'Marketing' => 'danger',
                        'Operations' => 'warning',
                        'Customer Service' => 'info',
                        'Management' => 'primary',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Total Users')
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->options([
                        'Stock' => 'Stock',
                        'Sales' => 'Sales',
                        'Finance' => 'Finance',
                        'Accounting' => 'Accounting',
                        'Human Resources' => 'Human Resources',
                        'IT' => 'IT',
                        'Marketing' => 'Marketing',
                        'Operations' => 'Operations',
                        'Customer Service' => 'Customer Service',
                        'Management' => 'Management',
                        'Other' => 'Other',
                    ])
                    ->searchable()
                    ->preload(),
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
            ->defaultGroup('department');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
