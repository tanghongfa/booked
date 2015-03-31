{*
Custom Release Plan Report
*}
{include file='globalheader.tpl' cssFiles="css/reports.css,scripts/js/jqplot/jquery.jqplot.min.css"}

<h1>{translate key=ReleasePlanReports}</h1>
<a href="#" id="showHideCustom">{translate key=ShowHide}</a>
<fieldset id="customReportInput-container">
	<form id="customReportInput">
		<div id="custom-report-input">			
			<div class="input-set">
				<span class="label">{translate key=Range}</span>
				<input type="radio" {formname key=REPORT_RANGE} value="{Report_Range::CURRENT_MONTH}" id="current_month" checked="checked"/>
				<label for="current_month">{translate key=CurrentMonth}</label>
				<input type="radio" {formname key=REPORT_RANGE} value="{Report_Range::NEXT_TWO_MONTHS}" id="next_two_months"/>
				<label for="current_week">Next 2 months</label>
				<input type="radio" {formname key=REPORT_RANGE} value="{Report_Range::NEXT_THREE_MONTHS}" id="next_three_months"/>
				<label for="today" style="width:auto;">Next 3 months</label>
				<input type="radio" {formname key=REPORT_RANGE} value="{Report_Range::DATE_RANGE}" id="range_within"/>
				<label for="range_within" style="width:auto;">{translate key=Between}</label>
				<input type="input" class="textbox dateinput" id="startDate"/> -
				<input type="hidden" id="formattedBeginDate" {formname key=REPORT_START}/>
				<input type="input" class="textbox dateinput" id="endDate"/>
				<input type="hidden" id="formattedEndDate" {formname key=REPORT_END} />
			</div>
			<div class="input-set">
				<span class="label">{translate key=FilterBy}</span>
				<select class="textbox" {formname key=RESOURCE_ID}>
					<option value="">{translate key=AllResources}</option>
				{foreach from=$Resources item=resource}
					<option value="{$resource->GetId()}">{$resource->GetName()}</option>
				{/foreach}
				</select>				
			</div>
		</div>
		<input type="submit" value="{translate key=GetReport}" class="button" id="btnCustomReport" asyncAction=""/>
	</form>
</fieldset>

<div id="saveMessage" class="success" style="display:none">
{translate key=ReportSaved} <a href="{$Path}reports/{Pages::REPORTS_SAVED}">{translate key=MySavedReports}</a>
</div>

<div id="resultsDiv">
</div>

<div id="indicator" style="display:none; text-align: center;"><h3>{translate key=Working}
	</h3>{html_image src="admin-ajax-indicator.gif"}</div>

{include file="Reports/chart.tpl"}

<div class="dialog" id="userPopup">
{translate key=User}<a href="#" id="browseUser">Browse</a>
</div>

<div class="dialog" id="groupPopup">
{translate key=Group}<input id="group_filter" type="text" class="textbox"/>
</div>


<div class="dialog" id="saveDialog" title="{translate key=SaveThisReport}">
	<label for="saveReportName">{translate key=Name}:</label>

	<form id="saveReportForm" action="" method="post">
		<input type="text" id="saveReportName" {formname key=REPORT_NAME} class="textbox">
		<br/><br/>
		<button type="button"
				class="button save"
				id="btnSaveReport">{html_image src="disk-black.png"} {translate key='SaveThisReport'}</button>
		<button type="button" class="button cancel">{html_image src="slash.png"} {translate key='Cancel'}</button>
	</form>
</div>

{jsfile src="autocomplete.js"}
{jsfile src="ajax-helpers.js"}
{jsfile src="reports/generate-reports.js"}
{jsfile src="reports/chart.js"}

<script type="text/javascript">
	$(document).ready(function () {
		var reportOptions = {
			userAutocompleteUrl:"{$Path}ajax/autocomplete.php?type={AutoCompleteType::User}",
			groupAutocompleteUrl:"{$Path}ajax/autocomplete.php?type={AutoCompleteType::Group}",
			customReportUrl:"{$smarty.server.SCRIPT_NAME}?{QueryStringKeys::ACTION}={ReportActions::Generate}",
			printUrl:"{$smarty.server.SCRIPT_NAME}?{QueryStringKeys::ACTION}={ReportActions::PrintReport}&",
			csvUrl:"{$smarty.server.SCRIPT_NAME}?{QueryStringKeys::ACTION}={ReportActions::Csv}&",
			saveUrl:"{$smarty.server.SCRIPT_NAME}?{QueryStringKeys::ACTION}={ReportActions::Save}"
		};

		var reports = new GenerateReports(reportOptions);
		reports.init();
	});
</script>

{control type="DatePickerSetupControl" ControlId="startDate" AltId="formattedBeginDate"}
{control type="DatePickerSetupControl" ControlId="endDate" AltId="formattedEndDate"}

{include file='globalfooter.tpl'}