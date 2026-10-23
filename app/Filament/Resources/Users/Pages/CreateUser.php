<?php

namespace App\Filament\Resources\Users\Pages;

use App\Actions\Users\CreateUser as CreateUserAction;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        return app(CreateUserAction::class)->handle($user, $data);
    }
}
