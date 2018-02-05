.. UQMarkup documentation master file, created by
   Andrew on Mon Nov 28 20:30:26 2016.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

Guide for Administrators
====================================

Installation
========================
This guide is intended as a process for installing the UQMarkup server software in a university environment.

Step 1: Server configuration
The UQMarkup server has been designed to work on a linux platform.  Other platforms may be appropriate, but have not been tested.

Install CentOS (recommended space 150GB)
Install Services:
	Apache2 (httpd)
	PHP (httpd bridge)
	MySQL
	Sendmail (or other apache compatible mail client)
	SSHD
	Webdav (via httpd)
Configure Server:
	- The server requires a public hostname (eg: uqmarkup.ceit.uq.edu.au)
	- SSH is highly recommended to be configured with apache (https)
Open ports globally:
  - 80/tcp   open  http
  - 443/tcp  open  https
Open internal ports if required:
	- 22/tcp   open  ssh
	- 25/tcp   open  smtp
	- 111/tcp  open  rpcbind
	- 631/tcp  open  ipp
	- 3306/tcp open  mysql
	- 8080/tcp open  http-proxy
Configure directories (see the attached zip file):
	root:root /var/www - This should be the apache working directory (htdocs)
	root:root /var/www/html/index.php - This should perform a redirect to the UQMarkup installation directory
	root:root /var/www/html/{uqmarkup directory} - This is where the UQMarkup files should be stored
	root:root /var/www/webdav - This is where the UQMarkup assignments are stored
Configure Apache:
	- Webdav must be enabled
	- Mod_Rewrite must be enabled
	- SSL.conf must be setup (with webdav)
Webdav configuration (ssl.conf - see below)
Import SQL into MySQL
	Create uqmarkup database
	Import tables from uqmarkup_schema.sql
Configure UQMarkup
	Configure /app/Config/database.php for MySQL settings
	Configure /app/Controllers/Component/LdapComponent.php for LDAP settings

Maintenance
========================
- The only maintenance requirements are that enough free disk space remains available on the servers.

WebDav Configuration
========================
#    WEBDAV FOR UPLOADS
    Alias /uploads /var/www/webdav/uploads
    <Location /uploads>
        DAV On
        Options Indexes MultiViews
        AuthType Basic
        AuthName "Please authenticate with your credentials"
        AuthBasicProvider ldap
        #AuthLDAPURL ***Add your authentication***
        AuthLDAPGroupAttributeIsDN on
        Require valid-user
    </Location>

    RewriteEngine On
    RewriteLog /var/log/httpd/rewrite.log
    RewriteLogLevel 2
    #RewriteRule ^/webdav$ /webdav/ [R=301]
    #RewriteCond %{LA-U:REMOTE_USER} (.*)
    RewriteRule ^/uploads(.*) /var/www/webdav/uploads/%{LA-U:REMOTE_USER}$1

