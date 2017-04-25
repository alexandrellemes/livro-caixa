/**************************************************************/
/*         phpSecurePages version 0.44 beta (04/02/15)        */
/*              Copyright 2015 Circlex.com, Inc.              */
/*                                                            */
/*          ALWAYS CHECK FOR THE LATEST RELEASE AT            */
/*              http://www.phpSecurePages.com                 */
/*                                                            */
/*              Free for non-commercial use only.             */
/*               If you are using commercially,               */
/*         or using to secure your clients' web sites,        */
/*   please purchase a license at http://phpsecurepages.com   */
/*                                                            */
/**************************************************************/


------ Introduction ------
- Easy security for your pages!
- Make different groups of users each with their own rights.
- Identify the user and use his/her data in your site.
- Restrict access by IP address or IP address range
- Can be used with or without a database.
- Fully customisable screens and configuration.
- Multiple-language support.
- Works with PHP4 and PHP5.

With this web application installed, you'll be able to secure your pages in a very fast and simple way. Just add 1 row of PHP code to your page, and access is only for those that are allowed. You can implement it with your existing database, or just put the data in the configuration file. Do you want to have different levels of security? No problem, just create different user groups and give them their own rights. Furthermore, the data of the user (login name, password, user level and ID) is after login available to be used in your pages. Do you want to change the look of the login screen? No problem, just change the HTML code in 'interface.php' to create your own design. And don't forget, you can customize this program  to use your own native language.


------ What's New ------
Look in the file changelog.txt for the latest changes
Also check my site for a list of Frequently Asked Questions:
http://www.phpSecurePages.com/


------- requirements ------
MySQL is optional, phpSecurePages can be used with or without MySQL.


------- Installation ------
- Extract the files in the directory 'phpSecurePages' on your server.
- Make sure that all .php files are handled by the server through the PHP parser.
- Edit the configuration in the file 'config.php' --> (see section 'configuration').
- Put the required lines of code on your HTML pages --> (see section 'workings').
- If you use a database then create the required tables. --> (see section 'MySQL').


------- Configuration ------
Edit the file 'config.php' to change the configuration. Make sure you read the comments added on each row.
- First provide the required information about the installation of this program.
- Then choose whether you will be using passwords encrypted with the MD5 algorithm.
- Then choose if you want to use a database, or just put the login data in this configuration file. If both are set to 'true', then the database is used. Note that it is possible to use more then 3 accounts of data in the configuration file. Just add more blocks of variables, while incrementing the index number of the array.
- Enter the required information for your chosen method.
- The usage of user levels is optional. Just leave it empty if you decide not to use it.
- The same is true for user ID, they are also optional. Just leave it empty if you decide not to use it.


After that, add the required code to your HTML pages as described in the section 'workings' below.


------ Workings ------
For examples of the described workings, look into the code of the provided test files. These are not necessary for the working of this application, and can safely be deleted if you might want too.

To make a page safe, without the use of user levels, simply add the following line as the very first line of every page:

        <?PHP
                $cfgProgDir =  'phpSecurePages/';
                include($cfgProgDir . "secure.php");
        ?>

Above line is only correct with a default installation of course. If you installed the program elsewhere on the server, make sure you change the address accordingly, so that it points to the configuration file. When someone now tries to view this page, he/she is first asked to login, before the page is showed.


