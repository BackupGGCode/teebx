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

class sysInfo
{
	// variables initialization
	public $pSysClass = '/sys/class/';
	public $pProc = '/proc/';
	public $data = array();


	public function __construct()
	{
		$this->data['retval'] = 1;
		// errors array
		$this->data['errors'] = array();
	}

	/* this class method depends on glibc/eglibc */
	private function glibc_getKernelHz($immediateResult = true)
	{
		$result = false;
		exec('getconf CLK_TCK', $out, $cmdRes);
		if (($cmdRes == 0) & is_array($out))
		{
			$result = (int) $out[0];
		}
		$this->data['kernelhz'] = $result;
		$this->data['retval'] = $cmdRes;
	}

	/* this class method depends on glibc/eglibc */
	private function glibc_getCoreCount()
	{
		$result = false;
		exec('getconf _NPROCESSORS_CONF', $out, $cmdRes);
		if (($cmdRes == 0) & is_array($out))
		{
			$result = (int) $out[0];
		}
		$this->data['corecount'] = $result;
		$this->data['retval'] = $cmdRes;
	}

	protected function readAsFloatFromFile($fileName)
	{
		if (!file_exists($fileName))
		{
			return 0;
		}
		//
		if (($result = file_get_contents($fileName)) === false)
		{
			return 0;
		}
		$this->data['time'] = time() * 1000;
		$this->data['retval'] = 0;
		return (float) trim($result);
	}

	protected function readFileIntoArray($fileName)
	{
		if (!file_exists($fileName))
		{
			return false;
		}
		//
		if (($result = file($fileName)) === false)
		{
			return false;
		}
		$this->data['time'] = time() * 1000;
		$this->data['retval'] = 0;
		return $result;
	}

	public function getNetIfTraffic($if = 'eth0')
	{
		$this->data['net'][$if]['tx_bits'] = $this->readAsFloatFromFile("{$this->pSysClass}net/$if/statistics/tx_bytes") * 8;
		$this->data['net'][$if]['rx_bits'] = $this->readAsFloatFromFile("{$this->pSysClass}net/$if/statistics/rx_bytes") * 8;
	}

	public function getNetStats()
	{
		$stats = $this->readFileIntoArray("{$this->pProc}net/dev");
		if (!is_array($stats))
		{
			return false;
		}
		// unset the first tree array elements that contains only text headers
		unset($stats[0], $stats[1], $stats[2]);
		foreach (array_keys($stats) as $key)
		{
			$res = preg_split('/[\s:]+/', trim($stats[$key]));
			if (count($res) == 17)
			{
				// tx stats
				$this->data['net'][$res[0]]['rx_bits'] = (float) $res[1] * 8;
				$this->data['net'][$res[0]]['rx_pkts'] = (float) $res[2];
				$this->data['net'][$res[0]]['rx_errs'] = (float) $res[3];
				$this->data['net'][$res[0]]['rx_drop'] = (float) $res[4];
				$this->data['net'][$res[0]]['rx_fifo'] = (float) $res[5];
				$this->data['net'][$res[0]]['rx_frame'] = (float) $res[6];
				$this->data['net'][$res[0]]['rx_compressed'] = (float) $res[7];
				$this->data['net'][$res[0]]['rx_multicast'] = (float) $res[8];
				// rx stats
				$this->data['net'][$res[0]]['tx_bits'] = (float) $res[9] * 8;
				$this->data['net'][$res[0]]['tx_pkts'] = (float) $res[10];
				$this->data['net'][$res[0]]['tx_errs'] = (float) $res[11];
				$this->data['net'][$res[0]]['tx_drop'] = (float) $res[12];
				$this->data['net'][$res[0]]['tx_fifo'] = (float) $res[13];
				$this->data['net'][$res[0]]['tx_colls'] = (float) $res[14];
				$this->data['net'][$res[0]]['tx_carrier'] = (float) $res[15];
				$this->data['net'][$res[0]]['rx_compressed'] = (float) $res[16];
			}
		}
	}

	public function getCpuStats()
	{
		// docs: http://man7.org/linux/man-pages/man5/proc.5.html
		$stats = $this->readFileIntoArray("{$this->pProc}/stat");
		if (!is_array($stats))
		{
			return false;
		}
		// drop the first row
		unset($stats[0]);
		foreach (array_keys($stats) as $key)
		{
			if (strpos($stats[$key], 'cpu') === 0)
			{
				// remove the initial 'cpu' substring so only the cpu index will remain
				$stats[$key] = substr($stats[$key], 3);
				//
				$fields = explode(' ', $stats[$key]);
				$fields[0] = (int) $fields[0];
				$this->data['cpu']['time'] = $this->data['time'];
				$this->data['cpu']['abs'][$fields[0]]['user'] = (float) $fields[1];
				$this->data['cpu']['abs'][$fields[0]]['nice'] = (float) $fields[2];
				$this->data['cpu']['abs'][$fields[0]]['system'] = (float) $fields[3];
				$this->data['cpu']['abs'][$fields[0]]['idle'] = (float) $fields[4];
				$this->data['cpu']['abs'][$fields[0]]['iowait'] = (float) $fields[5];
				$this->data['cpu']['abs'][$fields[0]]['irq'] = (float) $fields[6];
				$this->data['cpu']['abs'][$fields[0]]['softirq'] = (float) $fields[7];
				$this->data['cpu']['abs'][$fields[0]]['steal'] = (float) $fields[8];
				$this->data['cpu']['abs'][$fields[0]]['guest'] = (float) $fields[9];
				$this->data['cpu']['abs'][$fields[0]]['guest_nice'] = (float) $fields[10];
				$this->data['cpu']['abs'][$fields[0]]['totalticks'] = array_sum($this->data['cpu']['abs'][$fields[0]]);
				continue;
			}
			if (strpos($stats[$key], 'intr') === 0)
			{
				break;
			}
		}
		if (!isset($this->data['cpu']['cores']))
		{
			$this->data['cpu']['cores'] = count($this->data['cpu']['abs']);
		}
	}

	public function getCpuStatsRelative()
	{
		$this->data['cpu']['rel'] = array();
		$this->getCpuStats();
		foreach (array_keys($this->data['cpu']['abs']) as $cpuIdx)
		{
			foreach (array_keys($this->data['cpu']['abs'][$cpuIdx]) as $fieldIdx)
			{
				$this->data['cpu']['rel'][$cpuIdx][$fieldIdx] = 0;
				if ($this->data['cpu']['abs'][$cpuIdx][$fieldIdx] > 0)
				{
					$this->data['cpu']['rel'][$cpuIdx][$fieldIdx] = (100.0 / ($this->data['cpu']['abs'][$cpuIdx]['totalticks'] / $this->data['cpu']['abs'][$cpuIdx][$fieldIdx]));
				}
			}
			// debugging info: total for each core must be equal to 1
			$this->data['cpu']['sum'][$cpuIdx]['usage'] = array_sum($this->data['cpu']['rel'][$cpuIdx]) - $this->data['cpu']['rel'][$cpuIdx]['totalticks'];
			$this->data['cpu']['sum'][$cpuIdx]['busy'] = 100.0 - $this->data['cpu']['rel'][$cpuIdx]['idle'];
		}
	}
}
?>