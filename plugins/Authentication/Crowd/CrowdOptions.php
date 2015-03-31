<?php
/**
Copyright 2015 HONGFA TANG (Ericsson)
 */

require_once(ROOT_DIR . '/lib/Config/namespace.php');

class CrowdOptions
{
	public function __construct()
	{
		require_once(dirname(__FILE__) . '/CrowdConfig.php');

		Configuration::Instance()->Register(dirname(__FILE__) . '/Crowd.config.php', CrowdConfig::CONFIG_ID);
	}

	private function GetConfig($keyName, $converter = null)
	{
		return Configuration::Instance()->File(CrowdConfig::CONFIG_ID)->GetKey($keyName, $converter);
	}

	public function CrowdServerUrl()
	{
		return $this->GetConfig(CrowdConfig::CROWD_SERVER_URL);
	}

	public function ApplicationName()
	{
		return $this->GetConfig(CrowdConfig::CROWD_APP_BOOKED_NAME);
	}

	public function ApplicationPassword()
	{
		return $this->GetConfig(CrowdConfig::CROWD_APP_BOOKED_PASSWORD);
	}
	
	public function CrowdCookieDomainName()
	{
		return $this->GetConfig(CrowdConfig::CROWD_COOKIE_DOMAIN_NAME);
	}

}

?>