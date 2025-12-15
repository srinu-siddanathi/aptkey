<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\UnitResource\Pages;
use App\Filament\App\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $navigationLabel = 'Units';
    
    protected static ?string $modelLabel = 'Unit';
    
    protected static ?string $pluralModelLabel = 'Units';
    
    protected static ?string $navigationGroup = 'Residents';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', Auth::user()->tenant_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('block')
                    ->label('Block')
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit_number')
                    ->label('Unit Number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        '1BHK' => '1 BHK',
                        '2BHK' => '2 BHK',
                        '3BHK' => '3 BHK',
                        '4BHK' => '4 BHK',
                        'Penthouse' => 'Penthouse',
                        'Shop' => 'Shop',
                        'Office' => 'Office',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('area_sqft')
                    ->label('Area (sq ft)')
                    ->numeric()
                    ->suffix('sq ft'),
                Forms\Components\TextInput::make('monthly_maintenance')
                    ->label('Monthly Maintenance')
                    ->required()
                    ->numeric()
                    ->prefix('₹')
                    ->default(0.00),
                Forms\Components\Select::make('resident_id')
                    ->label('Resident')
                    ->relationship('resident', 'name', fn (Builder $query) => $query->where('tenant_id', Auth::user()->tenant_id)->where('role', 'resident'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Toggle::make('is_occupied')
                    ->label('Is Occupied')
                    ->default(true),
                Forms\Components\Hidden::make('tenant_id')
                    ->default(fn () => Auth::user()->tenant_id),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_identifier')
                    ->label('Unit')
                    ->getStateUsing(fn (Unit $record): string => ($record->block ? $record->block . ' - ' : '') . $record->unit_number)
                    ->searchable(['block', 'unit_number'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('resident.name')
                    ->label('Resident')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monthly_maintenance')
                    ->label('Maintenance')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_occupied')
                    ->label('Occupied')
                    ->boolean(),
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
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        '1BHK' => '1 BHK',
                        '2BHK' => '2 BHK',
                        '3BHK' => '3 BHK',
                        '4BHK' => '4 BHK',
                        'Penthouse' => 'Penthouse',
                        'Shop' => 'Shop',
                        'Office' => 'Office',
                    ]),
                Tables\Filters\TernaryFilter::make('is_occupied')
                    ->label('Occupied')
                    ->placeholder('All')
                    ->trueLabel('Occupied only')
                    ->falseLabel('Vacant only'),
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
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'view' => Pages\ViewUnit::route('/{record}'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
