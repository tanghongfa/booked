<?php
/**
Copyright 2015 HONGFA TANG (Ericsson)
 */

require_once(ROOT_DIR . 'lib/Application/Authentication/namespace.php');
require_once(ROOT_DIR . 'plugins/Authentication/Crowd/namespace.php');
require_once(ROOT_DIR . 'plugins/Authentication/Crowd/CrowdClient.php');

/*
* Add this lib so that i can manage User Groups
*/
require_once(ROOT_DIR . 'lib/Application/User/namespace.php');

class Crowd extends Authentication implements IAuthentication
{
	private $authToDecorate;
	private $registration;
	private $groupRepo;
	private $userRepo;

	private $crowdRequestClient;

	/*
	* The current logged in Crowd User
	*/
	private $user = null;

	/**
	 * @var CrowdOptions
	 */
	private $options;

	/**
	 * @return Registration
	 */
	private function GetRegistration()
	{
		if ($this->registration == null)
		{
			$this->registration = new Registration();
		}

		return $this->registration;
	}

	public function __construct(Authentication $authentication)
	{
		$this->options = new CrowdOptions();
		$this->authToDecorate = $authentication;
		$this->crowdRequestClient = new CrowdClient(
											$this->options->CrowdServerUrl(), 
											$this->options->ApplicationName(), 
											$this->options->ApplicationPassword());
	}
	

	public function Validate($username, $password)
	{
		Log::Debug('Attempting Crowd Validate: %s, pwd: %s', $username, $password);

		if(empty($username) || empty($password)) {
			//Try to see if he has cookie..
			$token = $this->_getCrowdSSOCookie();
			if(!empty($token)) {
				$result = $this->crowdRequestClient->validateCrowdSession($token);
				if($result) {
					$this->user = $result;
					return true;
				}
			}

		} else {

			$result = $this->crowdRequestClient->validateUserLogin($username, $password);
			if($result === false) {
				Log::Error("Failed to validate Username/password for Crowd authentication");
				return false;
			}

			$this->user = $result;
			
			$token = $this->crowdRequestClient->createCrowdSession($username);
			if($token === false) {
				Log::Error("Failed to create SSO token for this user. But will try to let the login continue");
			} else {
				$this->_setCrowdSSOCookieValue($token);
			}

			return true;	
		}

		return false;		
	}

	public function Login($username, $loginContext)
	{
		Log::Debug('Attempting Crowd login for username: %s', $username);

		if(empty($username) && isset($this->user)) {
			$username = $this->user['name'];
		}

		$this->Synchronize($username);

		$this->_autoSyncGroups($username);

		return $this->authToDecorate->Login($username, $loginContext);
	}

	public function Logout(UserSession $userSession)
	{
		Log::Debug('Attempting Crowd logout for email: %s', $userSession->UserId);

		$token = $this->_getCrowdSSOCookie();
		
		$this->_clearCrowdSSOCookie();		

		$this->crowdRequestClient->deleteCrowdSession($token);

		$this->authToDecorate->Logout($userSession);
	}

	public function AreCredentialsKnown()
	{			
		$token = $this->_getCrowdSSOCookie();

		Log::Debug('Checking Crowd Cookie %s', $token);

		//If CROWD cookie is available, then just validate the CROWD Token with CROWD Server
		if(!isset($token)) {
    		return false;
		} else {
		    return true;
		}
	}

	public function HandleLoginFailure(IAuthenticationPage $loginPage)
	{
		$this->authToDecorate->HandleLoginFailure($loginPage);
	}

	public function ShowUsernamePrompt()
	{
		return true;
	}

	public function ShowPasswordPrompt()
	{
		return true;
	}

	public function ShowPersistLoginPrompt()
	{
		return true;
	}

	public function ShowForgotPasswordPrompt()
	{
		return false;
	}

	private function Synchronize($username)
	{
		$registration = $this->GetRegistration();

		Log::Debug("trying to sync for user. %s", $this->user['email']);

		if(empty( $this->user['email'])) {
			$this->user['email'] = $this->user['firstName'] . "." . $this->user['lastName'] . "@team.telstra.com";
		}

		$registration->Synchronize(
				new AuthenticatedUser(
						$username,
						$this->user['email'],
						$this->user['firstName'],
						$this->user['lastName'],
						$username,
						Configuration::Instance()->GetKey(ConfigKeys::LANGUAGE),
						Configuration::Instance()->GetDefaultTimezone(),
						null, 
						null,
						null));
	}

	/**
	* This function will groups information for specified user from Crowd, and then use Group Name to match all the Groups 
	* defined inside Booked system, and then add this user to those matched groups in Booked System.
	*/
	private function _autoSyncGroups($username) {
		$groupsNames = $this->crowdRequestClient->getUserGroups($username);
		if(count($groupsNames)) {

			$localGroupIds = $this->_getLocalGroupIds($groupsNames);

			if(count($localGroupIds)) {
				$userRepo = $this->_getUserRepository();

				$manageUserFactory = new ManageUsersServiceFactory();
				$manageUserService = $manageUserFactory->CreateAdmin();

				$user = $userRepo->LoadByUsername($username);
				$manageUserService->ChangeGroups($user, $localGroupIds);	

			} else {
				Log::Debug("The Crowd Groups for user %s doesn't matching any local groups configured in Booked", $username);
			}
			
		} else {
			Log::Debug("User %s doesn't belong to any group. Please check Crowd Configuration for him.", $username);
		}	
	}

	private function _getGruopRepository() {
		if($this->groupRepo == null) {
			$this->groupRepo = new GroupRepository();
		}

		return $this->groupRepo;
	}

	private function _getUserRepository() {
		if($this->userRepo == null) {
			$this->userRepo = new UserRepository();
		}

		return $this->userRepo;
	}

	private function _getLocalGroupIds($groupNames) {
		$groupRepo = $this->_getGruopRepository();

		$groupIds = array();
		$localGroupList = $groupRepo->GetList()->Results();

		foreach ($localGroupList as $group) {			
		    if(in_array($group->Name(), $groupNames)) {
		    	array_push($groupIds, $group->Id());
		    }
		}

		return $groupIds;
	}

	private function _getCrowdSSOCookie() {
		return $_COOKIE['crowd_token_key'];
	}

	private function _setCrowdSSOCookieValue($token) {
		setcookie('crowd.token_key', $token, 0, "/", $this->options->CrowdCookieDomainName());
	}

	private function _clearCrowdSSOCookie() {		
		setcookie('crowd.token_key', null, -1, "/", $this->options->CrowdCookieDomainName());
	}
}

?>