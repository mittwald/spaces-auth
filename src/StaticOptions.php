<?php
namespace Mw\Spaces\OAuth2;

class StaticOptions implements Options
{

    /** @var string */
    private $signupBaseURI;

    /** @var string */
    private $spaceID;

    /** @var string */
    private $clientID;

    /** @var bool */
    private $supportLoginAllowed;

    /**
     * StaticOptions constructor.
     *
     * @param string $signupBaseURI
     * @param string $spaceID
     * @param string $clientID
     * @param bool   $supportLoginAllowed
     */
    public function __construct($signupBaseURI, $spaceID, $clientID, $supportLoginAllowed = true)
    {
        $this->signupBaseURI = $signupBaseURI;
        $this->spaceID = $spaceID;
        $this->clientID = $clientID;
        $this->supportLoginAllowed = $supportLoginAllowed;
    }

    /**
     * @return string
     */
    public function getSignupBaseURI()
    {
        return $this->signupBaseURI;
    }

    /**
     * @return string
     */
    public function getSpaceID()
    {
        return $this->signupBaseURI;
    }

    /**
     * @return string
     */
    public function getClientID()
    {
        return $this->clientID;
    }

    /**
     * @return string
     */
    public function isSupportLoginAllowed()
    {
        return $this->supportLoginAllowed;
    }
}