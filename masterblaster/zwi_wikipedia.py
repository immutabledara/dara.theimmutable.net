#!/usr/bin/env python
# -*- coding: utf-8 -*-
# Create ZWI files from Wikipedia 
# @version 1.0. Dec 10, 2021
# S.V.Chekanov (KSF)


# Please change these lines for other wiki based on Mediawiki 
SITE="Wikipedia";  # Full title of the Wiki 
SITE_API="https://en.wikipedia.org/w/"; # Should Points to api.php 
endPoint = SITE_API+"api.php";
SITE_URL="https://en.wikipedia.org/wiki/"; # Where all articles are located (SITE_URL+title) 
SITE_SHORT="wikipedia";                    # short name  

##################### do not change below ##################
from zwi_producer import *
import requests
import re,os
import gzip
import shutil # to save it locally
import zlib,zipfile
import sys, json, urllib.parse
from time import time
import tempfile
import urllib3
import argparse

# alway use low case for directories
SITE_SHORT=SITE_SHORT.lower()

#### input parameters ####
kwargs = {}
parser = argparse.ArgumentParser()
parser.add_argument('-q', '--quiet', action='store_true', help="don't show verbose")
parser.add_argument("-t", '--title', help="Title of the article")
args = parser.parse_args()
args.verbose = not args.quiet
#print("Title=",args.title)
#print("Is verbose=",args.verbose)
title=args.title

PARAMS = {
    "action": "parse",
    "page": title,
    "prop":"text|wikitext",
    "format": "json"
}

S = requests.Session()
R = S.get(url=endPoint, params=PARAMS)
DATA = R.json()

output_html = DATA["parse"]["text"]['*'] 
output_wikitext = DATA["parse"]["wikitext"]['*']

output_new=output_html.replace("/w/index.php", SITE_API + "index.php");
output_new=output_new.replace("/wiki/", SITE_URL);
output_new=output_new.replace("[math]", "\(");
output_new=output_new.replace("[/math]", "\)");

#add proper https
output_new=output_new.replace("src=\"//", "src=\"https://");
output_new=output_new.replace("srcset=\"//", "srcset=\"https://");
output_new=output_new.replace("url(\"//", "url(\"https://");


fout=gzip.open("article.html.gz",'wb')
fout.write( output_new.encode() )
fout.close()

fout=gzip.open("article.wikitext.gz",'w')
fout.write( output_wikitext.encode() )
fout.close()

html_file="article.html.gz"
title=title.replace(" ","_")
#title=title.replace("/","~")
zwi_file=title+".zwi"

zwi_file=urllib.parse.quote_plus(zwi_file)

makeZWI(html_file, zwi_file, title, SITE_SHORT)
