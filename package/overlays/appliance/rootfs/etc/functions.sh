# $Id$
# part of BoneOS build platform (http://www.teebx.com/)
# Copyright(C) 2013 - 2014 Giovanni Vallesi.
# More information can be found in the files COPYING and README.

killByMount()
{
	#1: signal
	#2: mountpoint
	local attachedProcs

	if [ -z "$1" ] ; then
		exit
	fi
	if [ -d "$2" ] ; then
		attachedProcs=$(lsof -t "$2")
		if [ ! -z "$attachedProcs" ] ; then
			kill "-$1" $attachedProcs
		fi
	fi
}
# do not delete the safeguard LF after last curly brace
