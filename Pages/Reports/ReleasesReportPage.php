<?php
/**
Custom Release Report
 */

require_once(ROOT_DIR . 'Pages/SecurePage.php');
require_once(ROOT_DIR . 'Pages/Reports/IDisplayableReportPage.php');
require_once(ROOT_DIR . 'Pages/Ajax/AutoCompletePage.php');
require_once(ROOT_DIR . 'Presenters/Reports/ReleasesPlanReportPresenter.php');

interface IReleasesReportPage extends IDisplayableReportPage, IActionPage
{
	/**
	 * @abstract
	 * @return string|Report_Usage
	 */
	public function GetUsage();

	/**
	 * @abstract
	 * @return string|Report_ResultSelection
	 */
	public function GetResultSelection();

	/**
	 * @abstract
	 * @return string|Report_GroupBy
	 */
	public function GetGroupBy();

	/**
	 * @abstract
	 * @return string|Report_Range
	 */
	public function GetRange();

	/**
	 * @abstract
	 * @return string
	 */
	public function GetStart();

	/**
	 * @abstract
	 * @return string
	 */
	public function GetEnd();

	/**
	 * @abstract
	 * @return int
	 */
	public function GetResourceId();

	/**
	 * @abstract
	 * @return int
	 */
	public function GetResourceTypeId();

	/**
	 * @abstract
	 * @return int
	 */
	public function GetAccessoryId();

	/**
	 * @abstract
	 * @return int
	 */
	public function GetScheduleId();

	/**
	 * @abstract
	 * @return int
	 */
	public function GetUserId();

	/**
	 * @return int
	 */
	public function GetParticipantId();

	/**
	 * @abstract
	 * @return int
	 */
	public function GetGroupId();

	/**
	 * @abstract
	 * @return string
	 */
	public function GetReportName();

	/**
	 * @abstract
	 * @param array|BookableResource[] $resources
	 */
	public function BindResources($resources);

	/**
	 * @abstract
	 * @param array|AccessoryDto[] $accessories
	 */
	public function BindAccessories($accessories);

	/**
	 * @abstract
	 * @param array|Schedule[] $schedules
	 */
	public function BindSchedules($schedules);

	/**
	 * @abstract
	 * @param array|GroupItemView[] $groups
	 */
	public function BindGroups($groups);

	/**
	 * @abstract
	 * @param array|ResourceType[] $resourceTypes
	 */
	public function BindResourceTypes($resourceTypes);
}

class ReleasesReportPage extends ActionPage implements IReleasesReportPage
{
	/**
	 * @var GenerateReportPresenter
	 */
	private $presenter;

	private $resourceRepo; 

	public function __construct()
	{
		parent::__construct('Reports', 1);
		$this->resourceRepo = new ResourceRepository();
		$this->presenter = new ReleasesPlanReportPresenter(
			$this,
			ServiceLocator::GetServer()->GetUserSession(),
			new ReportingService(new ReportingRepository()),
			$this->resourceRepo,
			new ScheduleRepository(),
			new GroupRepository());
	}

	/**
	 * @return void
	 */
	public function ProcessAction()
	{
		$this->presenter->ProcessAction();
	}

	/**
	 * @param $dataRequest string
	 * @return void
	 */
	public function ProcessDataRequest($dataRequest)
	{
		// no-op
	}

	/**
	 * @return void
	 */
	public function ProcessPageLoad()
	{
		$this->presenter->PageLoad();
		$this->Display('Reports/generate-release-plan-report.tpl');
	}

	/**
	 * @return string|Report_Usage
	 */
	public function GetUsage()
	{
		return $this->GetValue(FormKeys::REPORT_USAGE);
	}

	/**
	 * @return string|Report_ResultSelection
	 */
	public function GetResultSelection()
	{
		//hardcode to use LIST for now.
		return "LIST"; //$this->GetValue(FormKeys::REPORT_RESULTS);
	}

	/**
	 * @return string|Report_GroupBy
	 */
	public function GetGroupBy()
	{
		return $this->GetValue(FormKeys::REPORT_GROUPBY);
	}

	/**
	 * @return string|Report_Range
	 */
	public function GetRange()
	{
		return $this->GetValue(FormKeys::REPORT_RANGE);
	}

	/**
	 * @return string
	 */
	public function GetStart()
	{
		return $this->GetValue(FormKeys::REPORT_START);
	}

	/**
	 * @return string
	 */
	public function GetEnd()
	{
		return $this->GetValue(FormKeys::REPORT_END);
	}

	/**
	 * @return int
	 */
	public function GetResourceId()
	{
		return $this->GetValue(FormKeys::RESOURCE_ID);
	}
	
	/**
	 * @return int
	 */
	public function GetResourceTypeId()
	{
		return $this->GetValue(FormKeys::RESOURCE_TYPE_ID);
	}

	/**
	 * @return int
	 */
	public function GetScheduleId()
	{
		return $this->GetValue(FormKeys::SCHEDULE_ID);
	}

	/**
	 * @return int
	 */
	public function GetUserId()
	{
		return $this->GetValue(FormKeys::USER_ID);
	}

	/**
	 * @return int
	 */
	public function GetParticipantId()
	{
		return $this->GetValue(FormKeys::PARTICIPANT_ID);
	}

	/**
	 * @return int
	 */
	public function GetGroupId()
	{
		return $this->GetValue(FormKeys::GROUP_ID);
	}

	private function mashReportData(IReport $report) {
		//Need to mash around the data to make things working fine


	}

	private function loadResourcesInfo($resourceTypeId) {
		//$resourceTypes = 
	}

	public function BindReport(IReport $report, IReportDefinition $definition)
	{
		$this->Set('Definition', $definition);
		$this->Set('Report', $report);

		//Log::Debug("************* %s", $this->GetResourceTypeId());

		$this->Set('DaysInTheMonth', cal_days_in_month(CAL_GREGORIAN, 3, 2015));
	}

	/**
	 * @param array|BookableResource[] $resources
	 */
	public function BindResources($resources)
	{
		$this->Set('Resources', $resources);
	}

	/**
	 * @param array|AccessoryDto[] $accessories
	 */
	public function BindAccessories($accessories)
	{
		$this->Set('Accessories', $accessories);
	}

	/**
	 * @param array|Schedule[] $schedules
	 */
	public function BindSchedules($schedules)
	{
		$this->Set('Schedules', $schedules);
	}

	public function BindResourceTypes($resourceTypes)
	{
		$this->Set('ResourceTypes', $resourceTypes);
	}

	/**
	 * @return int
	 */
	public function GetAccessoryId()
	{
		return $this->GetValue(FormKeys::ACCESSORY_ID);
	}


	public function GetReportName()
	{
		return $this->GetForm(FormKeys::REPORT_NAME);
	}

	private function GetValue($key)
	{
		$postValue = $this->GetForm($key);

		if (empty($postValue))
		{
			return $this->GetQuerystring($key);
		}

		return $postValue;
	}

	public function ShowCsv()
	{
		$this->DisplayCsv('Reports/custom-csv.tpl', 'report.csv');
	}

	public function DisplayError()
	{
		$this->Display('Reports/error.tpl');
	}

	public function ShowResults()
	{
		$this->Display('Reports/results-release-plan.tpl');
	}

	public function PrintReport()
	{
		$this->Display('Reports/print-custom-report.tpl');
	}

	/**
	 * @param array|GroupItemView[] $groups
	 */
	public function BindGroups($groups)
	{
		$this->Set('Groups', $groups);
	}
}

?>