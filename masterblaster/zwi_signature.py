#!/usr/bin/env python
# -*- coding: utf-8 -*-

# This program is used to sign ZWI file with a private key of the ZWI publisher.
# It can also remove the key ("unsign"). After a ZWI file is signed, one can validate 
# the signature (and the validity of the ZWI file) using a public key.
# The public key can be in a local file, or, if not found, can be located at remote PSQR. 
# Check arguments as: python3  ../../zwi_signature.py -h
# The program expects an input ZWI file, and 2 public (auth.pub) and private (auth.pem) keys.
#
# @version 1.0, June 8, 2022 
# S.V.Chekanov, L.Sanger, C.Gribneau  
# Organzation: KSF
# 

import re,os,sys,json

try:
    import jose
except ImportError:
    print("Error: Please install jose module https://github.com/mpdavis/python-jose")
    sys.exit()

from zipfile import ZipFile
import re,os,sys,json
from os.path import exists
import hashlib
from jose import jwt
from datetime import datetime
import requests
import argparse


# Task: Look inside the ZWI file and validate all sha1 hashes.
# If everything is OK, return True. Otherwise, return False.
# This is a slow, but it examines all files as needed.

nmeta=0
nmedia=0

def validate_files(unzipped_file, js_content, js_media):
  global nmeta,nmedia
  for key, val in js_content.items():
    nmeta=nmeta+1
    #print(key, val)
    name = unzipped_file.read(key)
    sha_1 = hashlib.sha1()
    sha_1.update(name)
    if (sha_1.hexdigest() !=  val):
        print("Error: Sha1 of the content ", key, " is wrong")
        return False 

  # if no media file
  if (js_media==None): return True

  for key, val in js_media.items():
    nmedia=nmedia+1
    #print(key, val)
    name = unzipped_file.read(key)
    sha_1 = hashlib.sha1()
    sha_1.update(name)
    if (sha_1.hexdigest() !=  val):
        print("Error: Sha1 of the media ", key, " is wrong")
        return False 
  return True


def find_between( s, first, last ):
    try:
        start = s.index( first ) + len( first )
        end = s.index( last, start )
        return s[start:end]
    except ValueError:
        return ""

#### input parameters ####
kwargs = {}
parser = argparse.ArgumentParser()
parser.add_argument('-q', '--quiet', action='store_true', help="don't show verbose")
parser.add_argument("-z", '--zwi', help="Input ZWI file. Output will be the same as input")
parser.add_argument("-d", '--dir', help="Input directory with unzipped ZWI file (for fast validation)")
parser.add_argument("-i", '--input', help="Input private key (to sign) or public key (to validate)")
parser.add_argument("-p", '--organization', help="Name of the organization (producer) of the input ZWI file")
parser.add_argument("-a", '--address', help="Address of the producer of the input ZWI file")
parser.add_argument("-k", '--kid', help="PSQR a key ID of decentralized identifiers (DIDs)")
parser.add_argument("-u", '--url', help="Validate using the URL of the publisher with the public key")
parser.add_argument("-v", '--validate', action='store_true', help="Validate with public key \"auth.pub\"")
parser.add_argument("-r", '--remove', action='store_true', help="Remove the signature from the ZWI file")
parser.add_argument("-f", '--full', action='store_true', help="Full check of all files inside ZWI (slow)")

# algorith in use
alg="RS256"

args = parser.parse_args()
args.verbose = not args.quiet
fast = not args.full
# print(fast,args.full)

if args.zwi != None:
    if (len(args.zwi)>4):
          print(" : Input ZWI file =",args.zwi)
if args.dir != None:
    if (len(args.dir)>4):
          print(" : Input directory with unzipped ZWI =",args.dir)
          fast=True

signature_file="signature.json"
if (args.remove):
     z = ZipFile(args.zwi,"r")
     try:
       sig = z.read(signature_file)
     except KeyError as e:
       print()
       print("Error: This file is not signed. Exit!")
       sys.exit()
     z.close()
     print("Remove signature=", args.remove )
     cmd="zip -d "+args.zwi+" "+signature_file
     os.system(cmd)
     print("Done! Exit.")
     sys.exit()


