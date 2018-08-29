<?php
namespace Mw\Spaces\OAuth2\Tests;

use Mw\Spaces\OAuth2\SpacesResourceOwner;
use PHPUnit\Framework\TestCase;

class SpacesResourceOwnerTest extends TestCase
{
    private function buildOwner($role = "owner", $allowSupportLogin = true)
    {
        return new SpacesResourceOwner([
            "role" => $role,
            "firstName" => "Max",
            "lastName" => "Mustermann",
            "email" => "max@mustermann.example",
            "id" => "85b3b4a0-560e-477a-9f28-badd57fc2d01",
        ], $allowSupportLogin);
    }

    public function testOwnersHaveAdminAccess()
    {
        $resourceOwner = $this->buildOwner("owner");

        $this->assertTrue($resourceOwner->shouldHaveAdminPrivileges());
    }

    public function testMembersDoNotHaveAdminAccess()
    {
        $resourceOwner = $this->buildOwner("member");

        $this->assertFalse($resourceOwner->shouldHaveAdminPrivileges());
    }

    public function testMittwaldUserHaveAdminAccessIfEnabled()
    {
        $resourceOwner = $this->buildOwner("mittwald-support");

        $this->assertTrue($resourceOwner->shouldHaveAdminPrivileges());
    }

    public function testMittwaldUserDoNotHaveAdminAccessIfDisabled()
    {
        $resourceOwner = $this->buildOwner("mittwald-support", false);

        $this->assertFalse($resourceOwner->shouldHaveAdminPrivileges());
    }

    public function testEmailAddressIsUsedFromProfile()
    {
        $owner = $this->buildOwner();
        assertThat($owner->getEmailAddress(), equalTo("max@mustermann.example"));
    }

    public function testFullNameIsBuiltFromProfile()
    {
        $owner = $this->buildOwner();
        assertThat($owner->getFullName(), equalTo("Max Mustermann"));
    }

    public function testFullNameCanBeBuildFromFirstNameOnly()
    {
        $owner = new SpacesResourceOwner([
            "role" => "member",
            "firstName" => "Max",
            "email" => "max@mustermann.example",
            "id" => "85b3b4a0-560e-477a-9f28-badd57fc2d01",
        ], true);
        assertThat($owner->getFullName(), equalTo("Max"));
    }

    public function testFullNameCanBeBuildFromLastNameOnly()
    {
        $owner = new SpacesResourceOwner([
            "role" => "member",
            "lastName" => "Mustermann",
            "email" => "max@mustermann.example",
            "id" => "85b3b4a0-560e-477a-9f28-badd57fc2d01",
        ], true);
        assertThat($owner->getFullName(), equalTo("Mustermann"));
    }
}