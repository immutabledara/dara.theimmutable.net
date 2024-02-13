#!/bin/bash
DRBASEDIR="/var/www/misc/deathrow"
MBPATH="/var/www/html/masterblaster"
############################
DATESTAMP=`date +%Y%m%d`
DRDIR=$DRBASEDIR/$DATESTAMP
mkdir $DRDIR
cp -Rf $MBPATH/* $DRDIR/
cd $DRDIR && \
	curl -s "https://en.wikipedia.org/wiki/Wikipedia:Articles_for_deletion/Log/Yesterday" | \
		tr '">' '">\n' | \
			grep vector-toc-link | \
				sed -e 's/.*href="#\(.*\)">.*/\1/' | \
					grep -v vector-toc-link | \
						while read line; do \
							python3 zwi_mediawiki.py -s wikipedia -t "$line"; \
						done
