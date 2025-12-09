<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommissionResource\Pages;
use App\Models\Commission;
use App\Models\Sale;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\SelectFilter;

class CommissionResource extends Resource
{
    protected static ?string $model = Commission::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static ?string $navigationGroup = 'Sales Management';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Commission Information')
                    ->schema([
                        Forms\Components\Select::make('sale_id')
                            ->label('Sale')
                            ->options(Sale::all()->pluck('sale_id', 'sale_id'))
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $sale = Sale::where('sale_id', $state)->first();
                                    if ($sale) {
                                        $set('agent_id', $sale->agent_id);
                                        $set('total_sale_amount', $sale->net_total);
                                        $set('commission_amount', $sale->net_total * 0.05);
                                    }
                                }
                            })
                            ->columnSpan(2),
                        
                        Forms\Components\Select::make('agent_id')
                            ->label('Agent')
                            ->options(User::whereNotNull('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('total_sale_amount')
                            ->label('Total Sale Amount')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('commission_rate')
                            ->label('Commission Rate (%)')
                            ->numeric()
                            ->default(5.00)
                            ->suffix('%')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $get, Forms\Set $set) {
                                $total = $get('total_sale_amount') ?? 0;
                                $set('commission_amount', ($total * $state) / 100);
                            })
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('commission_amount')
                            ->label('Commission Amount')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('status')
                            ->options(Commission::getStatuses())
                            ->default(Commission::STATUS_PENDING)
                            ->required()
                            ->reactive()
                            ->columnSpan(1),
                        
                        Forms\Components\DatePicker::make('paid_date')
                            ->label('Paid Date')
                            ->visible(fn($get) => $get('status') === Commission::STATUS_PAID)
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('payment_reference')
                            ->label('Payment Reference')
                            ->visible(fn($get) => $get('status') === Commission::STATUS_PAID)
                            ->columnSpan(2),
                        
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('commission_id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('sale_id')
                    ->label('Sale ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('total_sale_amount')
                    ->label('Sale Amount')
                    ->money('usd')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('Rate')
                    ->suffix('%')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commission')
                    ->money('usd')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('usd'),
                    ]),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => Commission::STATUS_PENDING,
                        'success' => Commission::STATUS_PAID,
                        'danger' => Commission::STATUS_CANCELLED,
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('paid_date')
                    ->label('Paid Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('payment_reference')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(Commission::getStatuses())
                    ->multiple(),
                
                SelectFilter::make('agent_id')
                    ->label('Agent')
                    ->options(User::whereNotNull('name')->pluck('name', 'id'))
                    ->searchable(),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('markAsPaid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn(Commission $record) => $record->status === Commission::STATUS_PENDING)
                    ->form([
                        Forms\Components\TextInput::make('payment_reference')
                            ->label('Payment Reference')
                            ->required(),
                    ])
                    ->action(function (Commission $record, array $data) {
                        $record->markAsPaid($data['payment_reference']);
                    }),
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
            'index' => Pages\ListCommissions::route('/'),
            'create' => Pages\CreateCommission::route('/create'),
            'edit' => Pages\EditCommission::route('/{record}/edit'),
        ];
    }
}
