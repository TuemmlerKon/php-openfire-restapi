<?php

namespace TuemmlerKon\OpenFireRestApi\Entity;

class Group {

	/**
	 * The username of the user (the name before @yourdomain.tld)
	 *
	 * Optional: NO
	 *
	 * @var string $username
	 */
	private $groupname = '';
	/**
	 * The name of the user
	 *
	 * Optional: YES
	 *
	 * @var string $name
	 */
	private $description = '';

	#############################################################
	####################    public methods   ####################
	#############################################################

	/**
	 * User constructor.
	 *
	 * @param        $groupname
	 * @param string $description
	 *
	 */
	public function __construct($groupname, $description='') {
		$this->setGroupname($groupname);
		$this->setDescription($description);
	}

	/**
	 * Creates an array from the basic userdata
	 * @return array
	 */
	public function toArray() {
		$ret = array(
			'name' 			=> $this->getGroupname(),
			'description' 	=> $this->getDescription(),
		);

		return $ret;
	}

	/**
	 * Creates a new user from an stdClass (this one will be retreived from OpenfireApi)
	 *
	 * @param \stdClass $groupObject
	 *
	 * @return Group
	 *
	 */
	public static function createFromObjectClass(\stdClass $groupObject) {
		return new Group($groupObject->name, $groupObject->description);
	}

	#############################################################
	#################### Getters and Setters ####################
	#############################################################

	/**
	 * @return string
	 */
	public function getGroupname() {
		return $this->groupname;
	}

	/**
	 * @param string $groupname
	 *
	 * @return Group
	 */
	public function setGroupname($groupname) {
		$this->groupname = $groupname;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string $description
	 *
	 * @return Group
	 */
	public function setDescription($description) {
		$this->description = $description;

		return $this;
	}

}