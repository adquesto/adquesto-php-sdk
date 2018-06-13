<?php

namespace Adquesto\SDK;

class OAuth2Client
{
    protected $httpClient;
    protected $clientId;
    protected $authorizationUrl;
    protected $accessTokenUrl;
    protected $meUrl;
    protected $redirectUri;

    public function __construct(HttpClient $httpClient, $clientId, $authorizationUrl, $accessTokenUrl, $meUrl, $redirectUri)
    {
        $this->httpClient = $httpClient;
        $this->clientId = $clientId;
        $this->authorizationUrl = $authorizationUrl;
        $this->accessTokenUrl = $accessTokenUrl;
        $this->meUrl = $meUrl;
        $this->redirectUri = $redirectUri;
    }

    public function authorizationUrl()
    {
        return sprintf(
            '%s?%s',
            $this->authorizationUrl,
            http_build_query([
                'client_id' => $this->clientId,
                'scope' => 'read_profile',
                'redirect_uri' => $this->redirectUri,
                'response_type' => 'code',
            ])
        );
    }

    public function accessToken($code)
    {
        $response = $this->httpClient->post($this->accessTokenUrl, [
            'client_id' => $this->clientId,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
            'code' => $code,
        ]);
        $jsonResponse = json_decode($response, true);
        if (!empty($jsonResponse['error'])) {
            throw new \RuntimeException($jsonResponse['error']);
        }
        return $jsonResponse['access_token'];
    }

    public function me($accessToken)
    {
        $response = $this->httpClient->get($this->meUrl, [
            sprintf('Authorization: Bearer %s', $accessToken),
        ]);
        return json_decode($response, true);
    }
}
