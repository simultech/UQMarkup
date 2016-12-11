if [ -z "$1" ]; then
  AUDIODIR="."
else
  AUDIODIR=$1
fi
for i in $AUDIODIR/*.m4a
  do
    faad -o - "$i" | lame -h -b 192 - "${i%m4a}mp3"
  done
