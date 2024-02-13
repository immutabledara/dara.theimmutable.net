#!/usr/bin/env python
# -*- coding: utf-8 -*-
# Extract short description (when possible)
# We usually take up to 2 sentances, in several steps.
# S.Chekanov

from bs4 import *
import re,os
import sys, json, urllib.parse
from bs4 import BeautifulSoup
import html2text

def split_into_sentences(s):
    """Split text into list of sentences."""
    #s = re.sub(r"\s+", " ", s)
    #s = re.sub(r"[\\.\\?\\!]", "\n", s)
    #return s.split("\n")
    pat = re.compile(r'([A-Z,a-z][^.!?]*[.!?])', re.M)
    return pat.findall(s)


def getShortDescription(publisher,wiki,html,txt):
    """Extract short description form an article 
    Arguments:
        publisher: string representing a publisher (wikipedia, etc.)
        wiki: string representing wikicode of the article
        html: HTML version of the article (optional)  
        txt : string with plain text of article (optional)
    Returns:
        a short description of the article (1-2 sentences) 
    """
    # (1)
    # take up to 2 sentences from SEP
    term=""
    if (publisher == "sep"):
         soup = BeautifulSoup(html, 'html.parser')
         div = soup.find("div", {"id": "preamble"})
         content = str(div)
         parser = html2text.HTML2Text()
         parser.ignore_links = True
         parser.ignore_images = True
         parser.body_width = 0
         parser.ignore_emphasis = True
         parser.wrap_links = True
         articletxt = parser.handle(content)
         # print("Preamb=",articletxt)
         sen=split_into_sentences(articletxt)
         imax=2;
         if (len(sen)<imax): imax=len(sen)
         short="";
         for j in range(imax):
                short=short+" "+sen[j]
         term=short.strip();
         if (term.endswith(".") == True): term=term[0:len(term)-1] # no full stop 
         if (len(term)>20 and len(term)<1000): return term;

    # (2)a Do we have short description in wikicode?
    short1  = re.compile(r'{{short description(.*?)}}',  re.IGNORECASE)
    alist=short1.finditer(wiki)
    term=""
    for match in alist:
             s = match.start()
             e = match.end()
             subtext= wiki[s:e]
             sub=subtext.strip()
             subno=sub.replace("}}","")
             subsplit=subno.split("|")
             if (len(subsplit)>1):
                               term=subsplit[1].strip()
                               if (len(term)>10): break

    term=term.strip()
    if (term.endswith(".") == True): term=term[0:len(term)-1];
    words=term.split(" ")
    if (len(words)>2 and len(words)<50): return term

    # (2)b Do we have abstract in wikicode?
    short1  = re.compile(r'{{abstract(.*?)}}',  re.IGNORECASE)
    alist=short1.finditer(wiki)
    term=""
    for match in alist:
             s = match.start()
             e = match.end()
             subtext= wiki[s:e]
             sub=subtext.strip()
             subno=sub.replace("}}","")
             subsplit=subno.split("|")
             if (len(subsplit)>1):
                               term=subsplit[1].strip()
                               if (len(term)>10): break

    term=term.strip()
    if (term.endswith(".") == True): term=term[0:len(term)-1];
    words=term.split(" ")
    if (len(words)>2 and len(words)<50): return term


    # (3) Now try wikicode 
    # we will find a sentance with bold text
    # this is typical for wikipedia and handwiki
    term=""
    a_list = wiki.split("\n")
    for line in a_list:
         line=line.strip()
         if (len(line)<5): continue # too short
         breakets1=line.find("{{")
         if (breakets1>-1 and breakets1<5): continue # some box?
         imain=(line.lower()).find("{{see also");
         if (imain>-1):             continue
         if (line.startswith("==")): continue
         if (line.startswith("#")): continue
         if (line.startswith("*")): continue
         if (line.startswith("|")): continue
         if (line.find("[[File:")>-1): continue
         if (line.find("[[Image:")>-1): continue
         indx=line.find("'''")
         if indx>-1:
              term=line
              break

    # Cleaning. Remove internal links (even complex!)
    BreakFind=re.compile("\[\[(.*?)\]\]")
    mtxt=BreakFind.findall(term)
    for rtex in mtxt:
        dst=rtex.split("|",1) # first part   
        if (len(dst)>1): term=wiki.replace("[["+dst[0]+"|"+dst[1]+"]]",dst[1]) # proper internal link 
        if (len(dst)==1): term=wiki.replace("[["+dst[0]+"]]",dst[0]) # proper internal link 

    # no bold, no full stop. 
    term=term.replace("'''"," ")
    term=term.strip();
    if (term.endswith(".") == True): term=term[0:len(term)-1]; 
    term=re.sub(r'<ref(.+?)ref>', ' ', term)
    term=re.sub(r'{{.+?}}', ' ', term)
    a_list = term.split()
    term = " ".join(a_list)
    words=term.split(" ")
    if (len(words)>3 and len(words)<100): return term


    # Now most difficult part: plain text. Only if everything above failed.
    # take max 2 sentances, but each sentance should have 7 words
    a_list = txt.split()
    txt = " ".join(a_list)
    sen=split_into_sentences(txt)
    short="";
    nmax=2
    nn=0
    for j in range(len(sen)):
              sn=sen[j];
              if (sn.startswith("#")): continue
              if (sn.startswith("*")): continue
              if (sn.startswith("|")): continue
              words=sn.split(" ")
              if (len(words)>6 and sen[j].find("|")<0): # sentance without | and at least 6 words
                     short=short+" "+sen[j]
                     nn=nn+1
                     if (nn>nmax): break

    term=short.strip();
    if (term.endswith(".") == True or term.endswith("?") == True): term=term[0:len(term)-1] # no full stop
    if (len(term)>10 and len(term)<1000): return term;

    return "";
