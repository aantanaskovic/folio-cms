<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Actions\Pages\CreatePage as CreatePageAction;
use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        return app(CreatePageAction::class)->handle($user, $data);
    }
}
