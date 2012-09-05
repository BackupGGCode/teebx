# --- T2-COPYRIGHT-NOTE-BEGIN ---
# This copyright note is auto-generated by ./scripts/Create-CopyPatch.
# 
# T2 SDE: target/share/firmware/build.sh
# Copyright (C) 2004 - 2008 The T2 SDE Project
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

. $base/misc/target/functions.in

set -e

prev_pwd=$PWD

echo "Preparing initramfs image from build result ..."

rm -rf $imagelocation{,.igz}
mkdir -p $imagelocation ; cd $imagelocation

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

echo "Storing default config.xml ..."
mkdir conf.default
cp $base/target/$target/config.xml conf.default/

echo "Setup some symlinks ..."
ln -s /offload/kernel-modules lib/modules

echo "Stamping build ..."
echo $config > etc/version
echo `date +%s` > etc/version.buildtime

echo "Creating links for identical files ..."
link_identical_files

echo "Setting permissions ..."
chmod 755 bin/*
chmod 755 sbin/*
chmod 755 etc/rc*
chmod 644 etc/pubkey.pem
chmod 644 etc/inc/*

echo "Cleaning away stray files ..."
find ./ -type f -name "._*" -print -delete

#remove openssh build files
rm -rf ../initramfs/opt/bin
rm -rf ../initramfs/opt/etc
rm -rf ../initramfs/opt/sbin

echo "Creating initramfs image ..."
find . | cpio -H newc -o | gzip > ../initramfs.igz

echo "The image is located at $imagelocation.igz"

cd $prev_pwd
