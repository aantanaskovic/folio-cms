<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\PageStatus;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Page details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title')
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('slug')
                            ->copyable(),

                        TextEntry::make('author.name')
                            ->label('Author'),

                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(
                                fn(PageStatus|string|null $state): string => match ($state instanceof PageStatus ? $state : PageStatus::tryFrom((string) $state)) {
                                    PageStatus::Draft => 'Draft',
                                    PageStatus::Pending => 'Pending review',
                                    PageStatus::Published => 'Published',
                                    default => 'Unknown',
                                }
                            )
                            ->color(fn(PageStatus|string|null $state): string => match ($state instanceof PageStatus ? $state : PageStatus::tryFrom((string) $state)) {
                                PageStatus::Draft => 'gray',
                                PageStatus::Pending => 'warning',
                                PageStatus::Published => 'success',
                                default => 'gray',
                            }),

                        TextEntry::make('published_at')
                            ->dateTime()
                            ->placeholder('Not published'),

                        TextEntry::make('excerpt')
                            ->placeholder('No excerpt provided')
                            ->columnSpanFull(),

                        ImageEntry::make('cover_image')
                            ->label('Cover image')
                            ->placeholder('No cover image'),
                    ]),

                Section::make('SEO and navigation')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('meta_title')
                            ->label('Meta title')
                            ->placeholder('Not set'),

                        IconEntry::make('show_in_navigation')
                            ->label('Show in navigation')
                            ->boolean(),

                        TextEntry::make('meta_description')
                            ->label('Meta description')
                            ->placeholder('No meta description provided')
                            ->columnSpanFull(),
                    ]),

                Section::make('Content blocks')
                    ->schema([
                        RepeatableEntry::make('blocks')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('type')
                                    ->badge()
                                    ->formatStateUsing(
                                        fn(string $state): string => match ($state) {
                                            'hero' => 'Hero',
                                            'text' => 'Text',
                                            'gallery' => 'Gallery',
                                            'cta' => 'Call to Action',
                                            default => ucfirst($state),
                                        }
                                    ),

                                TextEntry::make('data.heading')
                                    ->label('Heading')
                                    ->visible(fn($state, $record): bool => filled($state)),

                                TextEntry::make('data.subheading')
                                    ->label('Subheading')
                                    ->visible(fn($state, $record): bool => filled($state)),

                                TextEntry::make('data.content')
                                    ->label('Content')
                                    ->columnSpanFull()
                                    ->visible(fn($state, $record): bool => filled($state)),

                                RepeatableEntry::make('data.images')
                                    ->label('Gallery images')
                                    ->schema([
                                        ImageEntry::make('url')
                                            ->label('Image'),

                                        TextEntry::make('alt')
                                            ->label('Alt text'),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),

                                TextEntry::make('data.label')
                                    ->label('Button label')
                                    ->visible(fn($state, $record): bool => filled($state)),

                                TextEntry::make('data.url')
                                    ->label('Button URL')
                                    ->url(fn(?string $state): ?string => $state)
                                    ->openUrlInNewTab()
                                    ->visible(fn($state, $record): bool => filled($state)),
                            ]),
                    ]),

                Section::make('Record history')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ]),
            ]);
    }
}
