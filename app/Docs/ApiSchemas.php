<?php

namespace App\Docs;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="User ID"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="User's name"
 *     ),
 *      @OA\Property(
 *         property="firstname",
 *         type="string",
 *         description="User's first name"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="User's email address"
 *     ),
 *      @OA\Property(
 *         property="joined",
 *         type="string",
 *         format="date",
 *         description="User's joined date"
 *     ),
 *      @OA\Property(
 *         property="gmt",
 *         type="string",
 *         format="",
 *         description="User's email address"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the user was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the user was last updated"
 *     )
 * )
 */
class ApiSchemas
{
    // No methods or properties needed here; this file is just for schema definitions.
}
