<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmithStockIssueResource\Pages;
use App\Models\SmithStockIssue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SmithStockIssueResource extends Resource
{
    protected static ?string $model = SmithStockIssue::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static ?string $navigationGroup = 'Smith Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Stock Issues';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Issue Information')
                    ->schema([
                        Forms\Components\TextInput::make('reference_no')
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated'),
                        Forms\Components\Select::make('user_id')
                            ->label('Smith')
                            ->relationship('user', 'name', function (Builder $query) {
                                $query->whereHas('role', function ($q) {
                                    $q->where('name', 'Smith');
                                });
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn() => auth()->user()->role?->name === 'Smith' ? auth()->id() : null),
                        Forms\Components\Select::make('warehouse_id')
                            ->relationship('warehouse', 'warehouse_name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('issue_date')
                            ->required()
                            ->default(now()),
                    ])->columns(2),
                Forms\Components\Section::make('Material Details')
                    ->schema([
                        Forms\Components\Select::make('item_id')
                            ->label('Item')
                            ->relationship('item', 'item_name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01),
                        Forms\Components\TextInput::make('project_name')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('purpose')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(3),
                Forms\Components\Section::make('Issue Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'issued' => 'Issued',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required()
                            ->disabled(fn() => !auth()->user()->can('approve', SmithStockIssue::class)),
                        Forms\Components\Select::make('issued_by')
                            ->relationship('issuer', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('issued_at')
                            ->disabled(),
                    ])->columns(3)->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_no')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Smith')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.warehouse_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item.item_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('issue_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project_name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'issued',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('issuer.name')
                    ->label('Issued By')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'issued' => 'Issued',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Smith')
                    ->relationship('user', 'name'),
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'warehouse_name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListSmithStockIssues::route('/'),
            'create' => Pages\CreateSmithStockIssue::route('/create'),
            'view' => Pages\ViewSmithStockIssue::route('/{record}'),
            'edit' => Pages\EditSmithStockIssue::route('/{record}/edit'),
        ];
    }
}
