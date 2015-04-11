#!/bin/sh
#
echo "starting download script"
echo "Args to shell:" $*
#
# ARG 1: Path to lsz executable.
# ARG 2: Elf File to download
# ARG 3: TTY port to use.
#
#path may contain \ need to change all to /
path_to_exe=$1
fixed_path=${path_to_exe//\\/\/}
#
tty_port_id=$3
echo "Serial Port PORT" $com_port_id "(note: should be /dev/cu.xxxxxx for OSX)"
echo "Using tty Port" $tty_port_id 
#
echo "Sending Command String to move to download if not already in download mode"
echo "~sketch download" > $tty_port_id
#Give the host time to stop the process and wait for download
sleep 1
#
#Move the existing sketch on target.
echo "Deleting existing sketch on target"
"$fixed_path/lsz" --escape -c "mv -f /sketch/sketch.elf /sketch/sketch.elf.old" < $tty_port_id > $tty_port_id
#"$fixed_path/lsz.exe" --escape -c "mv -f /sketch/sketch.elf /sketch/sketch.elf.old" < $tty_port_id > $tty_port 1>&0
#
# Execute the target download command
#
#Download the file.
host_file_name=$2
"$fixed_path/lsz" --escape --binary --overwrite $host_file_name < $tty_port_id  > $tty_port_id
#
#mv the downloaded file to /sketch/sketch.elf 
target_download_name="${host_file_name##*/}" 
echo "Moving downloaded file to /sketch/sketch.elf on target"
"$fixed_path/lsz" --escape -c "mv $target_download_name /sketch/sketch.elf; chmod +x /sketch/sketch.elf" < $tty_port_id > $tty_port_id
#
#
