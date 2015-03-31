<?php
/**
This is Custom Report for Telstra Proteus Booked System
 */

define('ROOT_DIR', '../../');
require_once(ROOT_DIR . 'Pages/Reports/ReleasesReportPage.php');

$roles = array(RoleLevel::APPLICATION_ADMIN, RoleLevel::GROUP_ADMIN, RoleLevel::RESOURCE_ADMIN, RoleLevel::SCHEDULE_ADMIN);
if (Configuration::Instance()->GetSectionKey(ConfigSection::REPORTS, ConfigKeys::REPORTS_ALLOW_ALL, new BooleanConverter()))
{
	$roles = array();
}

$page = new RoleRestrictedPageDecorator(new ReleasesReportPage(), $roles);
$page->PageLoad();

?>