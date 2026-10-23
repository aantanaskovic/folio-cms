<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                ...self::permissionSections(),
            ]);
    }

    private static function permissionSections(): array
    {
        /** @var Collection<int, Permission> $permissions */
        $permissions = Permission::query()
            ->orderBy('name')
            ->get();

        return $permissions
            ->groupBy(
                fn(Permission $permission): string => explode('.', $permission->name, 2)[0]
            )
            ->map(
                fn(Collection $group, string $area): Section => Section::make(ucfirst($area))
                    ->schema([
                        CheckboxList::make("permissions_{$area}")
                            ->label('Permissions')
                            ->options(
                                $group->mapWithKeys(
                                    fn(Permission $permission): array => [
                                        $permission->getKey() => ucfirst(
                                            str_replace(
                                                '-',
                                                ' ',
                                                explode('.', $permission->name, 2)[1] ?? $permission->name,
                                            ),
                                        ),
                                    ],
                                )->all(),
                            )
                            ->columns(2)
                            ->bulkToggleable(),
                    ]),
            )
            ->values()
            ->all();
    }
}
