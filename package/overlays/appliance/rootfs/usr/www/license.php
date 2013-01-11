<?php
/*
	$Id$
	part of m0n0wall (http://m0n0.ch/wall)

	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/

require("guiconfig.inc");

$pgtitle = array("License");

?>
<?php include("fbegin.inc"); ?>
	<p><strong>The TeeBX&reg; communication platform.<br>
	Copyright &copy; 2010 - 2011 Giovanni Vallesi (<a href="http://teebx.org/">TeeBX.org</a>).<br>
	All rights reserved.</strong></p>
	<pre>  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Affero General Public License as
  published by the Free Software Foundation, either version 3 of the
  License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Affero General Public License for more details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see [http://www.gnu.org/licenses/].

  TeeBX Source code is available via svn at [http://code.google.com/p/teebx/source/checkout].
  </pre>
  <p>Because  TeeBX includes many pieces of software the above license apply only to the code written for TeeBX itself, any other software boundled inluding the one forked apply for a proper license.</p>
  <hr size="1">
	<p>TeeBX&reg is derived from Askozia&reg;PBX (Linux based 2.0 branch, svn revision 1948) which is currently released under a more restrictive license.<br>
	Askozia&reg;PBX is Copyright &copy; 2007-2011 tecema gmbh a.k.a. <a href="http://www.tecema.de" target="_blank">IKT</a>.</br>
	Please read carefully their license at <a href="http://www.askozia.com/pbx-license" target="_blank">Askozia&reg; website</a>.<br>
	Many thanks to Michael Iedema, the whole tecema staff and all of the AskoziaPBX contributors.<br>
	<br>
	<hr size="1">
	<p>TeeBX&reg is also based upon/includes various Free/Libre and Open Source Software packages, listed below.<br>
	A big thanks to the authors for all of their hard work!</p>
	<ul>
		<li><a href="http://www.asterisk.org/">Asterisk</a> - telephony engine</li>
		<li><a href="http://busybox.net/">BusyBox</a> - tiny Linux userspace programs</li>
		<li><a href="http://www.bzip.org/">bzip2</a> - compression library</li>
		<li><a href="http://www.voip-info.org/wiki/view/DAHDI">DAHDI</a> - telephony interface drivers</li>
		<li><a href="http://matt.ucc.asn.au/dropbear/dropbear.html">Dropbear</a> - tiny SSH server</li>
		<li><a href="http://ez-ipupdate.com/">EZ-IPupdate</a> - tiny dynamic DNS update client</li>
		<li><a href="http://www.gnu.org/software/autoconf/">GNU Autoconf</a> &amp; <a href="http://www.gnu.org/software/automake/">GNU Automake</a> - automated software compilation configuration</li>
		<li><a href="http://www.gnu.org/software/binutils/">GNU Binutils</a> - binary tools</li>
		<li><a href="http://www.gnu.org/software/bison/">GNU Bison</a> - parser generator</li>
		<li><a href="http://gcc.gnu.org/">GNU GCC</a> - compiler collection</li>
		<li><a href="http://www.gnu.org/software/gettext/">GNU Gettext</a> - localization tools</li>
		<li><a href="http://www.gnu.org/software/grub/">GNU Grub</a> - bootloader</li>
		<li><a href="http://www.gnu.org/software/libtool/">GNU Libtool</a> - shared library abstraction library</li>
		<li><a href="http://gmplib.org/">GNU MP</a><span> - multiple precision arithmetic library</li>
		<li><a href="http://www.gnu.org/software/ncurses/">GNU Ncurses</a> - terminal library</li>
		<li><a href="http://www.gnu.org/software/termutils/">GNU Termcap</a> - terminal library</li>
		<li><a href="http://jqueryui.com/">jQuery UI</a> - webgui javascript interface library</li>
		<li><a href="http://info.wsisiz.edu.pl/%7Esuszynsk/jQuery/demos/jquery-selectbox/">jQuery.preloadimages</a> - jQuery interface plugin</li>
		<li><a href="http://t.wits.sg/jquery-progress-bar/">jQuery.progressbar</a> - jQuery interface plugin</li>
		<li><a href="http://www.texotela.co.uk/code/jquery/select">jQuery.selectboxes</a> - jQuery interface plugin</li>
		<li><a href="http://www.voip-info.org/wiki/view/Asterisk+libpri">LibPRI</a> - BRI and PRI ISDN signaling library</li>
		<li><a href="http://www.remotesensing.org/libtiff/">LibTIFF</a> - TIFF library</li>
		<li><a href="http://kernel.org/">Linux</a> - operating system kernel</li>
		<li><a href="http://people.freebsd.org/%7Eabe/">LSoF</a> - list open files utility</li>
		<li><a href="http://doolittle.icarus.com/ntpclient/">NTPClient</a> - tiny NTP client</li>
		<li><a href="http://m0n0.ch/wall">m0n0wall&reg;, &copy; 2002 - 2008 Manuel Kasper</a></li>
		<li><a href="http://www.mpfr.org/">MPFR</a><span> - Multiple precision floating-point library</span></li>
		<li><a href="http://msmtp.sourceforge.net/">MSMTP</a> - tiny SMTP client</li>
		<li><a href="http://www.opsound.org/">Music-on-Hold (opsound)</a> - hold music</li>
		<li><a href="http://www.openssl.org/">OpenSSL</a> - SSL/TLS library &amp; tools</li>
		<li><a href="http://mj.ucw.cz/pciutils.html">PCI Utils</a> - PCI utilities</li>
		<li><a href="http://php.net/">PHP</a> - scripting and webgui language</li>
		<li><a href="http://www.amooma.de">Prompts (AMOOMA)</a> - German prompts</li>
		<li><a href="http://www.enicomms.com/cutglassivr">Prompts (CutGlassIVR)</a> - British English prompts</li>
		<li><a href="http://www.asterisk.org/">Prompts (Digium)</a> - US English &amp; Canadian French prompts</li>
		<li><a href="http://mirror.tomato.it/">Prompts (Tomato.it)</a> - Italian prompts</li>
		<li><a href="http://www.soft-switch.org/">SpanDSP</a> - telephony DSP library</li>
		<li><a href="http://www.rickk.com/sslwrap">SSLWrap</a> - SSL connection wrapper</li>
		<li><a href="http://www.t2-project.org/">T2 System Development Environment</a> - cross-compiling build environment</li>
		<li><a href="http://www.uclibc.org/">uClibc</a> - tiny C language library</li>
		<li><a href="http://www.zlib.net/">zlib</a> - compression library</li>
	</ul>
<?php include("fend.inc"); ?>
