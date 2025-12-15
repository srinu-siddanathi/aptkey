<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ComplaintResource\Pages;
use App\Filament\App\Resources\ComplaintResource\RelationManagers;
use App\Models\Complaint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    
    protected static ?string $navigationLabel = 'Complaints';
    
    protected static ?string $modelLabel = 'Complaint';
    
    protected static ?string $pluralModelLabel = 'Complaints';
    
    protected static ?string $navigationGroup = 'Operations';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', Auth::user()->tenant_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'unit_number', fn (Builder $query) => $query->where('tenant_id', Auth::user()->tenant_id))
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('subject')
                    ->label('Subject')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Select::make('category')
                    ->options([
                        'plumbing' => 'Plumbing',
                        'electrical' => 'Electrical',
                        'cleaning' => 'Cleaning',
                        'security' => 'Security',
                        'parking' => 'Parking',
                        'noise' => 'Noise',
                        'elevator' => 'Elevator',
                        'other' => 'Other',
                    ])
                    ->required()
                    ->default('other'),
                Forms\Components\Select::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ])
                    ->required()
                    ->default('medium'),
                Forms\Components\Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('open'),
                Forms\Components\Select::make('assigned_to')
                    ->label('Assigned To')
                    ->relationship('assignee', 'name', fn (Builder $query) => $query->where('tenant_id', Auth::user()->tenant_id)->where('role', 'apartment_manager'))
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('resolution_notes')
                    ->label('Resolution Notes')
                    ->rows(3)
                    ->columnSpanFull()
                    ->visible(fn ($get) => in_array($get('status'), ['resolved', 'closed'])),
                Forms\Components\DateTimePicker::make('resolved_at')
                    ->label('Resolved At')
                    ->visible(fn ($get) => in_array($get('status'), ['resolved', 'closed'])),
                Forms\Components\TextInput::make('ticket_number')
                    ->label('Ticket Number')
                    ->default(fn () => 'TKT-' . date('Ymd') . '-' . strtoupper(uniqid()))
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\Hidden::make('tenant_id')
                    ->default(fn () => Auth::user()->tenant_id),
                Forms\Components\Hidden::make('raised_by')
                    ->default(fn () => Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('Ticket #')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('unit.full_identifier')
                    ->label('Unit')
                    ->getStateUsing(fn (Complaint $record): string => $record->unit ? (($record->unit->block ? $record->unit->block . ' - ' : '') . $record->unit->unit_number) : 'N/A')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge(),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'medium' => 'info',
                        'low' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'in_progress' => 'info',
                        'resolved' => 'success',
                        'closed' => 'gray',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Assigned To')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Raised On')
                    ->dateTime()
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'plumbing' => 'Plumbing',
                        'electrical' => 'Electrical',
                        'cleaning' => 'Cleaning',
                        'security' => 'Security',
                        'parking' => 'Parking',
                        'noise' => 'Noise',
                        'elevator' => 'Elevator',
                        'other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('assign_to_manager')
                        ->label('Assign to Manager')
                        ->icon('heroicon-o-user-plus')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('assigned_to')
                                ->label('Assign To')
                                ->relationship('assignee', 'name', fn ($query) => $query->where('tenant_id', Auth::user()->tenant_id)->where('role', 'apartment_manager'))
                                ->required()
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update([
                                    'assigned_to' => $data['assigned_to'],
                                    'status' => 'in_progress',
                                ]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListComplaints::route('/'),
            'create' => Pages\CreateComplaint::route('/create'),
            'view' => Pages\ViewComplaint::route('/{record}'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }
}
