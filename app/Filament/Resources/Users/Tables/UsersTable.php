<?php

namespace App\Filament\Resources\Users\Tables;

use App\Actions\Users\DeleteUser as DeleteUserAction;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'admin' => 'Administrator',
                        'contributor' => 'Contributor',
                    ])
                    ->query(function ($query, array $data): void {
                        if (blank($data['value'] ?? null)) {
                            return;
                        }

                        $query->whereHas('roles', function ($query) use ($data): void {
                            $query->where('name', $data['value']);
                        });
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(
                        fn(User $record): bool => Gate::allows('update', $record)
                    ),

                DeleteAction::make()
                    ->visible(
                        fn(User $record): bool => Gate::allows('delete', $record)
                    )
                    ->action(function (User $record): void {
                        $user = Auth::user();

                        abort_unless($user instanceof User, 403);

                        app(DeleteUserAction::class)->handle($user, $record);
                    }),
            ]);
    }
}
