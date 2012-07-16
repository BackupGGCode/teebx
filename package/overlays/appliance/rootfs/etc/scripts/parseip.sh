#!/bin/sh
# $Id$
# part of BoneOS build platform (http://www.teebx.com/)
# Copyright(C) 2011 - 2012 Giovanni Vallesi.
# All rights reserved.
# 
# originally part of AskoziaPBX svn trunk revision 1514 (http://askozia.com/pbx)
# Copyright (C) 2007-2009 tecema (a.k.a IKT) <http://www.tecema.de>. All rights reserved.
# originally part of m0n0wall (http://m0n0.ch/wall)
# Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>. All rights reserved.

/sbin/ifconfig eth0 | awk '/dr:/{gsub(/.*:/,"",$2);print$2}'
