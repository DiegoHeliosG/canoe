<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

/**
 * Shared OpenAPI schema definitions for JSON:API resources.
 */

#[OA\Schema(
    schema: 'FundResource',
    title: 'Fund',
    description: 'A fund resource in JSON:API format',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'funds'),
        new OA\Property(property: 'id', type: 'string', example: '1'),
        new OA\Property(
            property: 'attributes',
            type: 'object',
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Growth Fund I'),
                new OA\Property(property: 'start_year', type: 'integer', example: 2024),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
            ],
        ),
        new OA\Property(
            property: 'relationships',
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'manager',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'type', type: 'string', example: 'fund-managers'),
                                new OA\Property(property: 'id', type: 'string', example: '1'),
                            ],
                        ),
                    ],
                ),
                new OA\Property(
                    property: 'aliases',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'type', type: 'string', example: 'fund-aliases'),
                                    new OA\Property(property: 'id', type: 'string', example: '1'),
                                ],
                                type: 'object',
                            ),
                        ),
                    ],
                ),
                new OA\Property(
                    property: 'companies',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'type', type: 'string', example: 'companies'),
                                    new OA\Property(property: 'id', type: 'string', example: '1'),
                                ],
                                type: 'object',
                            ),
                        ),
                    ],
                ),
            ],
        ),
    ],
)]

#[OA\Schema(
    schema: 'FundManagerResource',
    title: 'Fund Manager',
    description: 'A fund manager resource in JSON:API format',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'fund-managers'),
        new OA\Property(property: 'id', type: 'string', example: '1'),
        new OA\Property(
            property: 'attributes',
            type: 'object',
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Blackstone Group'),
                new OA\Property(property: 'funds_count', type: 'integer', example: 5),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
            ],
        ),
    ],
)]

#[OA\Schema(
    schema: 'CompanyResource',
    title: 'Company',
    description: 'A company resource in JSON:API format',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'companies'),
        new OA\Property(property: 'id', type: 'string', example: '1'),
        new OA\Property(
            property: 'attributes',
            type: 'object',
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Acme Corp'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
            ],
        ),
    ],
)]

#[OA\Schema(
    schema: 'DuplicateWarningResource',
    title: 'Duplicate Warning',
    description: 'A duplicate fund warning resource in JSON:API format',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'duplicate-warnings'),
        new OA\Property(property: 'id', type: 'string', example: '1'),
        new OA\Property(
            property: 'attributes',
            type: 'object',
            properties: [
                new OA\Property(property: 'matched_name', type: 'string', example: 'Growth Fund I'),
                new OA\Property(property: 'is_resolved', type: 'boolean', example: false),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            ],
        ),
        new OA\Property(
            property: 'relationships',
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'fund',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'type', type: 'string', example: 'funds'),
                                new OA\Property(property: 'id', type: 'string', example: '2'),
                            ],
                        ),
                    ],
                ),
                new OA\Property(
                    property: 'duplicate_fund',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'type', type: 'string', example: 'funds'),
                                new OA\Property(property: 'id', type: 'string', example: '1'),
                            ],
                        ),
                    ],
                ),
                new OA\Property(
                    property: 'fund_manager',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'type', type: 'string', example: 'fund-managers'),
                                new OA\Property(property: 'id', type: 'string', example: '1'),
                            ],
                        ),
                    ],
                ),
            ],
        ),
    ],
)]

#[OA\Schema(
    schema: 'ValidationError',
    title: 'Validation Error',
    description: 'Validation error response (422)',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The name field is required.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string'),
            ),
            example: ['name' => ['The name field is required.']],
        ),
    ],
)]

class Schemas
{
    // This class exists solely to hold OpenAPI schema definitions.
}
