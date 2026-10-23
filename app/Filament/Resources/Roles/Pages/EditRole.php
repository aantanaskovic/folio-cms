<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Permission;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    /** @var array<int, int|string> */
    protected array $selectedPermissionIds = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (['pages', 'users', 'roles'] as $area) {
            $data["permissions_{$area}"] = [];
        }

        foreach ($this->record->permissions as $permission) {
            [$area] = explode('.', $permission->name, 2);

            $key = "permissions_{$area}";

            if (array_key_exists($key, $data)) {
                $data[$key][] = $permission->getKey();
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedPermissionIds = $this->extractPermissionIds($data);

        unset(
            $data['permissions_pages'],
            $data['permissions_users'],
            $data['permissions_roles'],
        );

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncPermissions(
            Permission::query()
                ->where('guard_name', $this->record->guard_name)
                ->whereKey($this->selectedPermissionIds)
                ->get(),
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
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
