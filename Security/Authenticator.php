<?php

namespace Kdyby\Security;

use Nette;
use Nette\Security\AuthenticationException;
use Kdyby;



/**
 * Users authenticator.
 */
final class Authenticator extends Nette\Object implements Nette\Security\IAuthenticator
{

	/**
	 * Performs an authentication
	 * @param  array
	 * @return Nette\Security\IIdentity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		$username = $credentials[self::USERNAME];
		$password = $credentials[self::PASSWORD];

		$configHooks = $this->context->getService("Kdyby\\ConfigHooks");

		$identity = new $configHooks['Nette\Security\IIdentity']($username, $password);
		$identity->addRoles(array(
				new Kdyby\Security\Acl\Role('registered'),
				new Kdyby\Security\Acl\Role('admin')
			));

		return $identity;


//		//$row = dibi::fetch('SELECT * FROM users WHERE login=%s', $username);
//
//		if (!$row) {
//			throw new AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
//		}
//
//		if ($row->password !== $password) {
//			throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
//		}
//
//		unset($row->password);
//		return new Identity($row->id, $row->role, $row);
	}



	public function getContext()
	{
		return Nette\Environment::getApplication()->getContext();
	}

}