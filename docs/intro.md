# Intro

This library is mainly designed to import data, export is in the raw condition (even it's working in basic form),
under development and not documented yet.

The important notes:

- It uses ActiveRecord models and PHPExcel library, so operating big data requires pretty good hardware, especially RAM.
In case of memory shortage I can advise splitting data into smaller chunks.
- This is not just a wrapper on some PHPExcel methods, it's a tool helping import data from Excel in human readable
form with minimal configuration.
- This is designed for periodical import.
