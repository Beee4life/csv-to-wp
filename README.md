# CSV Importer

Welcome to the CSV importer plugin for [Wordpress](http://wordpress.org). I built this initially for the IDF ([International Downhill Federation](http://internationaldownhillfederation.org)), to import rankings to user profiles, but I thought there's more that can be done with it.

It is still under development, although the upload, verification and remove parts work.

## Description 

This plugin gives you the option to import CSV data through file import or raw input and stores it in your database.

## Installation

* Upload the zip through WordPress plugin admin or
* Upload the files by ftp to `wp-content/plugins`.
* Activate the plugin `Action Logger` through the plugins page. 

## Usage

* Upload CSV data through file upload or raw import
* Preview it
* Import it

## FAQ

= Can I import posts and other WordPress data with it ? =

No, not right now. There are other plugins which do that like [https://wordpress.org/plugins/wordpress-importer/](WordPress Importer). Maybe in the future but now there are no plans for it.

### To Do
* [X] - Add setting who can import
* [X] - Preview data
* [X] - Read header columns
* [X] - Count header columns (for column verification)
* [ ] - Choose to insert where

## Changelog

**0.1**

Initial release