--- Workings: user levels ---
If you want to use different user levels, then you must first group your users in different groups and give a number to each group (don't use 0). Then decide which group is allowed to view each page. Instead of the above line, add the following line (in which you put the allowed user groups) at the top of each page:

        <?PHP
                $requiredUserLevel = array(1);
                $cfgProgDir =  'phpSecurePages/';
                include($cfgProgDir . "secure.php");
        ?>

Example: If you have 4 user groups and group 1 and 3 are allowed to view a certain page, then the code would be as followed:

        <?PHP
                $requiredUserLevel = array(1, 3);
                $cfgProgDir =  'phpSecurePages/';
                include($cfgProgDir . "secure.php");
        ?>

Furthermore, it is also possible to supply a minimal required user level. If the user has a higher level than the supplied number, he is also allowed access. To accomplish that all users of level 5 and higher are allowed, the following code should be used:

        <?PHP
                $minUserLevel = 5;
                $cfgProgDir =  'phpSecurePages/';
                include($cfgProgDir . "secure.php");
        ?>

Both methods can be used simulationously, for instance the following code gives access to the users of level 2, 4 & 6 and higher:

        <?PHP
                $requiredUserLevel = array(2, 4);
                $minUserLevel=6; 
                $cfgProgDir =  'phpSecurePages/';
                include($cfgProgDir . "secure.php");
        ?>


--- Workings: Ip restricted Access ---
If you need to additionally restrict access to an IP address or an IP address range, you can turn on IP restricted access.
phpSecurePages will (always) first ask for the users login and password, as normal.
After login and pass have been entered, if $use_IP_restricted_access is set to TRUE, the check for valid IP address occurs.
The array $allowed_addr[$i] is used to hold the IP address(es) ranges that will be allowed access.
If the user's IP does not match what you put into $allowed_addr[$i], they will be rejected.

To restrict logins by IP, you will need to: 
-- Make sure that you have set $use_IP_restricted_access to TRUE In config.php.
-- Put at least one IP address or IP range in the array $allowed_addr[$i] (see examples below).

To restrict access to a single IP:
$allowed_addr[$i] = array ("10", "1", "0", "1"); limits access to users with the IP address of 10.1.0.1.

You can also restrict by IP subnets by adding more or fewer items to the array. For example:

$allowed_addr[$i] = array ("10","1","0") limits access to users with an IP address in the range of 10.1.0.1 to 10.1.0.255.

$allowed_addr[$i] = array ("10","1") limits access to users with an IP address in the range of 10.1.0.1 to 10.1.255.255.

The $i referenced above is shown to let you know that the array must be numbered on a one-up basis (starting with zero). 

For example, here is an sample acceptable array to limit access to one specif IP address, and two additional subnets:

$allowed_addr[0] = array ("12", "15", "12", "1");  // Limits logins to a single IP: 12.15.12.1
$allowed_addr[1] = array ("191", "12", "12");      // Additionally limits logins to IP addresses in the range of 191.12.12.1 to 192.12.12.255
$allowed_addr[2] = array ("216", "200");           // Additionally limits logins to IP addresses in the range of  216.200.0.1 to 216.200.255.255.

You can add as many elements to this array as you need.


--- Workings: logout ---
To log out, simply make a link to a page, on which you add the following line of code (here it is also necessary to change the location if you installed the program in an other directory):

        <?PHP
                $logout = true;
                $cfgProgDir = 'phpSecurePages/';
                include($cfgProgDir . "secure.php");
        ?>

--- Workings: variables ---
After the program has run, the following variables are set, and can thus be used in the remaining code of your page. Use for instance <?PHP echo $ID ?> to write the user's ID code, or use it in a query to a database to gather more information about this user.
login name:   $login
password:     $password
user level:   $userLevel
ID code:      $ID


------- MySQL ------
If you desire to use MySQL to store the login / password combinations, I suggest you use a database with the following structure. Note however that you can also use other database, table and column names. They can be changed in the configuration file.


# MySQL-Dump
# Database: phpSecurePages
# Table structure for table 'phpSP_users'

CREATE TABLE phpSP_users (
   primary_key MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
   user VARCHAR(50) NOT NULL,
   password VARCHAR(32) NOT NULL,
   userlevel TINYINT(3),
   PRIMARY KEY (primary_key),
   KEY (user)
);


------- Language Support ------
This program can give output in multiple languages. English is set as default.
Change the variable $languageFile in the configuration file 'config.php' to use another language file. 
Language files can be found in the /lng directory.
The following languages are supported:
- Arabic
- Brazilian-Portuguese
- Bulgarian
- Catalan
- Croatian
- Czech
- Chinese Big5
- Chinese GB
- Danish
- Dutch
- English
- Estonian
- Euskara
- Finnish (2 versions)
- French
- German
- Icelandic
- Indonesian
- Italian
- Japanese (2 versions)
- Latvian
- Lithuanian
- Luxembourgish
- Macedonian
- Norwegian
- Polish
- Portuguese
- Romanian
- Russian - cp1251 (for windows)
- Russian - KOI8-R (for unix)
- Serbian
- Slovak
- Slovenian
- Spanish
- Spanish (Latin America)
- Swedish

I would love to receive new language files. If your native language is not present yet, please translate the file 'lng_english.php' and send it to me, so I can add support for it in this program.


------- Security ------
This application is meant to block everyone from you pages, who doesn't know the right login and password combination. When properly installed it is not possible to login to your site without this data. However, this does not provide a 100% secureness to your site (personally I don't think that this is possible on the Internet). Let me identify some issues you should be aware of, if you try to secure your site. (None of these issues can be attributed to this program. They are about security of your site in general).

