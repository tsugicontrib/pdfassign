
PDF Annotator
=============

Upload, grade, and annotate a PDF file as an Assignment

This repository contains two PDF files are good samples
that you can use with the CloudConvert sandbox using the
following md5 checksums:

    e787a11a7b8b2c468feb176f586c92c4  why_sakai.pdf

    53d6fe6b688c31c565907c81de625046  input.pdf

Privacy
-------

Once a PDF file has been converted by CloudConvert,
the HTML is pulled back into this system and and served from
this system for viewing and annotation.  CloudConvert
does *not* retain either the PDF or the converted
HTML longer than 24 hours.  It is merely a conversion
service.

CloudConvert API Keys
---------------------

CloudConvert provides limited use (about 25 conversions per day)
production API keys for free. They also provide a 'sandbox' environment
for testing.  You have to explicity list the files you will use
with the sandbox, but there is no limit on the number
of conversions.

The sandbox has 
different keys than production
so when you switch between sandbox an production in this screen you need to
switch keys as well.

To get an API key, go to 
<a href=\"https://cloudconvert.com/\" target=\"_blank\">
cloudconvert.com</a> and create an account.  Then create an
API V2 key from your dashboard.  Give the key the
*user.read*,
*user.write*,
*task.read*,
and
*task.write* permissions and
copy and retain the API key and paste it into this tool's
configuration page.

CloudConvert has an excellent 'Jobs' dashboard that lets you monitor
your jobs in progress and completed and makes it easy to debug problems
with any conversions.  You can monitor this as students are uploading and
converting files to help diagnose issues they might be experiencing.


