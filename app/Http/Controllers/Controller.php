<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

    /**
     * @OA\Info(
     *     title="My Laravel API",
     *     version="1.0.0",
     *     description="API documentation for my Laravel application",
     *     @OA\Contact(
     *         email="support@example.com"
     *     )
     * )
     */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