- If your pages are not parsed by a PHP parser, there is no password checking taking place. Everything on the page can then be viewed by everyone. Make sure you have configured your server to parse the PHP files.

- If somebody is able to crack into your server, they can most likely also gain access to the files stored on it. If so, this security is also passed. Make sure you have a secure server and that your applications are updated to the newest version.

- Login names and passwords are send over the Internet in a non-secure manner. This means that if somebody is tapping the information, they can get hold of the login names, the passwords and the data on the pages. To create a secure connection (one with encryption), contact your server provider.


------ License ------
See the complete license in the license.txt file.

Short version:

phpSecurePages is free for non-commercial use.

You are allowed to:

- Share — to copy, distribute and transmit the work
- Use a modified version of the code in your application

You are not allowed to:

- Publish modified versions of the code
- Use the code in anything commercial without first obtaining a license

The software may be used without fee if such usage is limited to non-commercial pursuits. It is explicitly forbidden, to sell this software or otherwise make money out of it, without approval of the author. To use this software on a commercial basis as described above you must contact the author for terms.
If this software is used for free, the copyright line in the file interface.php may NOT be removed or altered in such a way that it becomes less (or un-) readable.

For commercial usage it is now possible to buy a license online. For a small site the price is $25 USD. Go to phpsecurepages.com for the easiest way to get it. Contact me at info -at- phpsecurepages.com if you have any questions about licenses.


------ Disclaimer ------
By using this application, the user agrees that he/she is self responsible for the safety of his/her pages. The writer of this program does not accept any responsibility for the safety of your pages, and the possible loss resulted by the lack thereof.
The software is provided "as is", without warranty of any kind, express or implied. In no event shall the author be liable for any claim, damages or other liability, whether in an action of contract, tort or otherwise, arising from, out of or in connection with the software or the use or other dealings in the software.


------ Final ------
Note however that this application is still in beta development. Therefore this author cannot guarantee that it is completely bug and fault free. However, be assured that extensive testing has been taken place before this application was distributed.
Thanks to the following people for coding and/or testing phpSecurePages:
Paul Kruyt, Joshua Macadam, dfocus, Arno van de Kolk, Alejandro Vásquez, Richard M. Pavonarius, Fabiano R. Prestes, Matteo Bettineschi, Christian Schims, R. Tenenbaum, Stéphane Hoyau, Manuel Soriano, Dean Lin, Manuel Herrera, Mercury He, Henrik Blicher Hansen, Art Koval, Sorin Sfirlogea, Joan Manel López, Oskar, Dimiter Stankov, Frantisek Repkovsky, Markus Bernhard, Jan Hunter, Ingimundur Gunnar Nielsson, Martin Hubacek, Per Egil Kummervold, Panu Artimo, Markku Lappalainen, Suryo Sucipto, Janez Vrenjak, Mitsushi Sugimoto, Andris Jershovs, Marek Kotsulim

Got a suggestion or want to submit a patch / code enhancement?  Feel free to send it to me: info -at- phpsecurepages.com

The latest releases, more information and a demonstration setup can be found at:
    http://www.phpSecurePages.com
More information can also be found at FreeCode.com:
    http://freecode.com/projects/phpSecurePages
