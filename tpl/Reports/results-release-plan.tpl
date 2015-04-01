{*
Copyright 2012-2014 Nick Korbel

This file is part of Booked Scheduler.

Booked Scheduler is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Booked Scheduler is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Booked Scheduler.  If not, see <http://www.gnu.org/licenses/>.
*}
{if $Report->ResultCount() > 0}
	<div id="report-actions">
		<a href="#" id="btnChart">{html_image src="chart.png"}{translate key=ViewAsChart}</a> {if !$HideSave}<a href="#"
																												id="btnSaveReportPrompt">{html_image src="disk-black.png"}{translate key=SaveThisReport}</a> | {/if}
		<a href="#" id="btnCsv">{html_image src="table-export.png"}{translate key=ExportToCSV}</a> | <a href="#"
																										id="btnPrint">{html_image src="printer.png"}{translate key=Print}</a>
	</div>
	<div id="release-report-container">
		<style>
			table, th, td {
			    border: 1px solid black;
			    border-collapse: collapse;
			}
			th {
			    padding: 5px;
			    text-align: left;
			}
			td {
				width: 30px;
				height: 30px;
				text-align: center;
			}
			table#t01 {
			    width: 100%;    
			    background-color: #f1f1c1;
			}
		</style>
		<div class="release-report-month">
			<table style="width:100%">
			  <tr>
			    <th></th>
			    <td></td>
			    {for $day=1 to $DaysInTheMonth}
				    <td>{$day}</td>
				{/for}
			  </tr>
			  <tr>
			    <th rowspan="3">Level1:</th>
			    <th rowspan="2">Level2:</th>
			    {for $day=1 to $DaysInTheMonth}
				    <td></td>
				{/for}
			  </tr>
			  <tr>
			    {for $day=1 to $DaysInTheMonth}
				    <td></td>
				{/for}
			  </tr>
			  <tr>
			    <th>another level2</th>
			    {for $day=1 to $DaysInTheMonth}
				    <td></td>
				{/for}
			  </tr>
			</table>
		</div>
	</div>
	<h4>{$Report->ResultCount()} {translate key=Rows}
		{if $Definition->GetTotal() != ''}
			| {$Definition->GetTotal()} {translate key=Total}
		{/if}
	</h4>
{else}
	<h2 id="report-no-data" class="no-data" style="text-align: center;">{translate key=NoResultsFound}</h2>
{/if}

<script type="text/javascript">
	$(document).ready(function ()
	{
		$('#report-no-data, #report-results').trigger('loaded');
	});
</script>