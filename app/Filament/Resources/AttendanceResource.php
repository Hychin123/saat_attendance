<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Attendance';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('view', 'attendances') 
            || auth()->user()->isSuperAdmin() 
            || auth()->user()->role?->name === 'HR Manager'
            || true; // All users can view their own attendance
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('create', 'attendances') 
            || auth()->user()->isSuperAdmin() 
            || auth()->user()->role?->name === 'HR Manager'
            || true; // All users can create their own attendance
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        
        if ($user->hasPermission('edit', 'attendances') || $user->isSuperAdmin() || $user->role?->name === 'HR Manager') {
            return true;
        }
        
        // Users can edit their own attendance
        return $record->user_id === $user->id;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasPermission('delete', 'attendances') 
            || auth()->user()->isSuperAdmin() 
            || auth()->user()->role?->name === 'HR Manager';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Super admin and HR Manager can see all attendances
        if ($user->isSuperAdmin() || $user->role?->name === 'HR Manager') {
            return $query;
        }

        // Regular users see only their own attendance
        return $query->where('user_id', $user->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn() => auth()->id())
                            ->disabled(fn() => !auth()->user()->isSuperAdmin() && auth()->user()->role?->name !== 'HR Manager'),
                        
                        Forms\Components\Select::make('role_id')
                            ->label('Role')
                            ->relationship('role', 'name')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->default(now())
                            ->native(false),
                        
                        Forms\Components\TimePicker::make('time_in')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\TimePicker::make('time_out'),
                        
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->visible(fn() => auth()->user()->isSuperAdmin() || auth()->user()->role?->name === 'HR Manager'),
                
                Tables\Columns\TextColumn::make('role.name')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('time_in')
                    ->time()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('time_out')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('H:i:s') : 'Not checked out')
                    ->sortable()
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
                
                Tables\Columns\TextColumn::make('work_hours')
                    ->label('Hours Worked')
                    ->getStateUsing(function ($record) {
                        if ($record->time_out) {
                            $diff = $record->time_in->diffInMinutes($record->time_out);
                            $hours = floor($diff / 60);
                            $minutes = $diff % 60;
                            return "{$hours}h {$minutes}m";
                        }
                        return 'In progress';
                    })
                    ->badge()
                    ->color(fn ($state) => $state === 'In progress' ? 'warning' : 'success'),

                Tables\Columns\TextColumn::make('check_in_ip')
                    ->label('Check-in IP')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()->isSuperAdmin() || auth()->user()->role?->name === 'HR Manager'),

                Tables\Columns\TextColumn::make('check_in_latitude')
                    ->label('Check-in Location')
                    ->formatStateUsing(fn ($record) => 
                        $record->check_in_latitude && $record->check_in_longitude 
                            ? "{$record->check_in_latitude}, {$record->check_in_longitude}" 
                            : 'N/A'
                    )
                    ->url(fn ($record) => 
                        $record->check_in_latitude && $record->check_in_longitude 
                            ? "https://www.google.com/maps?q={$record->check_in_latitude},{$record->check_in_longitude}" 
                            : null
                    )
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()->isSuperAdmin() || auth()->user()->role?->name === 'HR Manager'),

                Tables\Columns\TextColumn::make('check_out_ip')
                    ->label('Check-out IP')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()->isSuperAdmin() || auth()->user()->role?->name === 'HR Manager'),

                Tables\Columns\TextColumn::make('check_out_latitude')
                    ->label('Check-out Location')
                    ->formatStateUsing(fn ($record) => 
                        $record->check_out_latitude && $record->check_out_longitude 
                            ? "{$record->check_out_latitude}, {$record->check_out_longitude}" 
                            : 'N/A'
                    )
                    ->url(fn ($record) => 
                        $record->check_out_latitude && $record->check_out_longitude 
                            ? "https://www.google.com/maps?q={$record->check_out_latitude},{$record->check_out_longitude}" 
                            : null
                    )
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => auth()->user()->isSuperAdmin() || auth()->user()->role?->name === 'HR Manager'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn() => auth()->user()->isSuperAdmin() || auth()->user()->role?->name === 'HR Manager'),
                
                Tables\Filters\SelectFilter::make('role')
                    ->relationship('role', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn() => auth()->user()->isSuperAdmin() || auth()->user()->role?->name === 'HR Manager'),
                
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->native(false),
                        Forms\Components\DatePicker::make('date_to')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                
                Tables\Filters\Filter::make('today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('date', today()))
                    ->toggle(),
                
                Tables\Filters\Filter::make('not_checked_out')
                    ->label('Not Checked Out')
                    ->query(fn (Builder $query): Builder => $query->whereNull('time_out'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
