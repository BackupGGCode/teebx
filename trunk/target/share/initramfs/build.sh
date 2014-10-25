# --- T2-COPYRIGHT-NOTE-BEGIN ---
# This copyright note is auto-generated by ./scripts/Create-CopyPatch.
# 
# T2 SDE: target/share/firmware/build.sh
# Copyright (C) 2012 - 2013 BoneOS build platform (http://www.teebx.com/)
# Copyright (C) 2004 - 2011 The T2 SDE Project
# 
# More information can be found in the files COPYING and README.
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; version 2 of the License. A copy of the
# GNU General Public License can be found in the file COPYING.
# --- T2-COPYRIGHT-NOTE-END ---
#
#Description: initramfs

# import T2 functions
source $base/misc/target/functions.in

# import appliance specific functions
source $base/target/share/image-build.functions

set -e

unset OVERRIDE_DISABLE_SHARED
echo "Checking if the gcc runtime library already built..."
if [ -f $base/build/$SDECFG_ID/lib/libgcc_s.so.1 ]; then
	echo "  -> Found."
else
	echo "  -> Gcc runtime not found, enabling shared then starting a new build."
	export OVERRIDE_DISABLE_SHARED='Y'
	eval "$base/scripts/Build-Target -cfg $config -job 0-gcc"
fi
export OVERRIDE_DISABLE_SHARED=

echo "Preparing initramfs image from build result ..."

rm -rf $initramfs_loc{,.igz}
mkdir -p $initramfs_loc
pushd $initramfs_loc

find $build_root -printf "%P\n" | sed '

# stuff we never need

/^TOOLCHAIN/							d;
/^offload/								d;

/\.a$/									d;
/\.o$/									d;
/\.old$/								d;
/\.svn/									d;
/\.po/									d;

 /^boot/								d;
/\/doc/									d;
/\/games/								d;
/\/include/								d;
/\/opt/									d;
/\/src/									d;
  /etc\/conf/							d;
  /etc\/cron.d/							d;
  /etc\/cron.daily/						d;
  /etc\/dahdi\/system.conf/				d;
  /etc\/hotplug/						d;
  /etc\/hotplug.d/						d;
  /etc\/init.d/							d;
  /etc\/opt/							d;
  /etc\/postinstall.d/					d;
  /etc\/profile.d/						d;
  /etc\/rc.d\//							d;
  /etc\/skel/							d;
  /etc\/stone.d/						d;
  /lib\/modules/						d;
  /var\/adm/							d;
# new bits to move usr/* out of the initramfs and into /offload
  /usr/									d;

' > tar.input

copy_with_list_from_file $build_root . $PWD/tar.input
rm tar.input

echo "Preparing initramfs image from target defined files ..."
copy_from_source $base/target/$target/rootfs .
copy_from_source $base/target/share/initramfs/rootfs .

echo "Storing a default system configuration file in initramfs..."
mkdir conf.default
setupCfgDefault "conf.default"

echo "Setup some symlinks ..."
ln -s /offload/kernel-modules lib/modules

echo "Stamping build and release information..."
#echo $config > etc/version
#echo `date +%s` > etc/version.buildtime
buildInfo $initramfs_loc

echo "Creating links for identical files ..."
link_identical_files

echo "Setting permissions ..."
chmod 755 init
chmod 755 bin/*
chmod 755 sbin/*
chmod 755 etc/rc*
chmod 755 etc/scripts/*

if [ -f etc/pubkey.pem ];
then
	chmod 644 etc/pubkey.pem
fi
if [ -d etc/inc ];
then
	chmod 644 etc/inc/*
fi

echo "Cleaning away stray files ..."
find ./ -type f -name "._*" -print -delete

#remove openssh build files
#rm -rf $initramfs_loc/opt/bin
#rm -rf $initramfs_loc/opt/etc
#rm -rf $initramfs_loc/opt/sbin

#remove /opt
rm -rf $initramfs_loc/opt

echo "Creating initramfs image..."
find . | cpio -H newc -o | gzip > "$build_toolchain/initramfs.igz"
echo "  -> Done."

echo "The image is located at $build_toolchain/initramfs.igz"

if [ "$image_type" == "livecd" ] ; then
	mv etc/rc.mount etc/rc.mount.save
	mv etc/rc.livecd.mount etc/rc.mount

	echo "Creating live CD initramfs image..."
	find . | cpio -H newc -o | gzip > "$build_toolchain/initramfs.livecd.igz"
	echo "  -> Done."

	mv etc/rc.mount etc/rc.livecd.mount
	mv etc/rc.mount.save etc/rc.mount
fi

popd
