<?php
namespace Mw\Spaces\OAuth2;

use InvalidArgumentException;

class EnvironmentOptions implements Options
{
    const ENV_SPACE_ID = "SPACES_SPACE_ID";
    const ENV_SIGNUP_URL = "SPACES_SIGNUP_URL";
    const ENV_OAUTH_SERVER_URL = "SPACES_OAUTH_SERVER_URL";
    const ENV_OAUTH_CLIENT_ID = "SPACES_OAUTH_CLIENT_ID";

    const DEFAULT_OAUTH_SERVER_URL = "https://signup.spaces.de";
    const DEFAULT_OAUTH_CLIENT_ID = "spaces.de/oauth/generic";

    /** @var string */
    private $signupURL;

    /** @var string */
    private $spaceID;

    /** @var string */
    private $clientID;

    /**
     * EnvironmentOptions constructor.
     * @param array $environment
     */
    public function __construct(array $environment)
    {
        if (!isset($environment[static::ENV_SPACE_ID])) {
            throw new InvalidArgumentException('missing environment variable: "' . static::ENV_SPACE_ID . '"');
        }

        if (isset($environment[static::ENV_SIGNUP_URL])) {
            $this->signupURL = $environment[static::ENV_SIGNUP_URL];
        } else if (isset($environment[static::ENV_OAUTH_SERVER_URL])) {
            $this->signupURL = $environment[static::ENV_OAUTH_SERVER_URL];
        } else {
            $this->signupURL = static::DEFAULT_OAUTH_SERVER_URL;
        }

        $this->spaceID = $environment[static::ENV_SPACE_ID];
        $this->clientID = $environment[static::ENV_OAUTH_CLIENT_ID] ?: (static::DEFAULT_OAUTH_CLIENT_ID . '/' . $this->spaceID);
    }

    public function getSignupBaseURI()
    {
        return $this->signupURL;
    }

    public function getSpaceID()
    {
        return $this->spaceID;
    }

    public function getClientID()
    {
        return $this->clientID;
    }

    public function isSupportLoginAllowed()
    {
        return true;
    }

}