# UIHook Plugin - SkinChanger

ILIAS Plugin for allocating roles to a skin.

##What does this plugin do?
This plugin makes it possible to allocate a role with a skin.  
When a user logs in and has a role that was allocated to a skin.  
For example: Administrator => adminSkin.  
The user will get that skin assigned.

If the skin is not available because it has been removed   
the user will get the default ilias skin, and an error message will be shown to the user.

It is also possible to use human-readable links like   
**www.mywebsite.com/databay60** to switch to the **databay60** skin (if available).  
This will also override the skin that was defined by allocating the users role with a skin.

## Requirement

* PHP: [![Minimum PHP Version](https://img.shields.io/badge/Minimum_PHP-7.4.x-blue.svg)](https://php.net/) [![Maximum PHP Version](https://img.shields.io/badge/Maximum_PHP-7.4.x-blue.svg)](https://php.net/)

* ILIAS: [![Minimum ILIAS Version](https://img.shields.io/badge/Minimum_ILIAS-6.x-orange.svg)](https://ilias.de/) [![Maximum ILIAS Version](https://img.shields.io/badge/Maximum_ILIAS-6.x-orange.svg)](https://ilias.de/)

## Installation

1. Clone this repository to **Customizing/global/plugins/Services/UIComponent/UserInterfaceHook**
2. Enter the plugins folder **SkinChanger** and execute this command: **composer install**
3. Login to ILIAS with an administrator account (e.g. root)
4. Select **Plugins** in **Extending ILIAS** inside the **Administration** main menu.
5. Search for the **SkinChanger** plugin in the list of plugin and choose **Install** from the **Actions** drop down.
6. Choose **Activate** from the **Actions** dropdown.
7. Choose **Configure** from the **Actions** dropdown to allocate roles with skins.  
The allocation table will only show available skins that were installed like described below.

##Nginx setup
The following line is required to allow changing/overriding the skin using a readable link.  
It has to be added to your servers config file (usually under /etc/nginx/sites-available).
````regexp
rewrite \/([A-Za-z0-9-_]+)$ /goto.php?target=skinChangeThroughLink&skin=$1 redirect;
````
Supported skin names can be changed/modified by changing the regex below.
````regexp
\/([A-Za-z0-9-_]+)$
````

## Skin installation
Place the skins folder into the folder located at **Customizing/global/skin/**  
Example for the databay60 skin: **Customizing/global/skin/databay60**  
In this example the databay60 folder then contains the template.xml file

### Bugs

None known