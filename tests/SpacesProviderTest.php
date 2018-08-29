<?php
namespace Mw\Spaces\OAuth2\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Token\AccessToken;
use Mw\Spaces\OAuth2\SpacesProvider;
use Mw\Spaces\OAuth2\StaticContext;
use Mw\Spaces\OAuth2\StaticOptions;
use PHPUnit\Framework\TestCase;

class SpacesProviderTest extends TestCase
{
    /** @var SpacesProvider */
    private $sut;

    public function setUp()
    {
        $this->sut = new SpacesProvider(
            new StaticOptions("https://signup.spaces.example", "some-space-id", "spaces.de/oauth/test", true),
            new StaticContext("https://application.example/oauth-redir")
        );
    }

    public function testAuthorizationUrlIsBuiltCorrectly()
    {
        $authURL = $this->sut->getAuthorizationUrl();
        assertThat($authURL, logicalAnd(
            isAbsoluteUri(),
            hasQueryParameter("redirect_uri", "https://application.example/oauth-redir"),
            hasQueryParameter("client_id", "spaces.de/oauth/test"),
            hasQueryParameter("state", logicalNot(isEmpty())),
            hasQueryParameter("response_type", "code"),
            hasQueryParameter("scope", "profile:read spaces:read")
        ));
    }

    public function testAuthorizationUrlIsBuiltCorrectlyWithCustomScope()
    {
        $authURL = $this->sut->getAuthorizationUrl(["scope" => ["foo", "bar"]]);
        assertThat($authURL, logicalAnd(
            isAbsoluteUri(),
            hasQueryParameter("redirect_uri", "https://application.example/oauth-redir"),
            hasQueryParameter("client_id", "spaces.de/oauth/test"),
            hasQueryParameter("state", logicalNot(isEmpty())),
            hasQueryParameter("response_type", "code"),
            hasQueryParameter("scope", "foo bar")
        ));
    }

    public function testStateIsBuiltCorrectly()
    {
        $authURL = $this->sut->getAuthorizationUrl();
        $state = $this->sut->getState();

        assertThat($state, logicalNot(isEmpty()));
        assertThat($authURL, hasQueryParameter("state", $state));
    }

    public function testAccessTokenIsRetrievedCorrectly()
    {
        $requests = [];
        $mock = new MockHandler([
            new Response(200, [], json_encode(["access_token" => "secret-token"]))
        ]);
        $handler = HandlerStack::create($mock);
        $handler->push(Middleware::history($requests));
        $client = new Client(["handler" => $handler]);

        $this->sut = new SpacesProvider(
            new StaticOptions("https://signup.spaces.example", "some-space-id", "spaces.de/oauth/test", true),
            new StaticContext("https://application.example/oauth-redir"),
            [],
            ["httpClient" => $client]
        );

        $token = $this->sut->getAccessToken("authorization_code", ["code" => "secret-code"]);

        assertThat($token->getToken(), equalTo("secret-token"));
        assertThat($requests, countOf(1));
        assertThat($requests[0]["request"], logicalAnd(
            hasMethod("POST"),
            hasUri("https://signup.spaces.example/o/oauth2/token"),
            hasContentType("application/x-www-form-urlencoded"),
            bodyMatchesForm([
                "grant_type" => "authorization_code",
                "code" => "secret-code",
                "redirect_uri" => "https://application.example/oauth-redir",
                "client_id" => "spaces.de/oauth/test"
            ])
        ));
    }

    public function testResourceOwnerIsRetrievedCorrectly()
    {
        $requests = [];
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                "role" => "owner",
                "id" => "some-uuid",
                "email" => "m.mustermann@example.com"
            ])),
        ]);
        $handler = HandlerStack::create($mock);
        $handler->push(Middleware::history($requests));
        $client = new Client(["handler" => $handler]);

        $this->sut = new SpacesProvider(
            new StaticOptions("https://signup.spaces.example", "some-space-id", "spaces.de/oauth/test", true),
            new StaticContext("https://application.example/oauth-redir"),
            [],
            ["httpClient" => $client]
        );

        $token = new AccessToken(["access_token" => "secret-token"]);
        $owner = $this->sut->getResourceOwner($token);

        assertThat($owner->getId(), equalTo("some-uuid"));
        assertThat($owner->getEmailAddress(), equalTo("m.mustermann@example.com"));
        assertThat($owner->getRole(), equalTo("owner"));

        assertThat($requests, countOf(1));
        assertThat($requests[0]["request"], logicalAnd(
            hasMethod("GET"),
            hasHeader("X-Access-Token", "secret-token")
        ));
    }

    public function testResourceOwnerIsRetrievedCorrectlyWhenMerged()
    {
        $requests = [];
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                "role" => "owner",
                "profile" => [
                    "id" => "some-uuid",
                    "email" => "m.mustermann@example.com",
                    "role" => "allowBeOverwrittenByHigherRole",
                ],
            ]))
        ]);
        $handler = HandlerStack::create($mock);
        $handler->push(Middleware::history($requests));
        $client = new Client(["handler" => $handler]);

        $this->sut = new SpacesProvider(
            new StaticOptions("https://signup.spaces.example", "some-space-id", "spaces.de/oauth/test", true),
            new StaticContext("https://application.example/oauth-redir"),
            [],
            ["httpClient" => $client]
        );

        $token = new AccessToken(["access_token" => "secret-token"]);
        $owner = $this->sut->getResourceOwner($token);

        assertThat($owner->getId(), equalTo("some-uuid"));
        assertThat($owner->getEmailAddress(), equalTo("m.mustermann@example.com"));
        assertThat($owner->getRole(), equalTo("owner"));

        assertThat($requests, countOf(1));
        assertThat($requests[0]["request"], logicalAnd(
            hasMethod("GET"),
            hasHeader("X-Access-Token", "secret-token")
        ));
    }
}