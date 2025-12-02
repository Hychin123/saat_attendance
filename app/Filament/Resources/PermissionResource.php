<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use App\Filament\Resources\PermissionResource\RelationManagers;
use App\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Permissions';

    protected static ?string $modelLabel = 'Permission';

    protected static ?string $pluralModelLabel = 'Permissions';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('view', 'permissions') 
            || auth()->user()?->isSuperAdmin();
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('create', 'permissions') 
            || auth()->user()?->isSuperAdmin();
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasPermission('edit', 'permissions') 
            || auth()->user()?->isSuperAdmin();
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasPermission('delete', 'permissions') 
            || auth()->user()?->isSuperAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Permission Details')
                    ->schema([
                        Forms\Components\Select::make('name')
                            ->label('Permission Type')
                            ->required()
                            ->options([
                                'view' => 'View',
                                'create' => 'Create',
                                'edit' => 'Edit',
                                'delete' => 'Delete',
                            ])
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $resource = $get('resource');
                                if ($state && $resource) {
                                    $set('display_name', ucfirst($state) . ' ' . ucfirst($resource));
                                }
                            }),

                        Forms\Components\Select::make('resource')
                            ->label('Resource')
                            ->required()
                            ->options([
                                'users' => 'Users',
                                'roles' => 'Roles',
                                'attendances' => 'Attendances',
                                'permissions' => 'Permissions',
                            ])
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $name = $get('name');
                                if ($state && $name) {
                                    $set('display_name', ucfirst($name) . ' ' . ucfirst($state));
                                }
                            }),

                        Forms\Components\TextInput::make('display_name')
                            ->label('Display Name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('e.g., "Create Users" or "Edit Attendances"'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Permission')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'view' => 'info',
                        'create' => 'success',
                        'edit' => 'warning',
                        'delete' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('resource')
                    ->label('Resource')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles_count')
                    ->counts('roles')
                    ->label('Assigned Roles')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}
