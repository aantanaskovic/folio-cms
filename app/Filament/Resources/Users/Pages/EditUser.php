<?php

namespace App\Filament\Resources\Users\Pages;

use App\Actions\Users\DeleteUser as DeleteUserAction;
use App\Actions\Users\UpdateUser as UpdateUserAction;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! $this->record instanceof User) {
            abort(404);
        }

        $data['role'] = $this->record->roles()->value('name');

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        if (! $record instanceof User) {
            abort(404);
        }

        return app(UpdateUserAction::class)->handle($user, $record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(
                    fn(User $record): bool => Gate::allows('delete', $record)
                )
                ->action(function (User $record): void {
                    $user = Auth::user();

                    abort_unless($user instanceof User, 403);

                    app(DeleteUserAction::class)->handle($user, $record);

                    $this->redirect(UserResource::getUrl('index'));
                }),
        ];
    }
}
