/*
  $Id$
  part of TeeBX(R) VoIP communication platform. http://www.teebx.com
  released under the terms of the GNU Affero General Public License ver. 3.
  Copyright(C) 2013 - 2014 Giovanni Vallesi.
  - look at TeeBX website to get details about license
*/

function doExec(url, paramsQuery, evGlobal, expectValue, targetContainer, poll, newId)
{
	var ret;

	if (newId == null)
	{
		var newId = appendElement(targetContainer);
	}

	return jQuery.ajax({
		type: 'POST',
		url: url,
		global: evGlobal,
		cache: false,
		async: false,
		timeout: 2000,
		data: paramsQuery,
		dataType: 'json',
		success: function(data)
		{
			jQuery(newId).html(data.msg);
			ret = data.retval;
			if (ret == expectValue)
			{
				jQuery(newId).removeClass('state_wait');
				jQuery(newId).addClass('state_done');
			}
			else if (ret == null)
			{
				jQuery(newId).removeClass('state_wait');
				jQuery(newId).addClass('state_error');
			}
		},
		complete: function(data)
		{
			if (poll & (ret != expectValue))
			{
				setTimeout(function()
				{
					doExec(url, paramsQuery, evGlobal, expectValue, targetContainer, poll, newId)
				}, 5000)
			}
		},
		failure: function(data)
		{
			jQuery(newId).html('Error!');
			jQuery(newId).removeClass('state_wait');
			jQuery(newId).addClass('state_error');
		}
	});
}

function pollHttp(pollTime, timeOut, timeStarted)
{
	if (timeStarted == null)
	{
		var timeStarted = Date.now();
	}

	return jQuery.ajax({
		type: 'HEAD',
		url: '/',
		cache: false,
		async: false,
		timeout: 2500,
		complete: function(jqXHR, textStatus)
		{
			if (((Date.now() - timeStarted) < timeOut) & (jqXHR.status == 0))
			{
				setTimeout(function()
				{
					pollHttp(pollTime, timeOut, timeStarted);
				}, pollTime);
			}
		}
	});
}

function appendElement(updateContainer)
{
	var newId = 'msg' + Date.now() + Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
	var wait = '<div class="state_msg state_wait" id="' + newId + '"> ...</div>';


	jQuery(updateContainer).append(wait);
	return '#' + newId;
}

function getFieldsInParent(parentElem)
{
	var params = {};
	// checkboxes only at this time
	jQuery(parentElem + ' input:checked').each(function()
	{
		params[jQuery(this).attr('id')] = jQuery(this).val();
	});
	return params;
}

function buildParamsQuery(paramsObj)
{
	var params = '';

	for (var property in paramsObj)
	{
		if (paramsObj.hasOwnProperty(property))
		{
			params += '&' + property + '=' + paramsObj[property];
		}
	}
	return params;
}

