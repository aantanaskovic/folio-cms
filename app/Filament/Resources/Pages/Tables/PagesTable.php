<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Actions\Pages\DeletePage as DeletePageAction;
use App\Actions\Pages\PublishPage;
use App\Actions\Pages\ReviewPage;
use App\Actions\Pages\SubmitPageForReview;
use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(
                        fn(PageStatus|string|null $state): string => str(
                            $state instanceof PageStatus
                                ? $state->value
                                : ($state ?? '')
                        )->headline()->toString()
                    )
                    ->sortable(),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not published'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        PageStatus::Draft->value => 'Draft',
                        PageStatus::Pending->value => 'Pending Review',
                        PageStatus::Published->value => 'Published',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->visible(
                        fn(Page $record): bool => Gate::allows('view', $record)
                    ),

                EditAction::make()
                    ->visible(
                        fn(Page $record): bool => Gate::allows('update', $record)
                    ),

                Action::make('submitForReview')
                    ->label('Submit for Review')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(
                        fn(Page $record): bool => Gate::allows('submitForReview', $record)
                    )
                    ->requiresConfirmation()
                    ->action(function (Page $record): void {
                        $user = Auth::user();

                        abort_unless($user instanceof User, 403);

                        app(SubmitPageForReview::class)->handle($user, $record);
                    }),

                Action::make('returnToDraft')
                    ->label('Return to Draft')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(
                        fn(Page $record): bool => Gate::allows('review', $record)
                            && $record->status === PageStatus::Pending
                    )
                    ->requiresConfirmation()
                    ->action(function (Page $record): void {
                        $user = Auth::user();

                        abort_unless($user instanceof User, 403);

                        app(ReviewPage::class)->handle($user, $record, false);
                    }),

                Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(
                        fn(Page $record): bool => Gate::allows('publish', $record)
                            && $record->status === PageStatus::Pending
                    )
                    ->requiresConfirmation()
                    ->action(function (Page $record): void {
                        $user = Auth::user();

                        abort_unless($user instanceof User, 403);

                        app(PublishPage::class)->handle($user, $record);
                    }),

                DeleteAction::make()
                    ->visible(
                        fn(Page $record): bool => Gate::allows('delete', $record)
                    )
                    ->action(function (Page $record): void {
                        $user = Auth::user();

                        abort_unless($user instanceof User, 403);

                        app(DeletePageAction::class)->handle($user, $record);
                    }),
            ]);
    }
}
