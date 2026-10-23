<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    /** @var array<int, int|string> */
    protected array $selectedPermissionIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedPermissionIds = $this->extractPermissionIds($data);

        unset(
            $data['permissions_pages'],
            $data['permissions_users'],
            $data['permissions_roles'],
        );

        $data['guard_name'] = 'web';

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncPermissions(
            Permission::query()
                ->where('guard_name', 'web')
                ->whereKey($this->selectedPermissionIds)
                ->get(),
        );
    }

    /**
     * @return array<int, int|string>
     */
    private function extractPermissionIds(array &$data): array
    {
        $permissionIds = [];

        foreach (['pages', 'users', 'roles'] as $area) {
            $key = "permissions_{$area}";

            $permissionIds = [
                ...$permissionIds,
                ...($data[$key] ?? []),
            ];

            unset($data[$key]);
        }

        return array_values(array_unique($permissionIds));
    }
}
