<?php

namespace TuemmlerKon\OpenFireRestApi;

use GuzzleHttp\Exception\BadResponseException;
use TuemmlerKon\OpenFireRestApi\Entity\User;
use TuemmlerKon\OpenFireRestApi\Entity\Group;
use Doctrine\Common\Collections\ArrayCollection;
use GuzzleHttp\Client;

class OpenFireRestApi {
	/**
	 * Constants
	 */
	const HTTP_METHOD_GET = 'get';
	const HTTP_METHOD_POST = 'post';
	const HTTP_METHOD_PUT = 'put';
	const HTTP_METHOD_DELETE = 'delete';

	const HTTP_PROTOCOL_HTTP = 'http';
	const HTTP_PROTOCOL_HTTPS = 'https';
	//Endpoint definitions
	const ENDPOINT_USER = '/users';
	const ENDPOINT_LOCKOUTS = '/lockouts';
	const ENDPOINT_GROUPS = '/groups';

	/**
	 * @var \Exception $lastError
	 */
	private $lastError;
	/**
	 * @var string
	 */
	private $host = 'localhost';
	/**
	 * @var string
	 */
	private $port = '9090';
	/**
	 * @var string
	 */
	private $plugin = '/plugins/restapi/v1';
	/**
	 * @var string
	 */
	private $secret = '';
	/**
	 * @var boolean
	 */
	private $useSSL = true;
	/**
	 * @var boolean
	 */
	private $useBasicAuth = false;
	/**
	 * @var string
	 */
	private $basicUser = '';
	/**
	 * @var string
	 */
	private $basicPwd = '';
	/**
	 * @var Client $client
	 */
	private $client;

	/**
	 * OpenFireRestApi constructor.
	 *
	 * @param array $settings
	 */
	public function __construct($settings=array()) {
		$this->client = new Client();

		if(isset($settings['host'])) 			$this->host 		= $settings['host'];
		if(isset($settings['port'])) 			$this->port 		= $settings['port'];
		if(isset($settings['plugin'])) 			$this->plugin 		= $settings['plugin'];
		if(isset($settings['secret'])) 			$this->secret 		= $settings['secret'];
		if(isset($settings['useSSL'])) 			$this->useSSL 		= $settings['useSSL'];
		if(isset($settings['useBasicAuth'])) 	$this->useBasicAuth = $settings['useBasicAuth'];
		if(isset($settings['basicUser'])) 		$this->basicUser 	= $settings['basicUser'];
		if(isset($settings['basicPwd'])) 		$this->basicPwd 	= $settings['basicPwd'];
	}

	/**
	 * Make the request and analyze the result
	 *
	 * @param   string $type     Request method
	 * @param   string $endpoint Api request endpoint
	 * @param   array  $params   Parameters
	 *
	 * @return  array|false                     Array with data or error, or False when something went fully wrong
	 */
	protected function doRequest($type, $endpoint, $params = array()) {
		//generate baseurl (http or https)
		$base = ($this->useSSL) ? self::HTTP_PROTOCOL_HTTPS : self::HTTP_PROTOCOL_HTTP;
		//generate final url for request ($endpoint should start with /)
		$url = $base . "://" . $this->host . ":" . $this->port . $this->plugin . $endpoint;

		//check which type of authorization we should use
		if ($this->useBasicAuth) {
			$auth = 'Basic ' . base64_encode($this->basicUser . ':' . $this->basicPwd);
		} else {
			$auth = $this->secret;
		}
		//default headers
		$headers = array(
			'Accept'        => 'application/json',
			'Authorization' => $auth
		);

		$body = json_encode($params);

		switch ($type) {
			case self::HTTP_METHOD_GET:
				$result = $this->client->get($url, compact('headers'));
				break;
			case self::HTTP_METHOD_POST:
				$headers += ['Content-Type' => 'application/json'];
				$result  = $this->client->post($url, compact('headers', 'body'));
				break;
			case self::HTTP_METHOD_DELETE:
				$headers += ['Content-Type' => 'application/json'];
				$result  = $this->client->delete($url, compact('headers', 'body'));
				break;
			case self::HTTP_METHOD_PUT:
				$headers += ['Content-Type' => 'application/json'];
				$result  = $this->client->put($url, compact('headers', 'body'));
				break;
			default:
				$result = null;
				break;
		}

		if ($result->getStatusCode() == 200 || $result->getStatusCode() == 201) {
			return array('status'  => true,
						 'message' => json_decode($result->getBody())
			);
		}

		return array('status'  => false,
					 'message' => json_decode($result->getBody())
		);

	}
	#############################################################
	####################    helper methods   ####################
	#############################################################

