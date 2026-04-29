<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Session Authentication Filter
 * 
 * Protects web routes by checking for a valid PHP session.
 * Redirects unauthenticated users to the login page.
 */
class SessionAuthFilter implements FilterInterface
{
    /**
     * Before filter — checks session for logged-in user
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Check if user is authenticated
        if (!$session->get('logged_in')) {
            // Store intended URL for redirect after login
            $session->set('redirect_url', current_url());

            return redirect()->to('/login')->with('info', 'Please sign in to continue.');
        }
    }

    /**
     * After filter — no action needed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}
