# TeeBX Build Quick Start #
TeeBX is built using a set of scripts to automate both the linux operating system and applications build. This is done using the T2 System Development Environment ([T2 SDE](http://www.t2-project.org/)) hosted on a T2 linux machine.

## Get the virtual host ##

---

For people who don't want to start from scratch by installing a T2 host and all the required software we provide a  linux host VMware image based on T2 version 8.0.

&lt;BR&gt;

The development system image is hosted on the Sourceforge project BoneOS _([BoneOS](http://sourceforge.net/projects/boneos/) is a parent project sharing a common base system with TeeBX)_, browse the project files on the [dev-environment folder](http://sourceforge.net/projects/boneos/files/00-dev-environment/) to download the latest VMware image.

After downloading the zip archive, uncompress it and then boot the linux virtual host using the VMware player (version 3.1+, free) or workstation (ver. 6+).

&lt;BR&gt;

You will need about 4 GB of free disk space to be able to successfully download all the source packages and build the image.

### First boot ###
Out of the box our T2 SDE virtual machine is set up to get it's network IP address via DHCP, the keyboard layout is set to "en-us".

&lt;BR&gt;

The root password is set to "boneos" (without quotes).

### Customizing system settings ###
The fastest way to change system setting is using the T2 linux configuration tool:

```
boneos-dev$ stone
```
Stone allow to easy change the most relevant settings, like keyboard layout, network, timezone... using a simple menu driven interface.<br>Sure the first thing you want to change will be the the keyboard layout, so select the first menu item:<br>
<br>
<img src='http://teebx.googlecode.com/svn/wiki/t2-stone-00.png' />

Now again the first menu item:<br>
<br>
<img src='http://teebx.googlecode.com/svn/wiki/t2-stone-01-sysconfig.png' />

...now select your preferred keymap:<br>
<br>
<img src='http://teebx.googlecode.com/svn/wiki/t2-stone-02-sysconfig-keymap.png' />

<h3>Virtual host additional info</h3>
The VM image has a 32 GB virtual scsi disk with two partitions. The first partition, mounted on /  is sized at 1 GB, the second partition sized at 5 GB is mounted on /var. The /var partition will be used to store sources and to build the system image.<br>This amount of storage allow to do most of the work without any problem but you must pay attention to keep the system clean.<br>
<br>
The choice to leave a large disk spare unpartitioned was done to be able to keep at reasonable size the development system image for distribution while having an option to resize the second partition if needed.<br>
<h4>Host editors</h4>
To people who do not like <b>vi</b>: before you try to install a new text editor, a good advice... <b>nano</b> is already available.<br>
<h2>Hands On</h2>
<hr />
The basic workflow steps are:<br>
<br>
<ol><li>Download sources via subversion<br>
</li><li>Configure a new build in the source tree<br>
</li><li>Start the build</li></ol>

Remember not to start any of the mentioned steps in the / parttion... you will go out of disk space a few minutes later!<br>
<br>
<h3>Get sources</h3>
Change your working directory:<br>
<br>
<pre><code>boneos-dev# cd /var/devel/public/teebx<br>
</code></pre>
Download sources from the subversion repository:<br>
<br>
<pre><code>boneos-dev# svn checkout http://teebx.googlecode.com/svn/trunk/ trunk<br>
</code></pre>
<h3>Configure a new build</h3>
Before starting bulding any image you must create a new configuration using the T2 SDE Config script:<br>
<br>
<pre><code>boneos-dev# cd trunk<br>
boneos-dev# ./scripts/Config -cfg trunk<br>
</code></pre>
In the above example we called the new configuration ''"trunk"'' using the -cfg parameter.<br>
<br>
<img src='http://teebx.googlecode.com/svn/wiki/start-config-step01.png' />

Use arrow keys to move between options, the tab key to move between action buttons.<br>
<br>
Now highlight the current Target Distribution then press enter to select a new target.<br>
<br>
<img src='http://teebx.googlecode.com/svn/wiki/start-config-step02.png' />

Press enter or the space bar to choose a new target and go back to the previous screen.<br>
<br>
If this is <b>the first time you use the T2 SDE I suggest you not to change</b> the various configuration options.<br>
<i>Caution</i>: <del>At the time of writing this document, the glibc target must be considered as experimental, it builds successfully but still shows many problems that need to be resolved - try it only if you want to contribute</del>

<i><b>Update:</b> the glibc target <del>got few progress but</del> was deleted, the eglib target may be considered mature and will become the preferred libc variant.</i>

Use the tab key to highlight the Exit button, then press enter to exit saving your new configuration.<br>
<br>
<h3>Starting building</h3>
Now that you have made a new configuration you're almost ready to run the build script, but please be patient.<br>
<br>
<h4>Downloading sources</h4>
The build script is able to download the source files, as needed, during the build process but I strongly suggest you do it before.<br>
<br>
This command will examine the configuration and will download all the required source packages:<br>
<br>
<pre><code>boneos-dev# ./scripts/Download -cfg trunk -required<br>
</code></pre>
We hope everything is going well, but it is possible we may encounter some problems: a few links no longer active, a site temporarily unreachable, a package that has been moved, and so on.<br>
<br>
If there are no problems you can switch to compile your image, else you need to fix any failed download before proceeding. You may fix the problematic download link reviewing the package source or downloading manually the source package into the mirror directory.<br>
<br>
<h4>Build!</h4>
To actually build the image you need to run the T2 SDE Build-Target script passing the configuration name to it:<br>
<br>
<pre><code>boneos-dev# ./scripts/Build-Target -cfg trunk<br>
</code></pre>
Now it's time to take a break,  it will take some time to complete the build (about 1 our on my machine to give you a vague idea).<br>
<br>
When the build will end look into the build subdirectory, you will find a directory whose name consists of <i>teebx-targetname-architecture-optimization-clibrary-configurationname</i>. Here into a subdirectory called <i>TOOLCHAIN</i> you will find the just built images: a .img file or a .iso file, or both depending on the target configuration.<br>
<br>
Now You have a complete build, which also mean You also got a working toolchain for this specific configuration.<br>
To update/rebuild a single package you don't need to start from scratch again but You simply need to run the Build-Target script appending the -job option, e.g. to rebuild the asterisk package:<br>
<pre><code>boneos-dev# ./scripts/Build-Target trunk -job 1-asterisk11<br>
</code></pre>
Another example, to rebuild the appliance scrips/web interface:<br>
<pre><code>boneos-dev# ./scripts/Build-Target trunk -job 1-appliance<br>
</code></pre>

That done You nedd to run the build script again to rebuild the image using the new/updated packages:<br>
<pre><code>boneos-dev# ./scripts/Build-Target trunk<br>
</code></pre>
<h4>Final thoughts</h4>
Would be nice to believe that you have not encountered any problems... but in the real world often things are not going this way, so what can you do now?<br>
<br>
The first thing to do is to study the <a href='http://www.t2-project.org/documentation/'>T2 SDE documentation</a>, only after doing this, and maybe if you like to contribute to the project, ask the developers.