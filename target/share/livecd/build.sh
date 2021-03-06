# --- T2-COPYRIGHT-NOTE-BEGIN ---
# This copyright note is auto-generated by ./scripts/Create-CopyPatch.
# 
# T2 SDE: target/share/firmware/build.sh
# Copyright (C) 2012 - 2013 BoneOS build platform (http://www.teebx.com/)
# Copyright (C) 2009 The AskoziaPBX Project
# 
# More information can be found in the files COPYING and README.
# 
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; version 2 of the License. A copy of the
# GNU General Public License can be found in the file COPYING.
# --- T2-COPYRIGHT-NOTE-END ---
#
#Description: livecd

. $base/misc/target/functions.in

set -e
# init a variable to build a custom initramfs for livecd
declare image_type=livecd

. target/share/firmware/build.sh

echo "Preparing livecd iso from build result ..."
livecdlocation="$build_toolchain/livecd"
rm -rf $livecdlocation
rm -f $SDECFG_ID.iso
mkdir -p $livecdlocation
cd $livecdlocation

echo "Copying over firmware contents..."
echo "  -> Boot files, initramfs"
cp -Rp $imagelocation/root_stage/* $livecdlocation
# delete firmware initramfs then replace with the livecd specific one
rm -f "$livecdlocation/boot/$initramfs_name"
cp "$build_toolchain/initramfs.livecd.igz" "$livecdlocation/boot/$initramfs_name"

echo "  -> Offload, rootfs"
cp -Rp $imagelocation/offload_stage/* $livecdlocation/
touch $livecdlocation/livecd
echo "Stamping SDE configuration name..."
echo $config > $livecdlocation/livecd

echo "Adding iso bootloader files..."
cp ../../usr/lib/grub/i386-t2/stage2_eltorito $livecdlocation/boot/grub/
cp $base/target/share/livecd/menu.lst $livecdlocation/boot/grub/

echo "Adding installable firmware image..."
cp ../$SDECFG_ID.img.gz $livecdlocation/firmware.img.gz

echo "Building iso ..."
mkisofs -R \
	-b boot/grub/stage2_eltorito \
	-V "TeeBX.appliance.livecd" \
	-no-emul-boot \
	-boot-load-size 4 \
	-boot-info-table \
	-iso-level 3 \
	-o $build_toolchain/$SDECFG_ID.iso .

echo "Done."
