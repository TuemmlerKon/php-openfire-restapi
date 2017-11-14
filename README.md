php-openfire-restapi
=====================

A simple PHP class designed to work with Openfire Rest Api plugin. It is used to remote manage the Openfire server.

## LICENSE
php-openfire-restapi is licensed under MIT style license, see LICENCE for further information.

## REQUIREMENTS
- PHP 5.4+

## INSTALLATION

### With Composer
-------------
The easiest way to install is via [composer](http://getcomposer.org/). Create the following `composer.json` file and run the `composer.phar` install command to install it.

```json
{
    "require": {
        "tuemmlerkon/php-openfire-restapi": "dev-master"
    }
}
```

## USAGE
```php
include "vendor/autoload.php";

//define your settings in an array (these are the default values) define only that ones you want to change
$settings = array(
    'host' 			=> 'localhost',
    'port' 			=> '9090',
    'plugin' 		=> '/plugins/restapi/v1',
    'secret' 		=> '',      //required when 'useBasicAuth' is set to false
    'useSSL' 		=> true,
    'useBasicAuth' 	=> false,   //if this option is set to true, you have also set 'basicUser' and 'basicPwd'
    'basicUser' 	=> '',
    'basicPwd' 		=> '',
);

// Create the Openfire Rest api object
$api = new TuemmlerKon\OpenFireRestApi\OpenFireRestApi($settings);
//You are also able to use getter ans setter methods on these settings
//e.g. $api->setEnableSSL(true);
```
For an easier handling of the returning values can use helper classes for `User` and `Group` returns.

After that you're able to use the following methods for accessing your OpenFire installation
```php
/**
 * Get all registered users
 *
 * @return ArrayCollection|false Returns all users within an ArrayCollection in error case false
 */
public function getUsers();

/**
 * Get information for a specified user
 *
 * @param $username
 *
 * @return false|User ArrayCollection when there are results and false if there was an error
 */
public function getUser($username);

/**
 * Searches database for an specific user. Returns an ArrayCollection with one or more entries
 * on fault, method returns false
 *
 * @param $username
 *
 * @return bool|ArrayCollection
 */
public function searchUser($username);

/**
 * Creates a new user from an User object
 * you can create one, for example, by calling createUser(new User('username', 'password', 'fullName', 'email'))
 *
 * @param User $user
 *
 * @return bool
 */
public function createUser(User $user);

/**
 * Removes a user from OpenFire
 *
 * @param $username
 *
 * @return bool
 */
public function deleteUser($username);

/**
 * Updates an user on OpenFire
 *
 * Important: It's not possible to change the password on this way
 *
 * @param User $user
 *
 * @return bool
 */
public function updateUser(User $user);

/**
 * @param $username
 *
 * @return bool
 */
public function lockUserByUsername($username);

/**
 * @param $username
 *
 * @return bool
 */
public function unlockUserByUsername($username);

/**
 * locks/Disables an OpenFire user
 *
 * @param User $user
 *
 * @return bool
 */
public function lockUser(User $user);

/**
 * unlocks/Enables an OpenFire user
 *
 * @param User $user
 *
 * @return bool
 */
public function unlockUser(User $user);

/**
 * Retreives all associated groups for a user
 *
 * @param User $user
 *
 * @return bool|ArrayCollection
 */
public function getUserGroups(User $user);

/**
 * Returns all groups in an ArrayCollection or an empty collection if nothing was found
 * Returns false on error
 *
 * @return bool|ArrayCollection
 */
public function getGroups();

/**
 * Returns a specific group by Group object
 *
 * @param Group $group
 *
 * @return bool|Group
 */
public function getGroup(Group $group);

/**
 * Returns a specific group by groupname
 *
 * @param $groupname
 *
 * @return bool|Group
 */
public function getGroupByGroupname($groupname);

/**
 * Create a group by Group object
 *
 * @param Group $group
 *
 * @return bool
 */
public function createGroup(Group $group);

/**
 * Create a group by groupname
 *
 * @param string $groupname
 * @param string $description
 *
 * @return bool
 *
 */
public function createGroupByName($groupname, $description="");

/**
 * Delete a group by Group object
 *
 * @param Group $group
 *
 * @return bool
 */
public function deleteGroup(Group $group);

/**
 * Delete a group by Group object
 *
 * @param $groupname
 *
 * @return bool
 */
public function deleteGroupByName($groupname);

/**
 * @param Group $group
 *
 * @return bool
 */
public function updateGroup(Group $group);

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
public function updateGroupByName($groupname, $description);

/**
 * Adds Multiple groups to an specific user
 * @param User            $user
 * @param ArrayCollection $groups
 *
 * @return bool
 */
public function addUserToGroups(User $user, ArrayCollection $groups);

/**
 * Adds an user to an group by Group object
 *
 * @param User  $user
 * @param Group $group
 *
 * @return bool
 */
public function addUserToGroup(User $user, Group $group);

/**
 * Adds an user to an group by a given groupname
 *
 * @param User $user
 * @param string $groupname
 *
 * @return bool
 */
public function addUserToGroupByGroupName(User $user, $groupname);

/**
 * Removes multiple groups from an specific user
 * @param User            $user
 * @param ArrayCollection $groups
 *
 * @return bool
 */
public function removeUserFromGroups(User $user, ArrayCollection $groups);

/**
 * Adds an user to an group by Group object
 *
 * @param User  $user
 * @param Group $group
 *
 * @return bool
 */
public function removeUserFromGroup(User $user, Group $group);

/**
 * Adds an user to an group by a given groupname
 *
 * @param User $user
 * @param string $groupname
 *
 * @return bool
 */
removeUserFromGroupByGroupName(User $user, $groupname);
```
## Development
Feel free to add new features or inform me about bugs or problems.
## CONTACT
- Mail me at konstantin@tuemmler.org
