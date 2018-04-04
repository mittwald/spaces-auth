<?php
namespace Mw\Spaces\OAuth2;


interface Context
{
    /**
     * @return string
     */
    public function getRedirectURI();

}