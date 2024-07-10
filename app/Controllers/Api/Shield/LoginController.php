<?php

// app/Controllers/Auth/LoginController.php

declare(strict_types=1);

namespace App\Controllers\Api\Shield;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Authentication\JWTManager;
use CodeIgniter\Shield\Validation\ValidationRules;

class LoginController extends BaseController
{
    use ResponseTrait;

    /**
     * Authenticate Existing User and Issue JWT.
     */
    public function jwtLogin(): ResponseInterface
    {
        // Get the validation rules
        $rules = $this->getValidationRules();

        // Validate credentials
        if (! $this->validateData($this->request->getJSON(true), $rules, [], config('Auth')->DBGroup)) {
            return $this->fail(
                ['errors' => $this->validator->getErrors()],
                $this->codes['unauthorized']
            );
        }

        // Get the credentials for login
        $credentials             = $this->request->getJsonVar(setting('Auth.validFields'));
        $credentials             = array_filter($credentials);
        $credentials['password'] = $this->request->getJsonVar('password');

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        // Check the credentials
        $result = $authenticator->check($credentials);

        // Credentials mismatch.
        if (! $result->isOK()) {
            return $this->failUnauthorized($result->reason());
        }

        $user = $result->extraInfo();

        /** @var JWTManager $manager */
        $manager = service('jwtmanager');

        // Generate JWT and return to client
        $jwt = $manager->generateToken($user);

        return $this->respond([
            'access_token' => $jwt,
        ]);
    }

    /**
    * Check if user is authenticated and Issue JWT.
    */
    public function issueJwt(): ResponseInterface
    {
        /** @var JWTManager $manager */
        $manager = service('jwtmanager');
    
        $user = auth()->user();
    
        if (!$user) {
            return $this->respond([
                'error' => 'User is not authenticated! Please login again.',
            ], 401);
        }
    
        // Generate access token
        $accessClaims = [
            'username' => $user->username,
        ];
        $accessToken = $manager->generateToken($user, $accessClaims);
    
        // Generate refresh token
        $refreshClaims = [
            'user_id' => $user->id,
        ];
        $refreshToken = $manager->issue($refreshClaims, MONTH);

        return $this->respond([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ]);
    }


    /**
    * Check if user is authenticated and Issue JWT.
    */
    public function refreshJwt(): ResponseInterface
    {
        $refreshToken = $this->request->getPost('refresh_token');
        
        /** @var JWTManager $manager */
        $manager = service('jwtmanager');

        $refreshTokenData = $manager->parse($refreshToken);
    
        if (!$refreshTokenData) {
            return $this->respond([
                'error' => 'Invalid or expired refresh token.',
            ], 401);
        }

        $user = auth()->user();
    
        if (!$user) {
            return $this->respond([
                'error' => 'User is not authenticated! Please login again.',
            ], 401);
        }
    
        // Generate access token
        $accessClaims = [
            'username' => $user->username,
        ];
        $accessToken = $manager->generateToken($user, $accessClaims);

        return $this->respond([
            'access_token' => $accessToken
        ]);
    }

    /**
     * Returns the rules that should be used for validation.
     *
     * @return array<string, array<string, array<string>|string>>
     * @phpstan-return array<string, array<string, string|list<string>>>
     */
    protected function getValidationRules(): array
    {
        $rules = new ValidationRules();

        return $rules->getLoginRules();
    }
}