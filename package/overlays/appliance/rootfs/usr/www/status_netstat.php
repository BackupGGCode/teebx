<?php
/*
  $Id$
part of BoneOS build platform (http://www.teebx.com/)
Copyright(C) 2010 - 2014 Giovanni Vallesi (http://www.teebx.com).
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
define('INCLUDE_JSCRIPTS', 'flot/jquery.flot.js|flot/excanvas.js|flot/jquery.flot.time.js|flot/jquery.flot.axislabels.js');
$pgtitle = array(_('Status'), _('Network Traffic'));

include('fbegin.inc');
?>
<div align="center">
<?php echo _('Port'), ' ', $config['interfaces']['lan']['if']; ?>
<div align="left" class="msgwait" id="waiting" style="width:98%">
	<img alt="..." src="img/ajax_busy_round.gif" height="16" width="16">
	<?php echo _('Collecting initial data, please wait'); ?>
</div>
</div>
<div align="center">
	<div class="chartnetwork" id="eth0tx" style="width:98%;height:250px"></div>
	<div class="chartnetwork" id="eth0rx" style="width:98%;height:250px"></div>
</div>
<?php
include('fend.inc');
?>
<script type="text/javascript">
var device = {
	collect: {},
	current: {
		eth0: {
			dataset: [],
			bpsTx: [],
			bpsRx: [],
			pktsTx: [],
			pktsRx: []
		}
	},
	plot: {
		initialized: false,
		totalPoints: 300,
		updateInterval: 1000,
		xTickSize: [20, 'second'],
		yTicks: [1000,10000,100000,1000000,10000000,100000000,1000000000]
	}
};

var optsTx = {
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
			ticks: device.plot.yTicks,
			transform: logTransform,
			tickFormatter: formatBitrate,
			min: 0,
			axisLabel: 'Tx bitrate',
			axisLabelUseCanvas: true,
			axisLabelFontSizePixels: 12,
			axisLabelFontFamily: 'Verdana, Arial',
			axisLabelPadding: 6
		}, {
			min: 0,
			position: 'right',
			axisLabel: 'Tx packets',
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

var optsRx = {
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
			ticks: device.plot.yTicks,
			transform: logTransform,
			tickFormatter: formatBitrate,
			min: 0,
			axisLabel: 'Rx bitrate',
			axisLabelUseCanvas: true,
			axisLabelFontSizePixels: 12,
			axisLabelFontFamily: 'Verdana, Arial',
			axisLabelPadding: 6
		}, {
			min: 0,
			position: 'right',
			axisLabel: 'Rx packets',
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

function formatBitrate(intVal, axis)
{
	var suffix = '';

	switch(true)
	{
		case intVal > 0 && intVal < 1000:
			suffix = ' bps';
			break;
		case intVal >= 1000 && intVal < 1000000:
			suffix = ' kbps';
			intVal = intVal / 1000;
			break;
		case intVal >= 1000000 && intVal < 1000000000:
			suffix = ' Mbps';
			intVal = intVal / 1000000;
			break;
		case intVal >= 1000000000:
			suffix = ' Gbps';
			intVal = intVal / 1000000000;
			break;
	}
	return intVal + suffix;
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

function getData()
{
	jQuery.ajax({
		async: false,
		cache: false,
		type: 'POST',
		url: '/sysinfo.php',
		data: 'job[]=netall',
		dataType: 'json',
		success: function (_data){
			if (!device.plot.initialized)
			{
				device.collect = _data;
				device.plot.initialized = true;
			}
			else
			{
				update(_data);
				if (device.current.eth0.bpsTx.length == 3)
				{
					jQuery('#waiting').remove();
				}
			}
		},
		complete: function () {
			setTimeout(getData, device.plot.updateInterval);
		}
	});
}

function update(_data)
{
	var currentTx;
	var currentRx;
	var currentPktsTx;
	var currentPktsRx;
	var realTick;
	var variance;
	var timeFrame = 1000;

	realTick = _data.time - device.collect.time;
	variance = (timeFrame / realTick);

	// adjust values storage array elements to allowed maximum set by totalPoints
	// (assuming all of equal length)
	while (device.current.eth0.bpsTx.length >= device.plot.totalPoints)
	{
		device.current.eth0.bpsTx.shift();
		device.current.eth0.bpsRx.shift();
		device.current.eth0.pktsTx.shift();
		device.current.eth0.pktsRx.shift();
	}

	currentBpsTx = (_data.net.eth0.tx_bits - device.collect.net.eth0.tx_bits) * variance;
	device.current.eth0.bpsTx.push([_data.time, currentBpsTx]);
	currentBpsRx = (_data.net.eth0.rx_bits - device.collect.net.eth0.rx_bits) * variance;
	device.current.eth0.bpsRx.push([_data.time, currentBpsRx]);
	currentPktsTx = (_data.net.eth0.tx_pkts - device.collect.net.eth0.tx_pkts) * variance;
	device.current.eth0.pktsTx.push([_data.time, currentPktsTx]);
	currentPktsRx = (_data.net.eth0.rx_pkts - device.collect.net.eth0.rx_pkts) * variance;
	device.current.eth0.pktsRx.push([_data.time, currentPktsRx]);

	// store actual data to be used as back reference in the next run
	device.collect = _data;

	device.current.eth0.dataset = [
		{label: 'b/S', data: device.current.eth0.bpsTx, lines: {fill: true, lineWidth: 1.2}, color: '#FFB449'},
		{label: 'p/S', data: device.current.eth0.pktsTx, points: {show: true}, color: '#103C5E', yaxis: 2}
	];

	jQuery.plot(jQuery('#eth0tx'), device.current.eth0.dataset, optsTx);

	device.current.eth0.dataset = [
		{label: 'b/S', data: device.current.eth0.bpsRx, lines: {fill: true, lineWidth: 1.2}, color: '#FFB449'},
		{label: 'p/S', data: device.current.eth0.pktsRx, points: {show: true}, color: '#103C5E', yaxis: 2}
	];
	jQuery.plot(jQuery('#eth0rx'), device.current.eth0.dataset, optsRx);
}


jQuery(document).ready(function ()
{
	if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) )
	{
		device.plot.totalPoints = 120;
	}
	getData();
});
</script>
