<?php

namespace TuemmlerKon\OpenFireRestApi\Entity;

class User {

	/**
	 * The username of the user (the name before @yourdomain.tld)
	 *
	 * Optional: NO
	 *
	 * @var string $username
	 */
	private $username = '';
	/**
	 * The name of the user
	 *
	 * Optional: YES
	 *
	 * @var string $name
	 */
	private $name = '';
	/**
	 * The email of the user
	 *
	 * Optional: YES
	 *
	 * @var string $email
	 */
	private $email = '';
	/**
	 * The password of the user
	 *
	 * Optional: NO
	 *
	 * @var string $password
	 */
	private $password = '';
	#############################################################
	####################    public methods   ####################
	#############################################################

	/**
	 * User constructor.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $name
	 * @param string $email
	 */
	public function __construct($username, $password, $name='', $email='') {
		$this->setUsername($username);
		$this->setPassword($password);
		$this->setName($name);
		$this->setEmail($email);
	}

	/**
	 * Creates an array from the basic userdata
	 * @return array
	 */
	public function toArray($excludePassword=false) {
		$ret = array(
			'username' 	=> $this->getUsername(),
			'name' 		=> $this->getName(),
			'email' 	=> $this->getEmail(),
		);
		//in case you update your user, its not nescessary to send the password
		if(!$excludePassword) {
			$ret['password'] = $this->getPassword();
		}

		return $ret;
	}

	/**
	 * Creates a new user from an stdClass (this one will be retreived from OpenfireApi)
	 *
	 * @param \stdClass $userObject
	 *
	 * @return User
	 */
	public static function createFromObjectClass(\stdClass $userObject) {
		return new User($userObject->username, '', $userObject->name, $userObject->email);
	}

	#############################################################
	#################### Getters and Setters ####################
	#############################################################
	/**
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @param string $username
	 *
	 * @return User
	 */
	public function setUsername($username) {
		$this->username = $username;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 *
	 * @return User
	 */
	public function setName($name) {
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @param string $email
	 *
	 * @return User
	 */
	public function setEmail($email) {
		$this->email = $email;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @param string $password
	 *
	 * @return User
	 */
	public function setPassword($password) {
		$this->password = $password;

		return $this;
	}


}