if (args.validate):
   unzipped_file=None 
   if (args.dir == None and args.zwi  != None):
           unzipped_file = ZipFile(args.zwi,"r")
           sig =      unzipped_file.read(signature_file)
           metadata = unzipped_file.read("metadata.json")
           sha_1 = hashlib.sha1()
           sha_1.update(metadata)
           data_metadata=sha_1.hexdigest()
           # media
           media =None
           data_media=None
           try:
             media =    unzipped_file.read("media.json")
             sha_1 = hashlib.sha1()
             sha_1.update(media)
             data_media=sha_1.hexdigest()
           except KeyError as e:
             pass

   if (args.dir != None and args.zwi  == None):
          #print(" : Reading local file:",args.dir +'/' + signature_file)
          #print(" : Reading local file:",args.dir +'/' + 'metadata.json')
          #print(" : Reading local file:",args.dir +'/' + 'media.json')
          with open(args.dir +'/' + signature_file,'r') as f:
                  sig = f.read()

          with open(args.dir +'/' + 'metadata.json','r') as f:
                  metadata = f.read()
                  sha_1 = hashlib.sha1()
                  sha_1.update(metadata.encode('utf-8'))
                  data_metadata=sha_1.hexdigest()
          with open(args.dir +'/' + 'media.json','r') as f:
                  media = f.read()
                  sha_1 = hashlib.sha1()
                  sha_1.update(media.encode('utf-8'))
                  data_media=sha_1.hexdigest()

   try: 
       signature=json.loads(sig)
       org=signature["identityName"]
       address=signature["identityAddress"]
       time=signature["updated"]
       token=signature["token"]

       if "alg" in  signature:
             ralg=signature["alg"];
             if len(ralg)>2: 
                  alg=ralg
                  print(" : Using algorithm =",alg)

       psqrKID=""
       if "psqrKid" in  signature:
             psqrKID=signature["psqrKid"];
       # the input was given instead?
       if (args.kid != None): 
                     psqrKID=args.kid 
       if (args.url != None):
                     psqrKID=args.url

       # read public key      
       publicKey=""
       if "publicKey" in  signature:
             publicKey=signature["publicKey"];

       local_file=False
       if (args.input != None): 
          if (exists(args.input)): local_file=True

       # start from none
       decoded_data=None

       # first check is any local pub file (to speed up)
       if (local_file==True and decoded_data==None): 
         print(" : Using local public key file =",args.input)
         with open(args.input) as pubkey_file:
               decoded_data = jwt.decode(token, key=pubkey_file.read(), algorithms=alg)

       # get locally stored public key
       if (len(publicKey)>390 and args.kid == None and args.url == None):
             local_file=False
             print(" : Using stored public key from ZWI")
             #print(publicKey)
             decoded_data = jwt.decode(token, key=publicKey, algorithms=alg)
             #print("decoded",decoded_data)

       if ( (local_file==False and decoded_data==None) or args.kid != None or args.url != None):  
         # convert to standard URL
         if (args.kid != None):
              print(" : Using remote public key from psqrKid =",psqrKID)
         if (args.url != None): 
              print(" : Using remote public key from URL =",args.url)
         xurl=psqrKID.replace("did:psqr:","https://");
         xurl=xurl.replace("#publish","auth.pub")
         tpcSession = requests.Session()
         headers = {'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'}
         try:
               r = tpcSession.get( xurl, headers=headers ) 
               decoded_data = jwt.decode(token, key=r.text, algorithms=alg)
               #print(r.content)
         except requests.exceptions.RequestException as e:  # This is the correct syntax
               print(e)
         except requests.ReadTimeout as e:
               print('Timed out: {}'.format(xurl))
         tpcSession.close()

       if (decoded_data != None):
             #print()
             #print("Decoded data with the  public key:")
             #print(decoded_data)
             metadata_read=decoded_data["metadata"]
             media_read=None
             if "media" in decoded_data: 
                   media_read=decoded_data["media"]

             authority_read=decoded_data["authority"]
             # print(data_metadata+" "+metadata_read)
             # print(data_media+" "+media_read)
             isValid=0
             if (data_metadata != metadata_read): isValid=1
             if (media_read!=None): 
                   if (data_media != media_read):       isValid=2
             if (signature["identityName"] != authority_read["identityName"]): isValid=3 
             if (signature["updated"] != authority_read["updated"]):                 isValid=3 
             if (signature["identityAddress"] != authority_read["identityAddress"]):           isValid=3  

             # validate all sha1 files?
             if (fast == False):
                  js_metadata=json.loads(metadata)
                  js_media=None
                  if (media != None): js_media=json.loads(media) 
                  js_content=js_metadata["Content"]
                  sha1Correct=validate_files(unzipped_file, js_content, js_media)
                  if (sha1Correct == False):
                       print("Error: Hash for some files are wrong! Exit!")
                       isValid=4
             if (isValid==0):
                  print(" : Signing organization =",signature["identityName"])
                  print(" : Address of signing organization =",signature["identityAddress"])
                  print(" : Time when it was signed",signature["updated"])
                  if (fast==False):
                          print(" : Nr of files varified =",nmeta,"(metadata), ",nmedia,"(media)")
                  print(" : Is fast validation =",fast)
                  print(" == Signature is valid! == ")
                  sys.exit()

             if (isValid==1):
                  print("")
                  print(" Error: Inconsistent hash of metadata")
             if (isValid==2):
                  print("")
                  print(" Error: Inconsistent hash of media data")
             if (isValid==3):
                  print("")
                  print(" Error: Information stored in token is not consistent with human-readable info")
                  print(" Error: This indicate that somebody tried to change signature.json")
             if (isValid==4):
                  print("")
                  print(" Error: Inconsistent hashes of stored files")
             if (isValid != 0):
                 print("== Signature is NOT valid! == ")
                 sys.exit()
       else:
                print(" Error: jwt.decode returns empty data")
                sys.exit()

   except KeyError as e:
       print("Key error:",str(e))
       print("Error: This file is not signed. Exit!")
       sys.exit()
   if (unzipped_file!= None): unzipped_file.close()
   sys.exit()

