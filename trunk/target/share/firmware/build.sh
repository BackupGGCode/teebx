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
#Description: firmware

. $base/misc/target/functions.in

# import some shared functions
source $base/target/share/image-build.functions

set -e

# variables initialization
#
# build root, build toolchain. Read only
declare -r build_root="$base/build/$SDECFG_ID"
declare -r build_toolchain="$base/build/$SDECFG_ID/TOOLCHAIN"
# set initramfs preparation directory. Read only
declare -r initramfs_loc="$build_toolchain/initramfs"
# set image preparation directory. Read only
declare -r imagelocation="$build_toolchain/firmware"
# root dir, offload (rootfs). Read only
declare -r root_top_dir=root_stage
declare -r rootfs_top_dir=offload_stage

# variables that can be overrided by the target image.conf
#
# boot env target files destination, defaults to $root_top_dir
declare env_top_dir="$root_top_dir"
# linux kernel image source name
declare linux_build_name=vmlinuz
# initramfs file name for the target, default to be fat naming compatible
declare initramfs_name=initramf.igz

# image geometry variables
#
# sector size in bytes
declare -i sect_size=512
declare -i track_sec=63
declare -i heads=16

# sfdisk compatibility mode
declare part_comp='-L'

# first partition offset in sectors, defaults to 63.
# increase it via a target image.conf
# if you need to write raw data before fs partitions
declare -i part_offset=1
# partitions padding
declare -i rtp_block_pad=2028
declare -i ofp_block_pad=10240

# load target image.conf, if any
if [ -f $base/target/$target/image.conf ]; then
	echo "Overriding/loading variables specified in target image.conf."
	source $base/target/$target/image.conf
fi

# cylinder size in bytes (16 heads x 63 sectors/track x $sect_size bytes/sector)
cylinder_size=$(($heads * $track_sec * $sect_size))
sectors_per_cylinder=$(($cylinder_size / $sect_size))
echo "Image geometry summary:"
echo "  -> Heads: $heads"
echo "  -> Sectors per track: $track_sec"
echo "  -> Sector Size: $sect_size"
echo "  -> Cylinder Size: $cylinder_size"
echo "  -> Sectors per cylinder: $sectors_per_cylinder"
echo "  -> Partition offset, sector(s): $part_offset"

# build initramfs
. target/share/initramfs/build.sh

# setup image build tree
echo "Preparing firmware image from build result ..."
echo "  -> Cleaning up old image files if any"
rm -rf $imagelocation{,.img}
prepareImgDirs $imagelocation $root_top_dir $rootfs_top_dir
echo "  -> Moving into $imagelocation"
cd $imagelocation

# Platform dependent initialization bits
# as defined by the target image.conf file
res=$(isArray 'boot_envfiles')
if [ $res -eq 0 ]; then
	echo "Copying platform specific initialization bits..."
	for i_file in "${!boot_envfiles[@]}"; do
		current="${boot_envfiles[$i_file]}"
		if [ -f "$base/target/$target/$current" ]; then
			echo "  -> copying $current to image root"
			cp "$base/target/$target/$current" "$imagelocation/$env_top_dir/"
		fi
	done
fi

# Platform specific binary blobs
# as defined by the target image.conf file
res=$(isArray 'boot_blobs')
if [ $res -eq 0 ]; then
	echo "Copying platform specific binary blobs..."
	for i_file in "${!boot_blobs[@]}"; do
		current="${boot_blobs[$i_file]}"
		if [ -f "$build_root/boot/$current" ]; then
			echo "  -> copying $current to image root"
			cp "$build_root/boot/$current" "$imagelocation/$root_top_dir/"
		fi
	done
fi

# copy default config file into the target tree
setupCfgDefault $root_top_dir/conf

echo "Copy system core files into staging directories..."
# using fat16 8.3 naming convention for destination files
# for larger fs portability.
if [ -d "$build_root/usr/lib/grub" ] ; then
	if [ ! -f "$base/target/$target/menu.lst" ] ; then
		echo "  -> Grub was built for this target but menu.lst is missing! Aborting."
		exit 1
	fi
	echo "  -> Preparing grub for the target image."
	mkdir -p $imagelocation/$root_top_dir/boot/grub/
	cp $build_root/usr/lib/grub/i386-t2/stage{1,2} $imagelocation/$root_top_dir/boot/grub/
	cp $base/target/$target/menu.lst $imagelocation/$root_top_dir/boot/grub/
