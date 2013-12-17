/*
  $Id$
part of BoneOS build platform (http://www.teebx.com/)
Copyright(C) 2010 - 2013 Giovanni Vallesi (http://www.teebx.com).
All rights reserved.

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Affero General Public License as
  published by the Free Software Foundation, either version 3 of the
  License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Affero General Public License for more details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see [http://www.gnu.org/licenses/].

- BoneOS source code is available via svn at [http://svn.code.sf.net/p/boneos/code/].
- look at TeeBX website [http://www.teebx.com] to get details about license.
*/

function plotCollection(arrDataPointNames, maxPoints)
{
	this.maxPoints = maxPoints;
	this.points = new dataPoints(arrDataPointNames);

	//
	this.trim = trim;
	this.updateDataPoints = updateDataPoints;
	this.feedAccumulator = feedAccumulator;
	this.getSeriesAvg = getSeriesAvg;

	function dataPoints(arrDataPointNames)
	{
		// iterate through passed array, assign a property for each element
		for (var i = 0, len = arrDataPointNames.length; i < len; i++)
		{
			this[arrDataPointNames[i]] = [];
		}
	}

	function updateDataPoints(currTimeStamp, newData, oldData, cbFunction)
	{
		var tmp = newData;

		if (typeof cbFunction === 'function')
		{
			tmp = cbFunction(newData, oldData);
		}

		for (var prop in this.points)
		{
			if (this.points.hasOwnProperty(prop))
			{
				this.points[prop].push([currTimeStamp, tmp[prop]]);
			}
		}
		this.trim();
	}

	function feedAccumulator(arrFldToSum, arrFldToSub, srcObj, dstProp)
	{
		var sum = 0;

		for (var prop in srcObj)
		{
			var pos = srcObj[prop].length - 1;
			var timeStamp = srcObj[prop][pos][0];

			if (arrFldToSum.indexOf(prop) != -1)
			{
				sum = sum + srcObj[prop][pos][1];
			}
			else if (arrFldToSub.indexOf(prop) != -1)
			{
				sum = sum - srcObj[prop][pos][1];
			}
		}
		this.points[dstProp].push([timeStamp, sum]);
		this.trim();
	}

	function getSeriesAvg(arrSeries)
	{
		var dataset = [];
		var dpLengths = [];
		var shortestDp = 0;
		var serCount = arrSeries.length;

		// find the shortest datapoint to stay in a safe place, although should be all equal.
		for (var dp = 0; dp < serCount; dp++)
		{
			if (this.points.hasOwnProperty(arrSeries[dp]))
			{
				dpLengths.push(this.points[arrSeries[dp]].length);
			}
		}
		if (dpLengths.length > 0)
		{
			shortestDp = Math.min.apply(null, dpLengths);
		}

		for (var dp = 0; dp < shortestDp; dp++)
		{
			sum = 0.0;
			for (var i = 0; i < serCount; i++)
			{
				sum = sum + this.points[arrSeries[i]][dp][1];
			}
			dataset.push([this.points[arrSeries[0]][dp][0], sum / serCount])
		}
		return dataset;
	}

	// adjust values storage array elements to allowed maximum set in maxPoints
	function trim()
	{
		for (var prop in this.points)
		{
			while (this.points[prop].length > this.maxPoints)
			{
				this.points[prop].shift();
			}
		}
	}
}

function coreRolesUsage(newSet, oldSet)
{
	var diff = {};
	var ceiling = newSet.totalticks - oldSet.totalticks;
	var usedTicks;
	var current;

	for (var prop in oldSet)
	{
		current = 0.0;
		usedTicks = (newSet[prop] - oldSet[prop]);
		if (usedTicks > 0)
		{
			current = 100.0 / (ceiling / usedTicks);
		}
		diff[prop] = current;
	}
	return diff;
}

function formatTimelabels(timestamp, axis)
{
	var date = new Date(timestamp);
	var nowSeconds = date.getSeconds();
	// render an x label at 20 seconds marks
	if (nowSeconds % 20 == 0)
	{
		// render hh:mm only at 0 second mark
		if (nowSeconds == 0)
		{
			return date.toLocaleTimeString();
		}
		return nowSeconds;
	} else {
		return '';
	}
}

function logTransform(intValue)
{
	return Math.log(intValue+100); //move away from zero
}