<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\SelectFilter;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    protected static ?string $navigationGroup = 'Sales Management';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Payment Information')
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
                                        $set('suggested_amount', $sale->remaining_amount);
                                    }
                                }
                            })
                            ->columnSpan(2),
                        
                        Forms\Components\Placeholder::make('suggested_amount')
                            ->label('Remaining Amount')
                            ->content(fn($get) => $get('suggested_amount') ? '$' . number_format($get('suggested_amount'), 2) : 'N/A')
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Payment Amount')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('payment_type')
                            ->label('Payment Type')
                            ->options(Payment::getPaymentTypes())
                            ->default(Payment::TYPE_DEPOSIT)
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options(Payment::getPaymentMethods())
                            ->default(Payment::METHOD_CASH)
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('paid_by')
                            ->label('Received By')
                            ->options(User::whereNotNull('name')->pluck('name', 'id'))
                            ->searchable()
                            ->default(auth()->id())
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\DateTimePicker::make('payment_date')
                            ->label('Payment Date')
                            ->default(now())
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('transaction_reference')
                            ->label('Transaction Reference')
                            ->maxLength(255)
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
                Tables\Columns\TextColumn::make('payment_id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('sale_id')
                    ->label('Sale ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('sale.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->money('usd')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('usd'),
                    ]),
                
                Tables\Columns\BadgeColumn::make('payment_type')
                    ->label('Type')
                    ->colors([
                        'warning' => Payment::TYPE_DEPOSIT,
                        'success' => Payment::TYPE_BALANCE,
                        'info' => Payment::TYPE_FULL,
                    ]),
                
                Tables\Columns\BadgeColumn::make('payment_method')
                    ->label('Method')
                    ->colors([
                        'success' => Payment::METHOD_CASH,
                        'info' => Payment::METHOD_BANK,
                        'warning' => Payment::METHOD_QR,
                        'primary' => Payment::METHOD_CREDIT_CARD,
                    ]),
                
                Tables\Columns\TextColumn::make('paidBy.name')
                    ->label('Received By')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('transaction_reference')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_type')
                    ->options(Payment::getPaymentTypes())
                    ->multiple(),
                
                SelectFilter::make('payment_method')
                    ->options(Payment::getPaymentMethods())
                    ->multiple(),
                
                Tables\Filters\Filter::make('payment_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q, $date) => $q->whereDate('payment_date', '>=', $date))
                            ->when($data['until'], fn($q, $date) => $q->whereDate('payment_date', '<=', $date));
                    }),
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
            ->defaultSort('payment_date', 'desc');
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
