# CSV Importer

Welcome to the CSV importer plugin for [Wordpress](https://wordpress.org).

It is still being developed but that won't hurt its usage. Everything works.

## Description

This plugin gives you the option to import CSV data through file import and stores it in a custom table or in post/user meta table.

## Installation

* Upload the zip through WordPress plugin admin or
* Upload the files by ftp to `wp-content/plugins`.
* Activate the plugin `CSV to WP` through the plugins page.

## Usage

* Upload CSV data through file upload
* Verify it
* Preview it
* Import it

## Actions

These actions are available:
* csv2wp_successful_csv_upload
* csv2wp_successful_csv_import

## Filters

These filters are available:
* csv2wp_import_options (array)
* csv2wp_delimiter (string)
* csv2wp_line_length (int)
* csv2wp_upload_folder (string)

## FAQ

= Can I import posts and other WordPress data with it ? =

No, not right now. There are other plugins which do that like [WordPress Importer](https://wordpress.org/plugins/wordpress-importer/). Maybe in the future but now there are no plans for it.
