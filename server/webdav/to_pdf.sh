#!/bin/bash
#
# Converts a document to a PDF file using
# unoconv and opens it with the evince viewer.
##

file=`basename $1`
dir=`dirname $1`
prefix=${file%.[^.]*}
outfile="${prefix}.pdf"

echo Generating $outfile

/usr/bin/python /usr/bin/unoconv --server localhost --port 2002 -vvv -f pdf $1


