#!/usr/bin/env python
# -*- coding: utf-8 -*-

# You should run this script imidiatly after viewing online wiki since this tool actively uses cached images.
# It is recommended to run in withing a few minutes after watching an article in factseek.org 
# If you run this script after 1 day after viewing article, some cached data may not be available.
#
# @version 1.4, May 14, 2022 
# Changelog:
#   Re-use TCP connection for images
#
# @version 1.3, Decemeber 18, 2021 
# Changelog:
#   1 SourceURL was added
#   2 Added description and comment
# @version 1.1. October 30, 2021
# Changelog:
#   1 Version is numeric number
#   2 Pretty printing for JSON 

# S.V.Chekanov (KSF)
# 
from bs4 import *
import requests
import re,os
import gzip 
import shutil # to save it locally
import zlib,zipfile
import sys, json, urllib.parse
from time import time 
import tempfile
from hashlib import sha1
from os import path
from bs4 import BeautifulSoup
import html2text
import urllib3
from  short_description import *
#urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)


ZWI_VERSION=1.3 # Version of ZWI files

# ISO Language Codes https://www.w3schools.com/tags/ref_language_codes.asp
Lang="en"

# find categories
PCategories=re.compile('\[\[Category:(.*?)\]\]')

# Create sha1 of a string 
def make_sha1(s, encoding='utf-8'):
    return sha1(s.encode(encoding)).hexdigest()

# Get file sha1 hash
def fileSha1(xfile):
  BLOCKSIZE = 65536
  hasher = sha1()
  if (path.exists(xfile)): 
    with open(xfile, 'rb') as afile:
       buf = afile.read(BLOCKSIZE)
       while len(buf) > 0:
          hasher.update(buf)
          buf = afile.read(BLOCKSIZE)
    return hasher.hexdigest()
  return ""


dirpath = tempfile.mkdtemp()

script_dir=os.path.dirname(os.path.realpath(__file__))
#print("From = ",script_dir)
stime=str(int(time()))
#print("Time=",stime)
#print("TMP dir=",dirpath)

# this is where data go
img_dir="data/media/images"
css_dir="data/css"
folder_images=dirpath+"/"+img_dir
folder_css=dirpath+"/"+css_dir

ainput=""
aoutput=""
atitle=""
asource=""
averbose=False


# get title from heading if possible
def getHeading(soup, stitle):
    ATIT=soup.find_all('h1')
    mtitle=stitle
    utitle=""
    for t in ATIT:
        utitle=t.text.strip()
        break
    if (utitle != None): 
        if len(utitle)>2:
            mtitle=utitle.strip()
            print(" -> Extracted title from heading=",mtitle)
    return mtitle;

# get URL of the article
# apa : correct basename of the file
# publisher : publisher
def getURL(apa, publisher):
    url="";
    #SITE_URL="https://en.wikipedia.org/wiki/";
    if (publisher=="wikipedia"): SITE_URL="https://en.wikipedia.org/wiki/";
    elif (publisher=="wikisource"): SITE_URL="https://en.wikisource.org/wiki/";
    elif (publisher=="wikitia"): SITE_URL="https://wikitia.com/wiki/";
    elif (publisher=="handwiki"): SITE_URL="https://handwiki.org/wiki/";
    elif (publisher=="citizendium"): SITE_URL="https://en.citizendium.org/wiki/";
    elif (publisher=="edutechwiki"): SITE_URL="https://edutechwiki.unige.ch/en/";
    elif (publisher=="ballotpedia"): SITE_URL="https://ballotpedia.org/";
    elif (publisher=="scholarpedia"): SITE_URL="http://www.scholarpedia.org/article/";
    elif (publisher=="encyclopediaofmath"): SITE_URL="https://encyclopediaofmath.org/wiki/";
    elif (publisher=="sep"): SITE_URL="https://plato.stanford.edu/entries/";

    url=SITE_URL+apa

    return url;


# CREATE FOLDER
def folder_create(images):

    os.system("rm -rf "+folder_images);
    os.system("rm -rf "+folder_css);

    try:
        # folder creation
        os.system("mkdir -p "+folder_images)
        os.system("mkdir -p "+folder_css)
 
    # if folder exists with that name, ask another name
    except:
        print("Folder Exist with that name!")
        pass

    # image downloading start
    download_images(images, folder_images)
  
 
