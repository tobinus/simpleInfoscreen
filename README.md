# simpleInfoscreen
Web-based infoscreen software

## A note about licensing

This repository includes software from GreenSock. Please
see their [licensing page](https://greensock.com/licensing/). If your use
of simpleInfoscreen is not covered by their "No Charge" license, you must
either buy a Business License from GreenSock or remove their software from
the web/js folder (you can tell from the files' contents whether they are from
GreenSock or not).

## How to set up

There are a few requirements for the environment.

* PHP >= 5.3.0 (not tested with PHP 7)
* Apache server is set up
* PHP Curl support
* PHP XML support

1.  Clone this repository, by running `git clone https://github.com/tobinus/simpleInfoscreen.git`.
2.  Change into the new folder: `cd simpleInfoscreen`
3.  This project has two folders: one which can be placed inside the web root, and one which must
    be placed _outside_ the web root. You must decide where you want the public part to be
    (its path will decide what URL the infoscreen will be accessible at) and the private part
    (its path is of no importance).
4.  Move the private folder: `mv private /desired/path/to/private`
5.  Inside `private`, there is a `config` folder which has nothing but a `template` folder
    inside. You must copy all files from the `template` folder to the `config` folder.
6.  Edit the configuration files you copied to fit your use. There are detailed instructions in each of the files.
7.  Change the owner of the `data` folder so that whichever user the PHP code will run as, is the owner. Example:
    `sudo chown -R www-data: data` (assuming you're in the `private` folder).
8.  Move and rename the web folder (assuming you're back in the simpleInfoscreen folder): `mv web /web_root/desired/url`
9.  Edit `local.php` so that the relative path to the private folder is correct.
10. Edit `local.php` so the timezone is correct.
11. Change the owner of the `local` folder so that whichever user the PHP code will run as, is the owner. Example:
    `sudo chown -R www-data: local`
12. Ensure no other file or folder is owned by the user PHP runs as. This is a security precaution, so that even if
    an attacker gains control of the PHP application, he or she will not be able to do damage to the rest of the 
    server. Ideally, this application should run completely alone, in a Virtual Machine, since security was not
    a priority during development.
13. You should be all set now. Try navigating to the web page in a browser.

## Setting up the infoscreens themselves

Essentially, you want to make it so that when the computer boots up, it will
open up the infoscreen in a web browser, in full screen. There are several
ways to achieve this, depending on which operating system you use. Either way,
we recommend using Chromium/Chrome for the smoothest animations. Search for
"Google Chrome kiosk mode Windows" or something along the lines.

Raspberry Pi 2 and above works fine as a computer, if you haven't got
one yet.
