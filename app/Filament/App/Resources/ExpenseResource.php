<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ExpenseResource\Pages;
use App\Filament\App\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-rupee';
    
    protected static ?string $navigationLabel = 'Expenses';
    
    protected static ?string $modelLabel = 'Expense';
    
    protected static ?string $pluralModelLabel = 'Expenses';
    
    protected static ?string $navigationGroup = 'Finance';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', Auth::user()->tenant_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                Forms\Components\Select::make('category')
                    ->options([
                        'maintenance' => 'Maintenance',
                        'repair' => 'Repair',
                        'security' => 'Security',
                        'cleaning' => 'Cleaning',
                        'utilities' => 'Utilities',
                        'staff_salary' => 'Staff Salary',
                        'insurance' => 'Insurance',
                        'tax' => 'Tax',
                        'other' => 'Other',
                    ])
                    ->required()
                    ->default('other'),
                Forms\Components\TextInput::make('amount')
                    ->label('Amount')
                    ->required()
                    ->numeric()
                    ->prefix('₹')
                    ->minValue(0),
                Forms\Components\DatePicker::make('expense_date')
                    ->label('Expense Date')
                    ->required()
                    ->default(now()),
                Forms\Components\TextInput::make('vendor')
                    ->label('Vendor')
                    ->maxLength(255),
                Forms\Components\TextInput::make('receipt_number')
                    ->label('Receipt Number')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('receipt_file')
                    ->label('Receipt File')
                    ->directory('expenses/receipts')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->maxSize(5120),
                Forms\Components\Hidden::make('tenant_id')
                    ->default(fn () => Auth::user()->tenant_id),
                Forms\Components\Hidden::make('created_by')
                    ->default(fn () => Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'maintenance' => 'info',
                        'repair' => 'warning',
                        'security' => 'success',
                        'staff_salary' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'maintenance' => 'Maintenance',
                        'repair' => 'Repair',
                        'security' => 'Security',
                        'cleaning' => 'Cleaning',
                        'utilities' => 'Utilities',
                        'staff_salary' => 'Staff Salary',
                        'insurance' => 'Insurance',
                        'tax' => 'Tax',
                        'other' => 'Other',
                    ]),
                Tables\Filters\Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('expense_date', now()->month)
                        ->whereYear('expense_date', now()->year)),
                Tables\Filters\Filter::make('this_year')
                    ->label('This Year')
                    ->query(fn (Builder $query): Builder => $query->whereYear('expense_date', now()->year)),
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'view' => Pages\ViewExpense::route('/{record}'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