# map to keep replacements for images 
imageReplacer={}
cssReplacer={}


def zipdir(path, ziph):
    global aoutput, atitle, asource

    # ziph is zipfile handle
    for root, dirs, files in os.walk(path):
        for file in files:
            ziph.write(os.path.join(root, file),
                       os.path.relpath(os.path.join(root, file),
                                       os.path.join(path, '..')))

# DOWNLOAD ALL IMAGES FROM THAT URL
def download_images(images, folder_name):
    
    # intitial count is zero
    count = 0

    images=list(set(images))
#    print(f"Total {len(images)} Images Found!")

    MaxImages2download=2000
    if (len(images)>MaxImages2download): # image abuse.. Skip 
          print("Found more than ",MaxImages2download," images. No download for this abuse!") 
          return 0;

    if (len(images) == 0): return 0;

    allImages=[]
    for i in range(len(images)):
        #print(images[i])
        #print("TYPE=",type(images[i]))
        # first try
        try:
          if (images[i].get('src') !=None): allImages.append(images[i].get('src'))
        except IndexError:
                 pass

        # second try
        try:
          if (images[i].get('srcset') !=None): 
                                     ss= images[i].get('srcset').split();
                                     for i in range(len(ss)):
                                             allImages.append(ss[i])
        except IndexError:
                 pass

        # 3rd try
        try:
          if (images[i].get('data-srcset') !=None): 
                                     ss= images[i].get('data-srcset').split();
                                     for i in range(len(ss)): 
                                            allImages.append(ss[i])
        except IndexError:
                pass

        # 4th try
        try:
           if (images[i].get('data-src') !=None): allImages.append(images[i].get('data-src'))
        except IndexError:
           pass 


    headers = {'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'}
    tpcSession = requests.Session()

    for i in range(len(allImages)):
            image_link=allImages[i]
            if (image_link.find("//")==-1): continue

            if (averbose): print(i," ",allImages[i])
                        # 1.data-srcset
                        # 2.data-src
                        # 3.data-fallback-src
                        # 4.src
                        # 5.srcset 

            #newname=os.path.basename(image_link)
            newname = image_link.split("/")[-1]
            if (len(newname)<2): continue # too short name

            filename=folder_name+"/"+newname
            if (averbose):  print(count+1, ") downloading=",image_link," to "+filename)

            # correct link when starts with //
            xurl=image_link
            if (xurl.startswith("//")): xurl="https:"+image_link

            try:
                # r = requests.get(xurl, stream = True, timeout=(10, 300))
                r = tpcSession.get( xurl, headers=headers ) # re-used TCP connection
            except requests.exceptions.RequestException as e:  # This is the correct syntax
               print(e) 
               continue 
            except requests.ReadTimeout as e:
               print('Timed out: {}'.format(xurl))
               continue


            # check  svg used in for formulars. Wikipedia does not have file extention! 
            # formulars are made in SVG. The browser should know this by extension. 

            xnames=img_dir+"/"+newname
            if (newname.find(".")==-1):
                if (image_link.find("/svg/")>-1): 
                          filename=folder_name+"/"+newname+".svg";
                          xnames=img_dir+"/"+newname+".svg"; 

            # remeber replacements
            imageReplacer[image_link]=xnames

            # Check if the image was retrieved successfully
            if r.status_code == 200:
              xfile = open(filename, "wb")
              xfile.write(r.content)
              xfile.close()
              count = count+1
              if (averbose): print('Image sucessfully Downloaded: ',filename)
            else:
              print(image_link,' couldn\'t be retreived')
              pass

    tpcSession.close()
    print("<b>Total Images</b>: ",count)
    return count


# extract CSS
def extractCSS(soup):
    count=0
    for link in soup('link'):
        if link.get('href'):
            if link.get('type') == 'text/css' or link['href'].lower().endswith('.css') or 'stylesheet' in (link.get('rel') or []): 
                new_type = 'text/css' if not link.get('type') else link['type']
                css = soup.new_tag('style', type=new_type)
                css['data-href'] = link['href']
                for attr in link.attrs:
                    if attr in ['href']:
                        continue
                    css[attr] = link[attr]
                    r_url=link['href']
                    if (averbose): print(css[attr],r_url) 

                    try:
                       r = requests.get(r_url, stream = True, timeout=(10, 300))
                       #r = requests.get(r_url, allow_redirects=True, timeout=(5, 200))
                    except requests.ConnectionError:
                       print(" -> Connection error")
                       continue
                    except requests.ReadTimeout as e:
                       print('Timed out: {}'.format(r_url))
                       continue

                    newname = r_url.split("/")[-1]
                    filename=folder_css+"/"+newname
                    cssReplacer[ r_url ] = css_dir+"/"+newname 
                    count=count+1 
                    with open(filename,'w') as f:
                        f.write(r.text)

    print("<b>Other Assets</b>: ",count," files")
    return count 


