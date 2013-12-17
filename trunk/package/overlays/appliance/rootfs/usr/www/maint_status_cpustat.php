<?php
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

require('guiconfig.inc');

// define some variables referenced in fbegin.inc
define('INCLUDE_JSCRIPTS', 'flot/jquery.flot.js|flot/excanvas.js|flot/jquery.flot.time.js|flot/jquery.flot.axislabels.js|charts.libutil.js');
$pgtitle = array(_('Status'), _('CPU Load'));;

include('fbegin.inc');
?>
<div align="center">
<?php echo _('CPU Load'); ?>
<div align="left" class="msgwait" id="waiting" style="width:98%">
	<img alt="..." src="img/ajax_busy_round.gif" height="16" width="16">
	<?php echo _('Collecting initial data, please wait'); ?>
</div>
</div>
<div align="center">
	<div class="chartcpu" id="aggregate" style="width:98%;height:250px"></div>
</div>
<?php
include('fend.inc');
?>
<script type="text/javascript">
var device = {
	totalPoints: 300,
	dpSetup: {
		detail: {
			source: ['user', 'nice', 'system', 'idle', 'iowait', 'irq', 'softirq', 'steal', 'guest', 'guest_nice'],
			display: ['user', 'nice', 'system', 'iowait', 'irq', 'softirq'],
			label: [],
			color: ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b']
		},
		aggregate: {
			srcsum: ['user', 'nice', 'system', 'iowait', 'irq', 'softirq', 'steal', 'guest', 'guest_nice'],
			srcsub: [],
			display: [],
			label: [],
			color: ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b']
		}
	},
	collect: {},
	current: {},
	plot: {
		initialized: false,
		updateInterval: 1000,
		xTickSize: [20, 'second']
	}
};

var optsCpus = {
	series: {
		// no shadowing, faster drawing
		shadowSize: 0,
		lines: {
			lineWidth: 1.2
		},
		bars: {
			align: 'center',
			fillColor: {colors: [{opacity: 1}, {opacity: 1}]},
			barWidth: 2
		},
		points: {
			radius: 0.6,
			symbol: "circle"
		}
	},
	xaxis: {
		mode: 'time',
		tickSize: device.plot.xTickSize,
		tickFormatter: formatTimelabels
	},
	yaxes: [
		{
			min: 0,
			max: 100,
			axisLabel: '% core(s) load',
			axisLabelUseCanvas: true,
			axisLabelFontSizePixels: 12,
			axisLabelFontFamily: 'Verdana, Arial',
			axisLabelPadding: 6
		},
		{
			min: 0,
			position: 'right',
			axisLabel: '% CPU load',
			axisLabelUseCanvas: true,
			axisLabelFontSizePixels: 12,
			axisLabelFontFamily: 'Verdana, Arial',
			axisLabelPadding: 6
		}
	],
	legend: {
		noColumns: 0,
		position: 'sw'
	},
	grid: {
		backgroundColor: { colors: ['#ffffff', '#EDF5FF'] }
	}
};

var optsCore = {
	series: {
		shadowSize: 0,
		lines: {
			lineWidth: 1.2
		},
		bars: {
			align: 'center',
			fillColor: {colors: [{opacity: 1}, {opacity: 1}]},
			barWidth: 2
		},
		points: {
			radius: 0.6,
			symbol: "circle"
		}
	},
	xaxis: {
		mode: 'time',
		tickSize: device.plot.xTickSize,
		tickFormatter: formatTimelabels
	},
	yaxes: [
		{
			min: 0,
			//max: 100,
			axisLabel: 'Core load',
			axisLabelUseCanvas: true,
			axisLabelFontSizePixels: 12,
			axisLabelFontFamily: 'Verdana, Arial',
			axisLabelPadding: 6
		}
	],
	legend: {
		noColumns: 0,
		position: 'sw'
	},
	grid: {
		backgroundColor: {colors: ['#ffffff', '#EDF5FF']}
	}
};

function getData()
{
	jQuery.ajax({
		async: false,
		cache: false,
		type: 'POST',
		url: '/sysinfo.php',
		data: 'job[]=cpustats',
		dataType: 'json',
		success: function (_data){
			if (!device.plot.initialized)
			{
				initialize(_data);
			}
			else
			{
				update(_data);
				jQuery('#waiting').css('display','none');
			}
		},
		complete: function () {
			setTimeout(getData, device.plot.updateInterval);
		}
	});
}

function initialize(_data)
{
	var cores = [];
	var currCore;

	device.collect = _data;

	// iterate through cpu array to create an object for each core
	for (var i = 0, len = _data.cpu.abs.length; i < len; i++)
	{
		currCore = 'core' + i;
		cores.push(currCore);
		device.current[currCore] = new plotCollection(device.dpSetup.detail.source, device.totalPoints);
		jQuery('#aggregate').clone().insertAfter('div.chartcpu:last').attr('id', currCore);
	}

	device.dpSetup.aggregate.display = cores;
	device.current.aggregate = new plotCollection(cores, device.totalPoints);
	device.plot.initialized = true;
}

function update(_data)
{
	var coreCount;
	var currCore;

	coreCount = _data.cpu.abs.length;
	// update individual core usage details chart(s)
	for (var i = 0; i < coreCount; i++)
	{
		currCore = 'core' + i;
		device.current[currCore].updateDataPoints(
			_data.cpu.time,
			_data.cpu.abs[i],
			device.collect.cpu.abs[i],
			coreRolesUsage
		);
		//
		device.dpSetup.aggregate.label.push('core ' + (i + 1));
		device.current.aggregate.feedAccumulator(
			device.dpSetup.aggregate.srcsum,
			device.dpSetup.aggregate.srcsub,
			device.current[currCore].points,
			currCore
		);
		//
		var dataset = [];
		for (var dp = 0, len = device.dpSetup.detail.display.length; dp < len; dp++)
		{
			dataset.push({
				label: device.dpSetup.detail.display[dp],
				data: device.current[currCore].points[device.dpSetup.detail.display[dp]],
				lines: {fill: false, lineWidth: 1.2},
				color: device.dpSetup.detail.color[dp]
			})
		}
		optsCore.yaxes[0].axisLabel = '% core ' + (i + 1);
		jQuery.plot(jQuery('#' + currCore), dataset, optsCore);
	}

	// update cpu usage chart
	var dataset = [];
	for (var dp = 0, len = device.dpSetup.aggregate.display.length; dp < len; dp++)
	{
		// for each core, scale on left axis
		dataset.push({
			label: device.dpSetup.aggregate.label[dp],
			data: device.current.aggregate.points[device.dpSetup.aggregate.display[dp]],
			lines: {fill: false, lineWidth: 1.2},
			color: device.dpSetup.aggregate.color[dp]
		})
	}
// average load sum, system cpu(s), rigth axis, auto scale
	dataset.push({
		label: 'CPU',
		yaxis: 2,
		data: device.current.aggregate.getSeriesAvg(device.dpSetup.aggregate.display),
		lines: {fill: true, lineWidth: 1.2},
		color: '#fee090'
	})

	jQuery.plot(jQuery('#aggregate'), dataset, optsCpus);
	// store actual data to be used as back reference in the next run
	device.collect = _data;
}

jQuery(document).ready(function ()
{
	if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) )
	{
		device.totalPoints = 120;
	}
	getData();
});
</script>
