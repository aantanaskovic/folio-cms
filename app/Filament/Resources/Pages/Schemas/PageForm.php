<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Page;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(table: Page::class, column: 'slug')
                    ->validationMessages([
                        'unique' => 'A page with this slug already exists.',
                    ]),

                Textarea::make('excerpt')
                    ->columnSpanFull(),

                Repeater::make('blocks')
                    ->schema([
                        Select::make('type')
                            ->options([
                                'hero' => 'Hero',
                                'text' => 'Text',
                                'gallery' => 'Gallery',
                                'cta' => 'Call to Action',
                            ])
                            ->required()
                            ->live(),

                        Grid::make(1)
                            ->schema(fn(Get $get): array => match ($get('type')) {
                                'hero' => [
                                    TextInput::make('data.heading')
                                        ->required()
                                        ->maxLength(255),

                                    Textarea::make('data.subheading'),
                                ],

                                'text' => [
                                    Textarea::make('data.content')
                                        ->required()
                                        ->columnSpanFull(),
                                ],

                                'gallery' => [
                                    Repeater::make('data.images')
                                        ->schema([
                                            FileUpload::make('url')
                                                ->image()
                                                ->disk('public')
                                                ->directory('gallery')
                                                ->required(),

                                            TextInput::make('alt')
                                                ->required()
                                                ->maxLength(255),
                                        ])
                                        ->defaultItems(1)
                                        ->addActionLabel('Add image')
                                        ->columnSpanFull(),
                                ],

                                'cta' => [
                                    TextInput::make('data.heading')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('data.label')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('data.url')
                                        ->required()
                                        ->maxLength(2048),
                                ],

                                default => [],
                            })
                            ->columnSpanFull(),
                    ])
                    ->defaultItems(0)
                    ->addActionLabel('Add content block')
                    ->columnSpanFull(),

                FileUpload::make('cover_image')
                    ->image(),

                TextInput::make('meta_title')
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('meta_description')
                    ->columnSpanFull(),

                Toggle::make('show_in_navigation')
                    ->default(false),
            ]);
    }
}