# MAIN FUNCTION START
def main(html,wikitext):
    global aoutput, atitle, dirpath, asource
 
    # content of URL
    #r = requests.get(url)
 
    # remove disambiguation
    if (html.find("Help:Disambiguation")>-1 and html.find("articles associated")>-1): 
        print("Detected Disambiguation -> Skip")
        return 

    if (html.find("Redirect to:")>-1 and len(html)<1000):
             print("Redirect -> Skip")
             return

    if (atitle.find("Category:")>-1): return 
    if (atitle.find("User:")>-1): return 
    if (atitle.find("Help:")>-1): return 


    # make txt file too
    parser = html2text.HTML2Text()
    parser.ignore_links = True
    parser.ignore_images = True
    parser.body_width = 0
    parser.ignore_emphasis = True
    parser.wrap_links = True
    articletxt = parser.handle(html)
    xlines = articletxt.splitlines();
    non_empty_lines="";
    for line in xlines:
        line=line.strip()
        if len(line)<3: line="\n"; 
        if line=="|": line=" ";
        line=line.replace("---","\n")
        non_empty_lines += line + "\n"
        # print(len(line)," ",line)
    articletxt=non_empty_lines
    articletxt=re.sub(r'\n\s*\n', '\n\n', articletxt) # replace 2 new lines 
    articletxt=articletxt.strip()
    if (len(articletxt)<300):
          print("Article has length =",len(articletxt)," ->  too short. Exit!")
          return
    if (len(articletxt)<500):
        if (articletxt.find("no text in this page")>-1):
          return
    if (len(articletxt)<500):
        if (articletxt.find("redirect"))>-1: 
          return

    

    # Parse HTML Code
    soup = BeautifulSoup(html, 'html.parser')

    # internal title. Sometime ZWI file name does not correspond to actual title
    # internal_title=soup.h1;

    # nicely looking
    # html = soup.prettify()   #prettify the html

    # find all images in URL
    images = soup.findAll('img')
  
    # Call folder create function
    folder_create(images)

    # extract CSS
    extractCSS(soup)

    xmedia={} 
    htmlnew=html


#    print("<b>-></b> Make CSS replacements")
    for key in  cssReplacer:
      if (path.exists( dirpath+"/"+cssReplacer[key] )):
          cssfile=cssReplacer[key]
          xmedia[cssReplacer[key]]=fileSha1( dirpath+"/"+cssfile ) 
          if (averbose): print(key," replaced by ",cssfile)
          htmlnew=htmlnew.replace(key,cssfile)

