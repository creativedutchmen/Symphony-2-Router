## URL Router ##

Version: 0.3  
Author: [Rob Philp](robert.h.philp@gmail.com)  
Build Date: 2010-04-07  
Requirements: Symphony 2.0.7  

### Installation ###

1. Upload the files into a folder named "router" in your Symphony 'extensions' folder.

2. Enable it by selecting "URL Router" on the "System -> Extensions" page, choosing "Enable" from the with-selected menu, and clicking "Apply".

3. Add your rules to the "System -> Preferences" page.
	

### Notes ###

### Changelog ###

0.4 ([Huib Keemink](http://creativedutchmen.com)):
* Cleaned up save code
* Added per filter redirect option. (used to be a plugin wide setting)

0.3 ([Max Wheeler](http://makenosound.com)):

* Remove `router.js` as it's no longer needed
* Fix error in save logic, no longer tries to write to `config.php`

0.2 ([Max Wheeler](http://makenosound.com)):

* Only add `router.js` to the `/system/preferences/` page (was breaking other JS)
* Updated README with correct installation details

0.1:

* Initial release, brief documentation forthcoming.
