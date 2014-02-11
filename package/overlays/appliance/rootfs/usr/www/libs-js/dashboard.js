/*
  $Id$
  part of TeeBX(R) VoIP communication platform. http://www.teebx.com
  released under the terms of the GNU Affero General Public License ver. 3.
  Copyright(C) 2013 - 2014 Giovanni Vallesi.
  - look at TeeBX website to get details about license
*/

var pageVars = {};
pageVars.refresh = 1000;

function updateClock(step)
{
	// since we want to show server time js date object will be not used because of client time offset
	var newTimeStamp = pageVars.oldTimestamp + (step/1000);
	pageVars.oldTimestamp = newTimeStamp;

	var currHours = Math.floor(newTimeStamp / 3600 % 24);
	var currMins = Math.floor(newTimeStamp % 3600 / 60);
	var currSecs = Math.floor(newTimeStamp % 3600 % 60);
	// Pad minutes and seconds with leading zeros, if needed
	currMins = (currMins < 10 ? '0' : '') + currMins;
	currSecs = (currSecs < 10 ? '0' : '') + currSecs;
	// Zerofill hours component
	currHours = (currHours == 0) ? '00' : currHours;

	// Apply values
	jQuery("#systime").html(currHours + pageVars.timeSeparator + currMins + pageVars.timeSeparator + currSecs);
}

function getTimeSeparator()
{
	var tmp = new Date(0).toLocaleTimeString();
	return tmp.substr(tmp.length-3,1);
}

jQuery(document).ready(function()
{
	pageVars.timeSeparator = getTimeSeparator();
	pageVars.oldTimestamp = Number(jQuery('#timestamp').text());
	setInterval(function() {
		updateClock(pageVars.refresh);
	}, pageVars.refresh);
});
