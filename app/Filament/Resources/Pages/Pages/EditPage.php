<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Actions\Pages\DeletePage as DeletePageAction;
use App\Actions\Pages\UpdatePage as UpdatePageAction;
use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        abort_unless($record instanceof Page, 404);

        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        return app(UpdatePageAction::class)->handle($user, $record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->visible(
                    fn(Page $record): bool => Gate::allows('view', $record)
                ),

            DeleteAction::make()
                ->visible(
                    fn(Page $record): bool => Gate::allows('delete', $record)
                )
                ->action(function (Page $record): void {
                    $user = Auth::user();

                    abort_unless($user instanceof User, 403);

                    app(DeletePageAction::class)->handle($user, $record);

                    $this->redirect(PageResource::getUrl('index'));
                }),
        ];
    }
}
