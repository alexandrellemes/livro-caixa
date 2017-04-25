/*  ------- MySQL ------
 *  If you desire to use MySQL to store the login / password
 *  combinations, I suggest you use a database with the following
 *  structure. Note however that you can also use other database, table
 *  and column names. They can be changed in the configuration file.
 *
 *  MySQL-Dump
 *  Database: phpSecurePages
 *  Table structure for table 'phpSP_users'
 */

CREATE TABLE phpSP_users (
   primary_key MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
   user VARCHAR(50) NOT NULL,
   password VARCHAR(32) NOT NULL,
   userlevel TINYINT(3),
   PRIMARY KEY (primary_key),
   KEY (user)
);


