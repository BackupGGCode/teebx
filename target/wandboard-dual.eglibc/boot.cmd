# $Id: $
# part of BoneOS build platform (http://www.teebx.com/)
# Copyright(C) 2012 - 2014 Giovanni Vallesi.
setenv console 'ttymxc0,115200'
setenv gpu_mem '16M'
setenv fb_mem '1M'
setenv root '/dev/ram0 rw'
setenv panicarg 'panic=1'
setenv loglevel '8'
setenv fdt_addr 0x18000000
setenv loadaddr 0x12000000
setenv initrd_addr 0x11800000
setenv mmcdevp ${mmcdev}:${mmcpart}
setenv image 'boot/kernel.ubi'
setenv initrd 'boot/iramfs.ubi' 
setenv fdt_file 'boot/dtbs/target.dtb'
setenv setargs 'setenv bootargs console=\${console} gpumem=\${gpu_mem} fbmem=\${fb_mem} init=/init root=\${root} \${panicarg} debug loglevel=\${loglevel}'
setenv load_mmc 'fatload mmc ${mmcdevp} ${loadaddr} ${image}; fatload mmc ${mmcdevp} ${initrd_addr} ${initrd}; fatload mmc ${mmcdevp} ${fdt_addr} ${fdt_file}'
setenv doboot 'bootz ${loadaddr} ${initrd_addr} ${fdt_addr}'
setenv bootcmd 'run setargs load_mmc doboot'
run bootcmd