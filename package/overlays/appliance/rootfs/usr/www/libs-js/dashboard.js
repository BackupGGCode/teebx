/*
  $Id$
  part of TeeBX(R) VoIP communication platform. http://www.teebx.com
  released under the terms of the GNU Affero General Public License.
  - look at TeeBX website to get details about license
*/

var pageVars = {};
pageVars.refresh = 1000;

function updateClock(step)
{
	var newTimeStamp = pageVars.oldTimestamp + step;
	var currTime = new Date(newTimeStamp);
	var currHours = currTime.getHours();
	var currMins = currTime.getMinutes();
	var currSecs = currTime.getSeconds();

	pageVars.oldTimestamp = newTimeStamp;
	// Pad minutes and seconds with leading zeros, if needed
	currMins = (currMins < 10 ? '0' : '') + currMins;
	currSecs = (currSecs < 10 ? '0' : '') + currSecs;
	// Zerofill hours component
	currHours = (currHours == 0) ? '00' : currHours;

	// Apply values
	jQuery("#systime").html(currHours + ':' + currMins + ':' + currSecs);
}

jQuery(document).ready(function()
{
	pageVars.oldTimestamp = Number(jQuery('#timestamp').text());
	setInterval(function() {
		updateClock(pageVars.refresh);
	}, pageVars.refresh);
});
