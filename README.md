CakePHP Askimet Component
=========================

Askiment Spam Check for CakePHP

Have all methods necessary to connect with the Askimet api. 

- verify key: Key verification need only be used if you will have different users with their own Akismet licenses using your platform. This call will verify that they are using a valid API key.
- comment check: Comment check is used to ask Akismet whether or not a given post, comment, profile etc. is spam
- submit spam and submit ham: Submit Spam and Submit Ham are follow-ups to let Akismet know when it got something wrong (missed spam and false positives). These are also very important and you shouldn’t develop using the Akismet API without a facility to include reporting missed spam and false positives.

For more info on what parameters to pass on each method, check: http://akismet.com/development/api/

Requires
========
- An Askimet API key: The development key can be got from: http://akismet.com/contact/ and the Askimet API key can be got from: https://akismet.com/signup/ .

Current Version
===============
The current version of the component have the basic working skeleton and the necessary methods to perform a key check, verify a comment, submit a spam or ham.

Next Version
============
- Code cleanup
- More debug options
- documentation on code

Notes
=====
- Currently it is structured to work with CakePHP v2.x, but I believe that with a few tweaks it can easily works with previous versions
