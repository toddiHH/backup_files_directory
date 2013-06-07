backup_files_directory
======================

Concrete5 addon that provides a one-click backup of a site's content and database.

**WARNING WARNING WARNING:** This addon is intended for use during site development (for example, to quickly copy the content of a staging site to your local development machine) and you should **NOT** leave it installed on live production sites!! See the "Security" section below for more details.

**USE AT YOUR OWN RISK!!! WE MAKE NO GUARANTEES ABOUT THE SAFETY OF THIS ADDON!!** We have been using it ourselves and have not run into problems on our end, **BUT THIS IN NO WAY GUARANTEES THAT YOU WILL NOT HAVE PROBLEMS!!**

##Compatibility
This addon has been tested with Concrete5.6.1.2 (it might work with lower versions, but we haven't tried it yet).

## Installation
 1. Download the ZIP of this repository (or clone it).
 2. Copy the `/packages/backup_files_directory` folder from the unzipped / cloned project into your site's `/packages/` directory.
 3. Install the addon via your site's "Install" (a.k.a. "Add Functionality") dashboard page.

## How It Works
Once installed, you will see a new dashboard page called "Backup Files Directory" in the `System & Settings > Backup & Restore` section.
From this dashboard page, you can click "Create New Backup" to create a ZIP file of your site's `/files/` directory.
If you leave the "and a database backup too" box checked, it will also create a database backup which will then be included in the new backup ZIP file (in the `/files/backups/` directory).
After you've downloaded your site backup, it is **HIGHLY** recommended that you delete the backup (and probably go and delete the database backup as well from the `System & Settings > Backup & Restore > Backup Database` dashboard page).

## Details
The ZIP file contains all of the contents of your site's `/files/` directory (and subdirectories), *EXECPT* the following:

 * Contents of the `/files/cache/` directory
 * Contents of the `/files/tmp/` directory
 * Contents of the `/files/trash/` directory

Also note that we temporarily change the file permissions of all database backup files in `/files/backups/` to `666` before adding them to the ZIP archive, and then change them back to `000` afterwards.

##Security
The most important thing to note is that you should absolutely, positively, under no circumstances leave site backups on your live server!
If you need to get a backup of a live site, we recommend you do it in the following way, and do all of these steps very quickly:

 1. Upload the addon to your live site (you didn't leave this thing lying around in your live site's `/packages/` directory, did you?!)
 2. Install the addon on your live site (you didn't leave this thing installed on the live site, did you?!)
 3. Make the backup
 4. Download the backup
 5. **DELETE THE BACKUP FROM THE SERVER** (this is the absolute most important and critical thing to do)
 6. Delete the database backup from the server (if applicable) via the "Backup Database" dashboard page
 7. Uninstall the addon (seriously -- do *not* leave this thing installed on a live site because an admin user might accidentally create site backups and forget to delete them)
 8. Delete the addon from your server (`/packages/backup_files_directory/`) -- seriously, do *not* leave this thing on a live site because an admin user might install it and accidentally create site backups without deleting them afterwards!

Note that any user who can access the dashboard and has the "Can Backup" task permission enabled can create backups with this tool.
