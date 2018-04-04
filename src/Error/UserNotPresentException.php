<?php
namespace MW\Spaces\OAuth2\Error;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class UserNotPresentException extends IdentityProviderException
{
    public function __construct($code, $response)
    {
        parent::__construct('this user does not have access to this TYPO3 project', $code, $response);
    }
}