<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Zenith API Documentation",
 *      description="Documentation for Zenith API endpoints",
 *      @OA\Contact(
 *          email="admin@zenith.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Get(
 *     path="/",
 *     description="Home Page",
 *     @OA\Response(response="default", description="Welcome page")
 * )
 */
abstract class Controller
{
    //
}
