<?php
namespace Mw\Spaces\OAuth2;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * A special resource owner for the Mittwald SPACES identity provider
 *
 * @package    Mittwald\SpacesAuth
 * @subpackage OAuth2
 * @author     Martin Helmich <m.helmich@mittwald.de>
 */
class SpacesResourceOwner implements ResourceOwnerInterface
{
    private $response;
    private $allowSupportLogin;

    /**
     * Creates a new SPACES resource owner.
     *
     * @param array  $response          The raw response data
     * @param bool   $allowSupportLogin Allow customer support login
     */
    public function __construct(array $response, $allowSupportLogin = true)
    {
        $this->response = $response;
        $this->allowSupportLogin = $allowSupportLogin;
    }

    /**
     * Gets the user role that the resource owner has within the current TYPO3 project
     *
     * @return string The user role (for example, "owner" or "mittwald-support")
     */
    public function getRole()
    {
        return $this->response["role"];
    }

    /**
     * Gets the user's email address
     *
     * @return string The email address
     */
    public function getEmailAddress()
    {
        return $this->response["email"];
    }

    /**
     * Gets the user's full name, or an empty string if not set
     *
     * @return string The user's full name, or an empty string if not set
     */
    public function getFullName()
    {
        $first = isset($this->response["firstName"]) ? $this->response["firstName"] : "";
        $last = isset($this->response["lastName"]) ? $this->response["lastName"] : "";

        return trim($first . " " . $last);
    }

    /**
     * Describes if the resource owner should receive administrator privileges in the
     * current TYPO3 project. Typically, this is determined by the user's role.
     *
     * @return bool TRUE if the user should be administrator
     */
    public function shouldHaveAdminPrivileges()
    {
        return $this->getRole() === "owner" || ($this->allowSupportLogin && $this->getRole() === "mittwald-support");
    }

    /**
     * Gets the resource owner's expiration date if set, or null otherwise.
     *
     * @return \DateTime|null The expiration date or null
     */
    public function getExpirationDate()
    {
        return isset($this->response["expires"]) ? new \DateTime($this->response["expires"]) : null;
    }

    /**
     * Gets the resource owner's ID
     *
     * @return string The resource owner ID
     */
    public function getId()
    {
        return $this->response['id'];
    }

    /**
     * Returns the resource owner's raw data array.
     *
     * @return array The resource owner's data
     */
    public function toArray()
    {
        return $this->response;
    }
}