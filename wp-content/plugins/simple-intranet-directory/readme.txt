===  Simple Intranet Directory  ===
Contributors: charlwood
Donate link: http://www.simpleintranet.org/
Tags: intranet, extranet, employee, user photo, company, directory, profile, staff, out of office
Requires at least: 3.5
Tested up to: 4.3
Stable tag: 3.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple employee directory with photos for your intranet.

== Description ==

*Simple Intranet Directory* is an easy to use employee directory listing of your users that includes extended profile information, user photos and a search function.  We have also added custom fields, an out of office alert function and sidebar widget, custom HTML biographies, an employee directory search widget and an employees sidebar widget.

Upgrade to the full version of our Simple Intranet (http://www.simpleintranet.org) plugin suite and get these features;

* searchable employee directory with photos with grid option
* privately secure part or all of your site as a company intranet
* company events calendar that syncs with Google Calendar
* online forms that you can customize for HR or contact inquiries
* Dropbox-like drag and drop front-end file management 
* bulk user import function from CSV file
* out of office expiry date and custom text options and widget
* employee of the month, company anniversary and employee birthday widgets
* drill-down detailed employee biography profiles (prepopulated or custom HTML)
* branded login and admin panels with your logo
* Facebook-like real-time activity feed for employee communication
* front-end user edited Wiki for group editing and collaboration
* employee online survey poll widget and archive
* an upcoming events calendar/listing page and widget with e-mail notifications
* most popular content sidebar widget (records views of all pages/posts)
* appointment and conference room bookings
* Question & Answer page function like Quora, Yahoo Answers, StackOverflow

NOTE: To upgrade you will need to deactivate and delete this Simple Intranet Directory plugin from your server.  Don't worry, your photos and employee data will however be saved and accessible in your the full version of the Simple Intranet plugin.

== Installation ==

Thank you for downloading our Simple Intranet Directory plugin.  Here is a quick primer on the installation and setup for installing our plugin-in.  

1. Download and unzip the plugin and copy/extract the "simple-intranet-directory" folder and all of its child files to the "wp-content/plugins" directory of your WordPress.org installation.  

2. You will then need to activate the Simple Intranet Directory plugin in the "Plugins" area of the Dashboard. 

3. Set up your employee directory by uploading employee photos via the "Users/Your Profile" menus.

4. Add a searchable employee directory listing by inserting the [employees] shortcode into a post or page. 

5. Optionally include the following parameters within the [employees] shortcode;
- include a search box with email, title or department pulldown menu options and filter out specific roles/groups e.g. [employees search="yes" email="yes" title="yes" department="yes" search_exclude="administrator"]
- set pixel width of avatar/employee photos e.g. [employees avatar="100"]
- include only specific groups/roles, sort by last_name (or first_name) in ascending or descending (ASC/DESC) order and limit to 10 people per page (25 is default) e.g. [employees group="subscriber" sort="last_name" order="ASC" limit="10"].
- include only specific users in a commas separated list [employees username="dsmith,rcharles"] 

6. View these and more options at the "Simple Intranet" menu item in your Dashboard or visit http://www.simpleintranet.org.

== Frequently Asked Questions ==

= How do I add the directory? =

Simply add the [employees] shortcode into any post or page.  Then update users profile information if you are an admin, or have your users update their own profiles under "Users / Your Profile" when they login. Limit to 25 employees per page using the limit parameter, display the search bar above the listing, exclude for example "board" and "executive" custom groups from search pull-down, set avatar pixel width to 100, display only Subscriber roles and sort by last name in ascending order as follows: [employees limit="25" search="yes" search_exclude="board,executive" avatar="100" group="subscriber" sort="last_name" order="ASC"].

= How can I change the look of my employee directory? =

You can edit the style sheet file called "si_employees.css" found in the /css folder.  This includes classes for the "employeephoto" and "employeebio" and "outofoffice" areas of the employee directory.

= Where do I add custom fields? =

In the "Users / Your Employee Profile" under the Country field you can add up to 3 custom fields.  Admins can edit the labels and all other roles can add content next to these labels.

= How does the Out of Office alert function work? =
In "Users / Your Profile" at the top of the page you will see "Out of office notification is OFF. | Turn ON.".  Click "Turn ON" to activate a message above your employee listing in the Employee Directory.   Go down to the bottom of your profile page, and edit the "Out of office text" field. Enter custom out of office notification text here, and it will show above your user photo in the Employee Directory, assuming you have activated the out of office alert at the top of the page.

= Can Admins change the out of office status for other users? =
Yes, they can edit any users profile information and just update the pull-down menu "Update out of office status" further down a user's profile page.  Just remember that when logged in as an Admin or other user, if you click to "Turn On or Off" the out of office alert at the top of the user profile page, that will only affect the logged in user out of office status (and not the user which you are editing).

= How do I set up more detailed employee profiles in the Employee Directory? =
We have added the ability to enable a custom editable biography page when you click-through from the main listing of the Employee Directory.  This will allow employees to upload files, add photos and customize the layout and formatting of their own profile page.  The first time the employee directory is activated (by adding the [employees] shortcode to any page or post), the detailed biography page will be prepopulated with all available information from the Users / Your Profile section of the Dashboard.  Once this page is accessed for the first time, it can then be edited and overwritten with custom content.

= Where do I find options to set the Employee Directory detailed pages? =
Login as an Administrator and "Visit Users / Your Profile" and scroll down to Company Information to view these selectable options:
* Check if you want to include a clickable profile page accessible by clicking on the photo or name in the Employee Directory.
Note, each person will have a post generated with their name as the title, and saved in the Employees category.
* Check to allow each user to create and edit a custom HTML detail/biography page. 
* Check to hide all e-mails from the Employee Directory. 

== Screenshots ==

1. This screen shot shows the Simple Intranet Directory with employee extended profile information and user photos/avatars.
2. This screen shot shows the round user photo/avatar option.

== Changelog ==
= 3.5 =
* Fixed pagination bug when directory appears on a static front page.

= 3.4 =
* Added the ability for admins to switch user photos from square to round in Your Profile area.

= 3.3 =
* Fixed some warnings and optimized for WordPress 4.1.

= 3.2 =
* Improved full name text sanitization for user profile links. Made the Out of Office and Employee widget avatars and user fullnames clickable back to their profile details when available. Updated instructions on included a selected set up users in the directory using the [employees username="admin,rbrown"] shortcode parameter.

= 3.1 =
* Adjusted out of office expiry checking function to account for timezone setting.

= 3.0 =
* Fixed out of office expiry checking function.

= 2.9 =
* Added a parameter to the employees shortcode to include only specific users in a commas separated list [employees username="dsmith,rcharles"]. Also renamed a function to prevent conflicts.

= 2.8 =
* Fixed the automatic Out of Office check function when a date is set.

= 2.7 =
* Moved flush_rewrite_rules function to only trigger once on activation. This may help solve some permalink issues in the directory with some installs. Also cleaned up isolated issues where blank employee fields were filled with previous employee data.

= 2.6 =
* Added address and postal/zip code user profile information to the front page of directory (left blank it will not appear).

= 2.5 =
* Adds two new parameters to the employees shortcode to allow for sorting the directory by default when loading.  For example, [employees sort="last_name" order="ASC"] would sort by last names in ascending order (starting with A to Z).

= 2.4 =
* Adds the option in Your Profile to allow public access for non-logged in users to view detailed employee profiles.

= 2.3 =
* Added European and South African phone formats to the directory for phone and fax numbers.

= 2.2 =
* Added title and department search parameters to the [employees] shortcode.  Default is [employees title="yes" department="yes"]

= 2.1 = 
* Fixed search_exclude array error appearing in older PHP versions.

= 2.0 =
*  Added lots of extra parameters to the [employees] shortcode. Limit to 25 employees per page using the limit parameter, display the search bar above the listing, exclude "board" and "executive" custom groups from search pull-down, set avatar pixel width to 100 and display only Subscriber roles as follows: [employees limit="25" search="yes" search_exclude="board,executive" avatar="100" group="subscriber"].

= 1.9 =
* Updated si_employees.css to fix width issues with Twenty Fourteen theme.

= 1.8 =
* Updated div and class labels to avoid theme conflicts.

= 1.7 =
* Provides an employee directory search function using the Search Employees widget.
* Optimizes directory for WordPress 3.8.

= 1.6 =
* We have completely overhauled the User Photo/Avatar functionality to make it easier to use and more reliable.  We have also optimized the Employee Directory search function by improving search relevance and loading times for queries by over 50%.  We have added 2 sidebar widgets: Out of Office, and Employees listings both with photos. We have also added a raft of drill-down clickable employee profile options for the Employee Directory.

= 1.5 =
* Added sorting order to Employee Directory results.  Default order is by user first name when directory page is loaded.  When text field is empty, and title or department is chosen, then results now show alphabetically (e.g. by what is selected in the pull-down search menu).

= 1.41 =
* Optimized the CSS formatting of the employee directory layout for select Internet Explorer versions, and updated formatting of new custom fields user profile inputs.

= 1.3 =
* Added 3 new custom fields where the administrator can edit the labels, and employees can fill in their data.
* Added an "Out of Office" alert system, which shows above the Employee Directory listings.  Text can be customized for the alert in the "Users / Your Profile" area.
* Optimized and tested for version 3.51 and the 3.6 beta version.

= 1.21 =
* Optimized speed of search function, added pull down menu for name, title, department.

= 1.2 =
* Updated phone numbers to make them clickable via all smart phones including iPhone and Android.
* Expanded employee search to title and departments as well as by name.

= 1.1 =
* Updated pagination formatting for over 25 records.

= 1.0 =
* First version of plugin.