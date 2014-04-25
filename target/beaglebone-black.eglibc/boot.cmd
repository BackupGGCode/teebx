# $Id: $
# part of BoneOS build platform (http://www.teebx.com/)
# Copyright(C) 2012 - 2013 Giovanni Vallesi.
setenv console 'ttyS0,115200'
setenv root '/dev/ram rw'
setenv panicarg 'panic=1'
setenv loglevel '8'
setenv kernel '/boot/kernel.ubi'
setenv initramfs '/boot/iramfs.ubi'
setenv setargs 'setenv bootargs console=\${console} init=/init root=\${root} \${panicarg} debug loglevel=\${loglevel}'
setenv boot_mmc 'fatload mmc 0 0x43000000 script.bin && fatload mmc 0 0x48000000 \${kernel}; fatload mmc 0 0x44000000 \${initramfs}; bootm 0x48000000 0x44000000'
setenv bootcmd 'run setargs boot_mmc'