<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Canoe Fund Management API',
    description: 'REST API for managing investment funds, fund managers, companies, and duplicate detection. Responses follow the JSON:API specification.',
)]
#[OA\Server(url: 'http://localhost:8080', description: 'Local development')]
abstract class Controller
{
    //
}
