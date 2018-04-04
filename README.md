# SPACES OAuth2.0 authentication

Client library to integrate an OAuth2.0 authorization flow into PHP
applications.

## Usage

1.  Provide your own implementation of the `Mw\Spaces\OAuth2\Context`
    interface:

        namespace Your\Namespace;
        
        class Context implements \Mw\Spaces\OAuth2\Context
        {
            public function getRedirectURI()
            {
                return "https://my-application.example/oauth-redir";
            }        
        }
        
    Note that the `/oauth-redir` path needs to point to an
    application-specific OAuth2 redirection handler implemented by
    you.

1.  Create the OAuth2.0 provider:

        $ctx = new \Your\Namespace\Context();
        $opts = new \Mw\Spaces\OAuth2\EnvironmentOptions($_SERVER);
        
        $provider = new \Mw\Spaces\OAuth2\SpacesProvider($opts, $ctx); 

1.  Next, retrieve the authorization URL and redirect your user there:

        $authorizationURL = $provider->getAuthorizationUrl();

        $_SESSION["spaces.de/auth/csrf"] = $provider->getState();

        header("Location: " . $authorizationURL);
        
1.  The identity provider will prompt the user for their credentials,
    and - on success - will redirect the user back to your
    Redirect URI. When handling the redirected request, you'll need
    to retrieve the authorization code and check the CSRF value:
    
        $state = $_GET["state"];
        $code  = $_GET["code"];
        
        if ($_SESSION["spaces.de/auth/csrf"] != $state) {
            die("...");
        }
        
    After that, you can use the code to retrieve your access token:
    
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

1.  Having the `$accessToken`, you can now (all while handling the
    redirected request) use that token to load the resource owner:
    
        try {
            $owner = $provider->getResourceOwner($accessToken);
            $ownerID = $accessToken->getResourceOwnerId();
            
            // synchronize local user using $owner
        } catch (\Mw\Spaces\OAuth2\Error\UserNotPresentException $err) {
            // user has no access to project
            // deny login
        }
    
    Use the data in the `$owner` object to construct a new local user
    (or update an existing record). You can store the _Resource Owner
    ID_ for each created user to match them later on. 