#    print("<b>-></b> Make image replacements")
    n=0
    for key in  imageReplacer:
      if (path.exists( dirpath+"/"+imageReplacer[key] )):
           imageFile=imageReplacer[key]
           xmedia[imageReplacer[key]]=fileSha1(dirpath+"/"+imageFile)
           if (averbose): print(n, ")", key," replaced by ",imageFile)
           htmlnew=htmlnew.replace(key,imageFile)
           n=n+1


    # now remove lock file and write correct ZWI file
    if os.path.exists(aoutput):
         os.remove( aoutput )

    z = zipfile.ZipFile(aoutput, 'w', compression=zipfile.ZIP_DEFLATED)  # this is a zip archive
    z.writestr("article.html", htmlnew)
    z.writestr("article.wikitext", wikitext)
    z.writestr("article.txt", articletxt)

    ncategories=[]
    resCat = PCategories.findall(wikitext)
    print("<b>Categories found</b>: ",len(resCat));
    if (len(resCat)>0):
       for cat in resCat:
                    catText=cat.replace("\n","")
                    catText=catText.replace("_"," ");
                    catText=cat.strip()
                    strpBar=cat.split("|",1)
                    if (len(strpBar)>1): catText=strpBar[0].strip();
                    ncategories.append(catText);


    #for key in  imageReplacer:
    #    z.write(imageReplacer[key].encode(), imageReplacer[key].encode(), zipfile.ZIP_DEFLATED )
    zipdir(dirpath+'/data/', z)
    #htmltit=htmltit.replace(".html.gz","")
    #htmltit=htmltit.replace(".html","")

    primary="article.html";
    # sha1
    content={}
    content["article.html"]=make_sha1(htmlnew)
    content["article.wikitext"]=make_sha1(wikitext);
    content["article.txt"]=make_sha1(articletxt);

    license="CC BY-SA 3.0"
    topics=[]
    revisions=[]
    rating=[]
    # topics are set for HandWiki only
    if (asource=="handwiki"):
            tt=atitle.split(":");
            if (len(tt)>1): topics.append(tt[0])
    if (asource=="sep"):
             license="The Stanford Encyclopedia of Philosophy (SEP) license"


    if (len(wikitext)>5): primary="article.wikitext";

    # SEP is very special about file names
    # we need to extract it from H1
    if (asource=="sep"):
                     primary="article.html";
                     atitle=getHeading(soup,atitle);

    # get URL
    xpa=os.path.basename(aoutput);
    xpa=xpa.replace(".zwi","")
    xpa=urllib.parse.unquote(xpa)
    xpa=xpa.replace(" ","_")
    url=getURL(xpa, asource)
    atitle=atitle.replace("_"," ")

    # description and comment
    generator="MediaWiki"
    comment="";
    if (asource=="sep"):
            comment="Not for redistribution"
            generator="Custom CMS"


    description= getShortDescription(asource,wikitext,htmlnew,articletxt)
    print ("<b>Short description</b>: ",description,"<br>")

    metadata = {
            "ZWIversion":ZWI_VERSION, 
            "Title":atitle,
            "Lang":Lang,
            "Content":content, 
            "Primary":primary, 
            "Revisions":revisions,
            "Publisher":asource,
            "CreatorNames":[],
            "ContributorNames":[],
            "LastModified":stime,
            "TimeCreated":stime,
            "Categories":ncategories,
            "Topics":topics,
            "Description":description,
            "GeneratorName":generator,
            "Comment":comment,
            "Rating":rating,
            "License":license,
            "SourceURL":url
            }

    z.writestr("metadata.json", json.dumps(metadata,indent=4))
    z.writestr("media.json", json.dumps(xmedia,indent=4, sort_keys=True))
    z.close()

#    print("Cleared =",dirpath)    
#    shutil.rmtree(dirpath)    
    print("<b>Created</b>: ",aoutput)
 

# Main procesor
def makeZWI(html_file, zwi_file, ztitle, zsource):
  global aoutput, atitle, asource

  ainput=html_file
  aoutput=zwi_file
  atitle=ztitle
  asource=zsource
#  print("Input=",ainput)
#  print("Output=",aoutput)
  print("<b>Article</b>: ",atitle)
#  print("Encyclopedia source=",asource)
#  print("Is verbose=",averbose)

  # create progress file. It is useful to avoid locking when other progrm runs
  if (os.path.exists(aoutput) == True):
         b = os.path.getsize( aoutput )
         if (b<50):
                   print("Locking file=",aoutput," exists! Exit!");
                   return
  else:
         progress_file = open(aoutput, "w")
         progress_file.write('In progress')
         progress_file.close()


  HTML="";
  index=ainput
  try:

                if index.endswith('.html'):
                   ret = open(index, 'r', encoding='utf-8').read()
                elif index.endswith('.html.gz'):
                  ret =  gzip.open(index, 'rt',encoding='utf-8').read()

                # prepare file  header and footer
                data_head=open(script_dir+'/html_header.html', 'r', encoding='utf-8').read();
                data_footer=open(script_dir+'/html_footer.html', 'r', encoding='utf-8').read();
                HTML=data_head+ret+data_footer;
                HTML=urllib.parse.unquote(HTML) # replace %28 %29 with ()
  except IOError as err:
    print("Error with HTML creation")


  # get wikitex
  wikitextfile=index.replace(".html.gz",".wikitext.gz")
  wikitext="None";
  if os.path.exists(wikitextfile):
      wikitext =  gzip.open(wikitextfile, 'rt',encoding='utf-8').read()

# CALL MAIN FUNCTION
  main(HTML,wikitext)
   
