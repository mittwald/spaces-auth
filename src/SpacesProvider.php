<?php
namespace Mw\Spaces\OAuth2;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Mw\Spaces\OAuth2\Error\UserNotPresentException;
use Psr\Http\Message\ResponseInterface;

/**
 * OAuth2 provider for SPACES login
 *
 * @package Mw\Spaces\OAuth2
 */
class SpacesProvider extends GenericProvider
{
    /** @var Options */
    private $opts;

    /**
     * SpacesProvider constructor.
     *
     * @param Options $opts          Options for building the client.
     * @param Context $ctx           The authentication context. This is typically user-provided
     *                               and needs to implement logic like building the redirect URL.
     * @param array   $options       Additional options that should be passed to the underlying
     *                               provider
     * @param array   $collaborators Super-simple DI container
     */
    public function __construct(Options $opts, Context $ctx, array $options = [], array $collaborators = [])
    {
        $baseURL = $opts->getSignupBaseURI();
        $this->opts = $opts;

        parent::__construct(array_merge($options, [
            "clientId" => $opts->getClientID(),
            "clientSecret" => "",
            "redirectUri" => $ctx->getRedirectURI(),
            "urlAuthorize" => $baseURL . "/o/oauth2/auth",
            "urlAccessToken" => $baseURL . "/o/oauth2/token",
            "urlResourceOwnerDetails" => $baseURL . "/o/oauth2/profile?spaceID=" . urlencode($opts->getSpaceID()),
        ]), $collaborators);
    }

    /**
     * @param AccessToken $token
     * @return SpacesResourceOwner
     *
     * @throws IdentityProviderException
     */
    public function getResourceOwner(AccessToken $token)
    {
        try {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return parent::getResourceOwner($token);
        } catch (IdentityProviderException $err) {
            if ($err->getCode() === 404) {
                throw new UserNotPresentException($err->getCode(), $err->getResponseBody());
            }
            throw $err;
        }
    }

    public function getAuthorizationUrl(array $options = [])
    {
        if (!isset($options["scope"])) {
            $options["scope"] = ['profile:read', 'spaces:read'];
        }

        return parent::getAuthorizationUrl($options);
    }


    protected function createResourceOwner(array $response, AccessToken $token)
    {
        if (isset($response['profile'])) {
            $response = array_merge($response['profile'], $response);
            unset($response['profile']);
        }

        return new SpacesResourceOwner(
            $response,
            $this->opts->isSupportLoginAllowed()
        );
    }

    /**
     * @param ResponseInterface $response
     * @param array             $data
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        parent::checkResponse($response, $data);

        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException("unexpected status code: " . $response->getStatusCode(), $response->getStatusCode(), $data);
        }
    }

    protected function getScopeSeparator()
    {
        // " " is mandated by RFC 6749. league/oauth2-client, WHY the fuck would you choose a different default value!?
        return " ";
    }

    protected function getAuthorizationHeaders($token = null)
    {
        return [
            "X-Access-Token" => $token,
        ];
    }

}