	/**
	 * You can retreive the last occured exeption with this method
	 *
	 * @return \Exception
	 */
	public function getLastError() {
		return $this->lastError;
	}

	/**
	 * @param \Exception $e
	 */
	private function setLastError(\Exception $e) {
		$this->lastError = $e;
	}

	#############################################################
	##################    user based methods   ##################
	#############################################################
	/**
	 * Get all registered users
	 *
	 * @return ArrayCollection|false Returns all users within an ArrayCollection in error case false
	 */
	public function getUsers() {
		try {
			$userCollection = new ArrayCollection();
			$endpoint 		= self::ENDPOINT_USER;
			$result 		= $this->doRequest(self::HTTP_METHOD_GET, $endpoint);
			//go through every element received by the API
			foreach ($result['message']->user as $userStdObject) {
				$userCollection->add(User::createFromObjectClass($userStdObject));
			}
			//return received data
			return $userCollection;
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	/**
	 * Get information for a specified user
	 *
	 * @param $username
	 *
	 * @return false|User ArrayCollection when there are results and false if there was an error
	 */
	public function getUser($username) {
		try {
			$endpoint 	= self::ENDPOINT_USER.'/'.$username;
			$result 	= $this->doRequest(self::HTTP_METHOD_GET, $endpoint);
			//return received data
			return User::createFromObjectClass($result['message']);
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	/**
	 * Searches database for an specific user. Returns an ArrayCollection with one or more entries
	 * on fault, method returns false
	 *
	 * @param $username
	 *
	 * @return bool|ArrayCollection
	 */
	public function searchUser($username) {
		try {
			$userCollection = new ArrayCollection();
			$endpoint 		= self::ENDPOINT_USER.'?search='.$username;
			$result 		= $this->doRequest(self::HTTP_METHOD_GET, $endpoint);
			//check if there was an result, if not return false
			if($result['message']->user == NULL) {
				return false;
			}
			//check if the return is an array or not. In case of non array wrap with array() so we can always return an ArrayCollection
			if(!is_array($result['message']->user)) {
				$result['message']->user = array($result['message']->user);
			}
			//go through every element received by the API
			foreach (is_array($result['message']->user)?$result['message']->user:array($result['message']->user) as $userStdObject) {
				$userCollection->add(User::createFromObjectClass($userStdObject));
			}
			//return received data
			return $userCollection;
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	/**
	 * Creates a new user from an User object
	 * you can create one, for example, by calling createUser(new User('username', 'password', 'fullName', 'email'))
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function createUser(User $user) {
		try {
			$endpoint = self::ENDPOINT_USER;
			$this->doRequest(self::HTTP_METHOD_POST, $endpoint, $user->toArray());
			//if there is no exception return success
			return true;
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	/**
	 * Removes a user from OpenFire
	 *
	 * @param $username
	 *
	 * @return bool
	 */
	public function deleteUser($username) {
		try {
			$endpoint = self::ENDPOINT_USER.'/'.$username;
			$this->doRequest(self::HTTP_METHOD_DELETE, $endpoint);
			//if successfull
			return true;
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	/**
	 * Updates an user on OpenFire
	 *
	 * Important: It's not possible to change the password on this way
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function updateUser(User $user) {
		try {
			$endpoint = self::ENDPOINT_USER.'/'.$user->getUsername();
			$this->doRequest(self::HTTP_METHOD_PUT, $endpoint, $user->toArray(true));

			return true;
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	#############################################################
	#############  lock/unlock user based methods   #############
	#############################################################

	/**
	 * @param $username
	 *
	 * @return bool
	 */
	public function lockUserByUsername($username) {
		return $this->lockUser(new User($username, ''));
	}

	/**
	 * @param $username
	 *
	 * @return bool
	 */
	public function unlockUserByUsername($username) {
		return $this->unlockUser(new User($username, ''));
	}

	/**
	 * locks/Disables an OpenFire user
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function lockUser(User $user) {
		try {
			$endpoint = self::ENDPOINT_LOCKOUTS.'/'.$user->getUsername();
			$this->doRequest(self::HTTP_METHOD_POST, $endpoint);

			return true;
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}


	/**
	 * unlocks/Enables an OpenFire user
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function unlockUser(User $user) {
		try {
			$endpoint = self::ENDPOINT_LOCKOUTS . '/' . $user->getUsername();
			$this->doRequest(self::HTTP_METHOD_DELETE, $endpoint);

			return true;
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	#############################################################
	###################  group based methods   ##################
	#############################################################

	/**
	 * Retreives all associated groups for a user
	 *
	 * @param User $user
	 *
	 * @return bool|ArrayCollection
	 */
	public function getUserGroups(User $user) {
		try {
			$groups		= new ArrayCollection();
			$endpoint 	= self::ENDPOINT_USER.'/'.$user->getUsername().self::ENDPOINT_GROUPS;
			$result		= $this->doRequest(self::HTTP_METHOD_GET, $endpoint);
			//check if there is an result
			if(!isset($result['message']->groupname)) {
				return $groups;
			}
			//check if there is only one result
			if(!is_array($result['message']->groupname)) {
				$result['message']->groupname = array($result['message']->groupname);
			}
			//go through every group (OpenFire returns here an array)
			foreach ($result['message']->groupname as $group) {
				$groups->add(new Group($group)); //Here is no description set, because the OpenFire Api only returns the groupname
			}
			//return the groups as ArrayCollection
			return $groups;
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	/**
	 * Returns all groups in an ArrayCollection or an empty collection if nothing was found
	 * Returns false on error
	 *
	 * @return bool|ArrayCollection
	 */
	public function getGroups() {
		try {
			$groups 	= new ArrayCollection();
			$endpoint 	= self::ENDPOINT_GROUPS;
			$result 	= $this->doRequest(self::HTTP_METHOD_GET, $endpoint);
			//check if there was an result, if not return false
			if(!isset($result['message']->group)) {
				return $groups;
			}
			//check if the return is an array or not. In case of non array wrap with array() so we can always return an ArrayCollection
			if(!is_array($result['message']->group)) {
				$result['message']->group = array($result['message']->group);
			}
			//go through every element received by the API
			foreach ($result['message']->group as $groupObject) {
				$groups->add(Group::createFromObjectClass($groupObject));
			}
			//return received data
			return $groups;
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	/**
	 * Returns a specific group by Group object
	 *
	 * @param Group $group
	 *
	 * @return bool|Group
	 */
	public function getGroup(Group $group) {
		try {
			$endpoint 	= self::ENDPOINT_GROUPS.'/' . $group->getGroupname();
			$result 	= $this->doRequest(self::HTTP_METHOD_GET, $endpoint);

			return Group::createFromObjectClass($result['message']);
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	/**
	 * Returns a specific group by groupname
	 *
	 * @param $groupname
	 *
	 * @return bool|Group
	 */
	public function getGroupByGroupname($groupname) {
		return $this->getGroup(new Group($groupname));
	}

	/**
	 * Create a group by Group object
	 *
	 * @param Group $group
	 *
	 * @return bool
	 */
	public function createGroup(Group $group) {
		try {
			$endpoint = self::ENDPOINT_GROUPS;
			$this->doRequest(self::HTTP_METHOD_POST, $endpoint, $group->toArray());

			return true;
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	/**
	 * Create a group by groupname
	 *
	 * @param string $groupname
	 * @param string $description
	 *
	 * @return bool
	 *
	 */
	public function createGroupByName($groupname, $description="") {
		return $this->createGroup(new Group($groupname, $description));
	}

	/**
	 * Delete a group by Group object
	 *
	 * @param Group $group
	 *
	 * @return bool
	 */
	public function deleteGroup(Group $group) {
		try {
			$endpoint = self::ENDPOINT_GROUPS.'/'.$group->getGroupname();
			$this->doRequest(self::HTTP_METHOD_DELETE, $endpoint);

			return true;
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	/**
	 * Delete a group by Group object
	 *
	 * @param $groupname
	 *
	 * @return bool
	 */
	public function deleteGroupByName($groupname) {
		return $this->deleteGroup(new Group($groupname));
	}

	/**
	 * @param Group $group
	 *
	 * @return bool
	 */
	public function updateGroup(Group $group) {
		try {
			$endpoint = self::ENDPOINT_GROUPS.'/'.$group->getGroupname();
			$this->doRequest(self::HTTP_METHOD_PUT, $endpoint, $group->toArray());

			return true;
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	/**
	 * Update a group by Group name
	 *
	 * Note: Description is required here
	 *
	 * @param string $groupname
	 * @param string $description
	 *
	 * @return bool
	 */
	public function updateGroupByName($groupname, $description) {
		return $this->updateGroup(new Group($groupname, $description));
	}

	#############################################################
	###############  group to user based methods   ##############
	#############################################################

	/**
	 * Adds Multiple groups to an specific user
	 * @param User            $user
	 * @param ArrayCollection $groups
	 *
	 * @return bool
	 */
	public function addUserToGroups(User $user, ArrayCollection $groups) {
		try {
			$endpoint 	= self::ENDPOINT_USER.'/'.$user->getUsername().self::ENDPOINT_GROUPS;
			$this->doRequest(self::HTTP_METHOD_POST, $endpoint, array('groupname' => $groups->toArray()));

			return true;
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	/**
	 * Adds an user to an group by Group object
	 *
	 * @param User  $user
	 * @param Group $group
	 *
	 * @return bool
	 */
	public function addUserToGroup(User $user, Group $group) {
		return $this->addUserToGroups($user, new ArrayCollection($group->getGroupname()));
	}

	/**
	 * Adds an user to an group by a given groupname
	 *
	 * @param User $user
	 * @param string $groupname
	 *
	 * @return bool
	 */
	public function addUserToGroupByGroupName(User $user, $groupname) {
		return $this->addUserToGroup($user, new Group($groupname));
	}

	/**
	 * Removes multiple groups from an specific user
	 * @param User            $user
	 * @param ArrayCollection $groups
	 *
	 * @return bool
	 */
	public function removeUserFromGroups(User $user, ArrayCollection $groups) {
		try {
			$endpoint 	= self::ENDPOINT_USER.'/'.$user->getUsername().self::ENDPOINT_GROUPS;
			$this->doRequest(self::HTTP_METHOD_DELETE, $endpoint, array('groupname' => $groups->toArray()));

			return true;
		}
		catch (BadResponseException $e) {
			//save last exception
			$this->setLastError($e);
			//Error case
			return false;
		}
	}

	/**
	 * Adds an user to an group by Group object
	 *
	 * @param User  $user
	 * @param Group $group
	 *
	 * @return bool
	 */
	public function removeUserFromGroup(User $user, Group $group) {
		return $this->removeUserFromGroups($user, new ArrayCollection($group->getGroupname()));
	}

	/**
	 * Adds an user to an group by a given groupname
	 *
	 * @param User $user
	 * @param string $groupname
	 *
	 * @return bool
	 */
	public function removeUserFromGroupByGroupName(User $user, $groupname) {
		return $this->removeUserFromGroup($user, new Group($groupname));
	}


	#############################################################
	#####################  further methods   ####################
	#############################################################


	//ToDo: Implement more methods for rosters and chatrooms
}
