<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AzureADAuthService
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $graphApiUrl;
    protected $tenantID;
    protected $SSLConection;

    /**
     *   constructor inti
     */
    public function __construct()
    {
        $this->clientId = config('azure.client_id');
        $this->clientSecret = config('azure.client_secret');
        $this->redirectUri = config('azure.redirect_uri');
        $this->graphApiUrl = config('azure.graph_api_url_resource'); ;
        $this->tenantID =  config('azure.tenant_id');
        $this->SSLConection =  config('azure.use_ssl');
    }

    /**
     * Generate the Azure AD authorization URL for user login.
     *
     * This method generates the Azure AD authorization URL that users will
     * be redirected to for authentication.It also generates and stores a random
     * state value to protect against cross-site request forgery (CSRF) attacks.
     * @return string
     * @throws \Exception
     */
    public function getAuthorizationUrl()
    {
        // Generate a unique state value to protect against CSRF attacks
        $state = bin2hex(random_bytes(16));
        Session::put('azure_state', $state);

        // Construct the Azure AD authorization URL
        $authorizeUrl = "https://login.microsoftonline.com/$this->tenantID /oauth2/authorize";
        $authParams = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
//            'scope' => '', // Adjust scopes as needed
            'state' => $state,
        ];

        return $authorizeUrl . '?' . http_build_query($authParams);
    }

    /**
     * Exchange an authorization code for an access token.
     *
     * This method exchanges the authorization code obtained from the Azure AD callback
     * for an access token and possibly a refresh token.
     * It makes a POST request to the Azure AD token endpoint.
     * @param $code
     * @return array|mixed
     * @throws \Exception
     */
    public function getTokenFromCode($code)
    {
        $tokenEndpoint = "https://login.microsoftonline.com/$this->tenantID/oauth2/v2.0/token";
        $tokenParams = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
//            'scope' => '', // Adjust scopes as needed
        ];

        $response = Http::withOptions([
             'verify' => $this->SSLConection ,
        ])->asForm()->post($tokenEndpoint, $tokenParams);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Error: Unable to obtain access token');
    }

    /**
     * Fetch user profile data from the Microsoft Graph API.
     *
     * this method fetches the user's profile data from the Microsoft
     * Graph API using the provided access token. It adds the access
     * token to the request headers for authentication.
     * @param $accessToken
     * @return array|mixed
     * @throws \Exception
     */
    public function getUserProfile($accessToken)
    {
        $response = Http::withOptions([
            'verify' => $this->SSLConection ,
        ])->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get($this->graphApiUrl);
        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Error: Unable to fetch user profile');
    }
}
