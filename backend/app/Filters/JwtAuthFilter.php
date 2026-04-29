<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * JWT Authentication Filter
 * 
 * Protects API endpoints by validating JWT tokens.
 * Applied to all /api/* routes except /api/login.
 */
class JwtAuthFilter implements FilterInterface
{
    /**
     * Before filter — validates JWT token
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('jwt');

        $response = service('response');

        // Get Authorization header
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return $response->setStatusCode(401)
                           ->setJSON([
                               'status'  => 'error',
                               'message' => 'Authorization header missing',
                           ]);
        }

        // Extract token
        $token = jwt_extract_token($authHeader);

        if (empty($token)) {
            return $response->setStatusCode(401)
                           ->setJSON([
                               'status'  => 'error',
                               'message' => 'Invalid authorization format. Use: Bearer {token}',
                           ]);
        }

        // Decode and validate
        $decoded = jwt_decode_token($token);

        if ($decoded === null) {
            return $response->setStatusCode(401)
                           ->setJSON([
                               'status'  => 'error',
                               'message' => 'Invalid or expired token',
                           ]);
        }

        // Store user data in request for controllers to access
        // Store user data for controllers to access via service
        $request->setHeader('X-Auth-User', json_encode($decoded->data ?? null));
    }

    /**
     * After filter — no action needed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}