print(" : Signing organization =",args.organization)
print(" : Address of signing organization =",args.address)
print(" : PSQR  key ID =",args.kid)
print(" : Is verbose   =",args.verbose)
print(" : Is fast validation =",fast)

# time ISO 8601
now = datetime.now()
dt_string = now.strftime("%Y-%m-%dT%H:%M:%S.%f%z")

# input ZWI file
zfile=args.zwi
sha_1 = hashlib.sha1()
unzipped_file = ZipFile(zfile, "r")
metadata = unzipped_file.read("metadata.json")
js_metadata=json.loads(metadata)
js_content=js_metadata["Content"]

# check if exists
js_media=None
try:
       media = unzipped_file.read("media.json")
       js_media=json.loads(media)
except KeyError as e:
       pass 
       #print("Error: This file does not have media.json",e)


# validate all sha1 files? 
if (fast == False): 
   sha1Correct=validate_files(unzipped_file, js_content, js_media)
   if (sha1Correct == False):
      print("Error: Hash for some files are wrong! Exit!")
      sys.exit(1)

# data to decode
data={}
# who signed added
data["authority"]={'identityName':args.organization, "identityAddress":args.address, "psqrKid":args.kid,"updated":dt_string} 

sha_1 = hashlib.sha1()
sha_1.update(metadata)
data["metadata"]=sha_1.hexdigest()

if (js_media != None):
  sha_1 = hashlib.sha1()
  sha_1.update(media)
  data["media"]=sha_1.hexdigest()

if (args.verbose):
  print()
  print("Data that goes to token of the "+signature_file)
  print(data)

unzipped_file.close()

# Encode data with private key
file_exists = exists(args.input)
if (file_exists == False):
    print("Error: No private key was found in",args.input)
    sys.exit()

with open(args.input) as key_file:
    token = jwt.encode(data, key=key_file.read(), algorithm=alg)

# we also read public key and insets it
pubFile=args.input.replace(".pem",".pub")
file_exists = exists(pubFile)
if (file_exists == False):
    print("Error: No public key was found in",pubFile)
    print("Error: We include public key in the signature to make decentralizated verification",pubFile)
    sys.exit()
print(" : Private key used     =",args.input)
print(" : Public key included  =",pubFile)
print(" : Used algorithm =",alg)
print(" : Signature time =",dt_string)

pubkey="";
with open(pubFile, 'r') as f1:
    pubkey = f1.read()
    #pubkey=pubkey.replace("-----BEGIN PUBLIC KEY-----", "")
    #pubkey=pubkey.replace("-----END PUBLIC KEY-----", "")
    #pubkey=pubkey.replace("\n", "")
    #pubkey=pubkey.strip()

if (args.verbose):
  print()
  print("Created token:")
  print(token)

json_string = {'identityName':args.organization, "identityAddress":args.address, "psqrKid":args.kid, "token":token, "publicKey":pubkey,"alg":alg,"updated":dt_string}
with open(signature_file, 'w') as outfile:
    outfile.write( json.dumps(json_string, indent=4) )

# decode data:
# with open(args.input) as pubkey_file:
#    decoded_data = jwt.decode(token, key=pubkey_file.read(), algorithms='RS256')

#if (args.verbose):
#  print()
#  print("Decoded data with the  public key:")
#  print(decoded_data)


print("Signature "+signature_file+" was inserted")
os.system("zip -q "+zfile+" "+signature_file)
os.remove(signature_file)

