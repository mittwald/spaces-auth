<?php
namespace Mw\Spaces\OAuth2;

class StaticContext implements Context
{
    private $redirectURI;

    public function __construct($redirectURI)
    {
        $this->redirectURI = $redirectURI;
    }

    public function getRedirectURI()
    {
        return $this->redirectURI;
    }
}