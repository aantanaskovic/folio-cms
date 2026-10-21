<?php

namespace App\Validators\Pages;

use App\Models\Page;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PageDataValidator
{
    public function validate(array $data, ?Page $page = null): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(Page::class, 'slug')->ignore($page),
            ],
            'excerpt' => ['nullable', 'string'],
            'blocks' => ['present', 'array'],
            'blocks.*' => ['required', 'array:type,data'],
            'blocks.*.type' => [
                'required',
                'string',
                Rule::in(['hero', 'text', 'gallery', 'cta']),
            ],
            'blocks.*.data' => ['required', 'array'],
            'show_in_navigation' => ['sometimes', 'boolean'],
        ];

        foreach (
            is_array($data['blocks'] ?? null) ? $data['blocks'] : []
            as $index => $block
        ) {
            if (
                ! is_array($block)
                || ! is_array($block['data'] ?? null)
            ) {
                continue;
            }

            $prefix = "blocks.{$index}.data";

            switch ($block['type'] ?? null) {
                case 'hero':
                    $rules["{$prefix}.heading"] = [
                        'required',
                        'string',
                        'max:255',
                    ];
                    $rules["{$prefix}.subheading"] = [
                        'nullable',
                        'string',
                    ];

                    break;

                case 'text':
                    $rules["{$prefix}.content"] = [
                        'required',
                        'string',
                    ];

                    break;

                case 'gallery':
                    $rules["{$prefix}.images"] = [
                        'present',
                        'array',
                    ];
                    $rules["{$prefix}.images.*"] = [
                        'required',
                        'array:url,alt',
                    ];
                    $rules["{$prefix}.images.*.url"] = [
                        'required',
                        'string',
                    ];
                    $rules["{$prefix}.images.*.alt"] = [
                        'required',
                        'string',
                        'max:255',
                    ];

                    break;

                case 'cta':
                    $rules["{$prefix}.heading"] = [
                        'required',
                        'string',
                        'max:255',
                    ];
                    $rules["{$prefix}.label"] = [
                        'required',
                        'string',
                        'max:255',
                    ];
                    $rules["{$prefix}.url"] = [
                        'required',
                        'string',
                        'max:2048',
                    ];

                    break;
            }
        }

        return Validator::make($data, $rules)->validate();
    }
}
