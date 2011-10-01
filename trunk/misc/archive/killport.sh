#!/bin/sh
#
# --- T2-COPYRIGHT-NOTE-BEGIN ---
# This copyright note is auto-generated by ./scripts/Create-CopyPatch.
# 
# T2 SDE: misc/archive/killport.sh
# Copyright (C) 2004 - 2005 The T2 SDE Project
# Copyright (C) 1998 - 2003 ROCK Linux Project
# 
# More information can be found in the files COPYING and README.
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; version 2 of the License. A copy of the
# GNU General Public License can be found in the file COPYING.
# --- T2-COPYRIGHT-NOTE-END ---

signal=15
returncode=0

for port ; do
	xport="`printf '%04X' $port 2> /dev/null || echo ERROR`"
	if [ "$xport" = "ERROR" ] ; then
		echo "Not a valid port number: $port" >&2
		returncode=$(($returncode + 1))
	else
	    echo "Sending signal $signal to all processes connected" \
	         "to port $port:"

	    for proto in tcp udp ; do
		echo -n "  Inodes for `echo $proto | tr a-z A-Z`/$xport: "
		inodes=`egrep "^ *[0-9]+: +[0-9A-F]+:$xport " /proc/net/$proto |
		       tr -s ' ' | cut -f11 -d' ' | tr '\n' ' '`
		if [ "$inodes" ] ; then
		    echo "$inodes (getting pids)"
		    for inode in $inodes ; do
			echo -n "    PIDs for inode $inode: "
			pids="`ls -l /proc/[0-9]*/fd/* 2> /dev/null | \
			       grep 'socket:\['$inode'\]' | tr -s ' ' |
			       cut -f9 -d' ' | cut -f3 -d/ | tr '\n' ' '`"
			if [ "$pids" ] ; then
				echo "$pids (sending signal)"
				kill -$signal $pids
			else
				echo "None found."
			fi
		    done
		else
			echo "None found."
		fi
	    done
	fi
done

exit $returncode
