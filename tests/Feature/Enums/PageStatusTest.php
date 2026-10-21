<?php

use App\Enums\PageStatus;

it('defines all supported page statuses', function () {
    expect(PageStatus::cases())
        ->toHaveCount(3)
        ->and(PageStatus::Draft->value)->toBe('draft')
        ->and(PageStatus::Pending->value)->toBe('pending')
        ->and(PageStatus::Published->value)->toBe('published');
});

it('can be created from a valid status value', function (
    string $value,
    PageStatus $expected,
) {
    expect(PageStatus::from($value))->toBe($expected);
})->with([
    'draft' => ['draft', PageStatus::Draft],
    'pending' => ['pending', PageStatus::Pending],
    'published' => ['published', PageStatus::Published],
]);

it('rejects an unsupported status value', function () {
    PageStatus::from('archived');
})->throws(ValueError::class);
