<?php
namespace Mw\Spaces\OAuth2;

interface Options
{
    /**
     * @return string
     */
    public function getSignupBaseURI();

    /**
     * @return string
     */
    public function getSpaceID();

    /**
     * @return string
     */
    public function getClientID();

    /**
     * @return string
     */
    public function isSupportLoginAllowed();
}