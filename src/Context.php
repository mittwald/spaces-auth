<?php
namespace MW\Spaces\OAuth2;


interface Context
{
    /**
     * @return string
     */
    public function getRedirectURI();

}