fi
echo "  -> Initramfs."
cp $build_toolchain/initramfs.igz $imagelocation/$root_top_dir/boot/$initramfs_name
echo "  -> Linux kernel."
cp $build_root/boot/$linux_build_name $imagelocation/$root_top_dir/boot/
echo "  -> Kernel modules."
cp -Rp $build_root/lib/modules/* $imagelocation/$rootfs_top_dir/kernel-modules/

# prepare target offload
prepareRootfs $build_root $imagelocation/$rootfs_top_dir

# cleanup sources, obj files, etc...
doCleanup $imagelocation

# get individual partitions size
getPartSize $imagelocation $root_top_dir $rootfs_top_dir

# calculate image size
getImageSize

echo "Writing a binary container for the disk image ..."
dd if=/dev/zero \
	of=$imagelocation/firmware.img \
	bs=$sect_size \
	count=$total_sector_count


cyls_needed=$(($total_sector_count / $sectors_per_cylinder + $part_offset))
echo "Cylinders needed: $total_sector_count sectors / $sectors_per_cylinder sectors-per-cyl + $part_offset = $cyls_needed"
offload_start_sector=$(($root_size + $part_offset))

# moving into image dir to avoid that long lines
# reported by fdisk make output hard to read
echo "Pushing working dir then moving to image directory:"
pushd $imagelocation
echo "Partitioning the disk image..."
sfdisk -C$cyls_needed -S${track_sec} -H${heads} -uS -f ${part_comp} --no-reread firmware.img << EOF
$part_offset,$root_size,6,*
$offload_start_sector,,83
EOF

echo "Back to original directory:"
popd

echo "Formatting and populating partitions..."
echo "  -> Working on partition 1..."
echo "     -> zero filling"
dd if=/dev/zero \
	of=$imagelocation/part1.img \
	bs=$sect_size \
	count=$root_size
echo "     -> set up a loop device"
losetup /dev/loop0 $imagelocation/part1.img
echo "     -> set up filesystem"
mkfs.vfat -n system /dev/loop0
echo "     -> mount partition"
mount -t msdos /dev/loop0 $imagelocation/loop
echo "     -> copying root stage files into partition"
cp -Rp $imagelocation/$root_top_dir/* $imagelocation/loop/
echo "     -> unmount"
umount /dev/loop0
echo "     -> detach loop device"
losetup -d /dev/loop0

echo "  -> Working on partition 2..."
echo "     -> zero filling"
dd if=/dev/zero \
	of=$imagelocation/part2.img \
	bs=$sect_size \
	count=$offload_size
echo "     -> set up filesystem"
mke2fs -m0 -L offload -F $imagelocation/part2.img
echo "     -> adjust filesystem parameters"
tune2fs -c0 $imagelocation/part2.img
echo "     -> mount partition"
mount -o loop $imagelocation/part2.img $imagelocation/loop
echo "     -> copying offload stage files into partition"
cp -Rp $imagelocation/$rootfs_top_dir/* $imagelocation/loop/
echo "     -> unmount"
umount loop

# building the final image
echo "Writing final image..."
echo "  -> raw copying partition 1"
dd if=$imagelocation/part1.img \
	of=$imagelocation/firmware.img \
	bs=$sect_size \
	seek=$part_offset
echo "  -> raw copying partition 2"
dd if=$imagelocation/part2.img \
	of=$imagelocation/firmware.img \
	bs=$sect_size \
	seek=$offload_start_sector

# Install grub if built for that target
if [ -d "$build_root/usr/lib/grub" ] ; then
	setupGrub
fi

# verify image size
checkImageSize $imagelocation

echo "Compressing image ..."
gzip -9 $imagelocation/firmware.img
echo "Moving image to $build_toolchain/$SDECFG_ID.img.gz..."
mv $imagelocation/firmware.img.gz $build_toolchain/$SDECFG_ID.img.gz
