<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('view', 'users') 
            || auth()->user()->isSuperAdmin() 
            || auth()->user()->role?->name === 'HR Manager';
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('create', 'users') 
            || auth()->user()->isSuperAdmin() 
            || auth()->user()->role?->name === 'HR Manager';
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasPermission('edit', 'users') 
            || auth()->user()->isSuperAdmin() 
            || auth()->user()->role?->name === 'HR Manager'
            || auth()->id() === $record->id;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasPermission('delete', 'users') 
            || auth()->user()->isSuperAdmin();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Super admin and HR Manager can see all users
        if ($user->isSuperAdmin() || $user->role?->name === 'HR Manager') {
            return $query;
        }

        // Regular users see only their own profile
        return $query->where('id', $user->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\FileUpload::make('profile_image')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->circleCropper()
                            ->maxSize(2048)
                            ->disk('public')
                            ->directory('profile-images')
                            ->visibility('public')
                            ->downloadable()
                            ->fetchFileInformation(false)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(table: 'users', column: 'email', ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'This email address is already registered.',
                            ])
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->unique(table: 'users', column: 'phone', ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'This phone number is already registered.',
                            ])
                            ->maxLength(255),

                        Forms\Components\TextInput::make('age')
                            ->numeric()
                            ->required()
                            ->minValue(18)
                            ->maxValue(100),

                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ])
                            ->native(false)
                            ->required(),

                        Forms\Components\TextInput::make('school')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Telegram Notifications')
                    ->schema([
                        Forms\Components\TextInput::make('telegram_chat_id')
                            ->label('Telegram Chat ID')
                            ->maxLength(255)
                            ->helperText('Get your Chat ID by messaging @userinfobot on Telegram'),
                        
                        Forms\Components\Toggle::make('telegram_notifications')
                            ->label('Enable Telegram Notifications')
                            ->helperText('Receive attendance alerts via Telegram')
                            ->default(false),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Forms\Components\Section::make('Work Information')
                    ->schema([
                        Forms\Components\Select::make('role_id')
                            ->label('Role')
                            ->required()
                            ->relationship('role', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(fn(?User $record) => !auth()->user()->isSuperAdmin() && !auth()->user()->role?->name === 'HR Manager')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('department')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->rows(3),
                            ]),

                        Forms\Components\TextInput::make('salary')
                            ->numeric()
                            ->prefix('$')
                            ->maxValue(999999.99)
                            ->disabled(fn() => !auth()->user()->isSuperAdmin() && !auth()->user()->role?->name === 'HR Manager'),

                        Forms\Components\TextInput::make('kpa')
                            ->label('KPA (Key Performance Area)')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Shift Assignment')
                    ->schema([
                        Forms\Components\Repeater::make('shifts')
                            ->relationship('shifts')
                            ->schema([
                                Forms\Components\Select::make('shift_id')
                                    ->label('Shift')
                                    ->options(\App\Models\Shift::active()->pluck('name', 'id'))
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        if (!$record) return null;
                                        return "{$record->name} ({$record->code}) - {$record->start_time} to {$record->end_time}";
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->native(false),
                                
                                Forms\Components\DatePicker::make('effective_from')
                                    ->label('Effective From')
                                    ->required()
                                    ->default(now())
                                    ->native(false),
                                
                                Forms\Components\DatePicker::make('effective_to')
                                    ->label('Effective To')
                                    ->helperText('Leave empty for ongoing assignment')
                                    ->native(false),
                                
                                Forms\Components\Toggle::make('is_primary')
                                    ->label('Primary Shift')
                                    ->default(true)
                                    ->helperText('User\'s main shift for attendance'),
                            ])
                            ->columns(4)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['shift_id']) ? \App\Models\Shift::find($state['shift_id'])?->name : 'New Shift Assignment'
                            )
                            ->defaultItems(0)
                            ->addActionLabel('Add Shift Assignment')
                            ->saveRelationshipsUsing(function ($component, $state, $record) {
                                if (!$record || !$state) {
                                    return;
                                }
                                
                                // Detach existing shifts first
                                $record->shifts()->detach();
                                
                                // Attach new shifts with pivot data
                                foreach ($state as $item) {
                                    if (isset($item['shift_id'])) {
                                        $record->shifts()->attach($item['shift_id'], [
                                            'effective_from' => $item['effective_from'] ?? now(),
                                            'effective_to' => $item['effective_to'] ?? null,
                                            'is_primary' => $item['is_primary'] ?? true,
                                        ]);
                                    }
                                }
                            })
                            ->dehydrated(false),
                    ])
                    ->collapsed()
                    ->visible(fn() => auth()->user()->isSuperAdmin() || auth()->user()->role?->name === 'HR Manager'),

                Forms\Components\Section::make('Security')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_super_admin')
                            ->label('Super Admin')
                            ->helperText('Grant super admin privileges to this user')
                            ->visible(fn() => auth()->user()?->isSuperAdmin())
                            ->disabled(fn(?User $record) => $record?->id === auth()->id()),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_image')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role.name')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_super_admin')
                    ->label('Super Admin')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('age')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('gender')
                    ->badge()
                    ->colors([
                        'primary' => 'male',
                        'danger' => 'female',
                        'warning' => 'other',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('salary')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->relationship('role', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(fn () => 'users-' . date('Y-m-d'))
                            ->withWriterType(\Maatwebsite\Excel\Excel::XLSX),
                        ExcelExport::make('pdf')
                            ->fromTable()
                            ->withFilename(fn () => 'users-' . date('Y-m-d') . '.pdf')
                            ->withWriterType(\Maatwebsite\Excel\Excel::DOMPDF),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename(fn () => 'users-' . date('Y-m-d'))
                                ->withWriterType(\Maatwebsite\Excel\Excel::XLSX),
                        ]),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
