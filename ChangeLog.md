JSON for the XP Framework ChangeLog
========================================================================

## ?.?.? / ????-??-??

## 3.1.0 / 2018-02-01

* Fixed `Output::write()` not to call the underlying stream's `close()`
  method implicitely, this is unexpected.
  (@thekid)

## 3.0.2 / 2017-08-19

* Fixed issue #12: Error reading lists ending with 0 - @thekid

## 3.0.1 / 2017-06-29

* Fixed issue #11: Sequential output and empty arrays/objects - @thekid

## 3.0.0 / 2017-06-04

* **Heads up:** Dropped PHP 5.5 support - @thekid
* Added forward compatibility with XP 9.0.0 - @thekid

## 2.3.1 / 2016-10-31

* Fixed detection for UTF-16 (LE, BE) encoding - @thekid
* Added maximum nesting level to all input sources - @thekid

## 2.3.0 / 2016-10-29

* Read [Parsing JSON is a Minefield](http://seriot.ch/parsing_json.html)
  and fixed various noncompliant behaviors:
  - Support for UTF-16 without BOM
  - Raise errors for unexpected delimiters in arrays or objects
  - Be stricter than `is_numeric()` when parsing numbers
  - Raise errors when encountering malformed or unclosed escape sequences
  - Implement a maximum nesting level for arrays and objects, default 512
  (@thekid)

## 2.2.0 / 2016-08-28

* Added forward compatibility with XP 8.0.0: Use File::in() instead of
  the deprecated *getInputStream()*
  (@thekid)

## 2.1.1 / 2016-06-24

* Fixed issue #9: Integer keys produce invalid JSON - @thekid 

## 2.1.0 / 2016-05-29

* Merged PR #7: Add support for all traversables to `Json::write()`
  (@thekid)
* Merged PR #8: Add reset() operation. Enable calling the `elements()`,
  `pairs()` and `read()` methids  again after explicitly resetting the 
  stream. This operation may raise an exception if the input is not
  seekable, e.g. if the underlying stream is socket I/O.
  (@thekid)

## 2.0.1 / 2016-05-28

* Merged PR #6: Fix unicode surrogate pairs not being handled correctly
  (@thekid)

## 2.0.0 / 2016-02-21

* Added version compatibility with XP 7 - @thekid

## 1.0.1 / 2016-01-24

* Changed code base to no longer use deprecated FILE_MODE_* constants
  (@thekid)

## 1.0.0 / 2015-12-13

* Merged PR #4: Simplify JSON API - @thekid

## 0.9.0 / 2015-02-25

* Ensured I/O objects are closed after reading / writing has finished
  (@thekid)
* Changed text.json.FileOutput and text.json.FileInput to not close open
  files passed to its constructor.
  (@thekid)
* Changed text.json.FileOutput and text.json.FileInput to also accept
  file names - @thekid

## 0.8.5 / 2015-02-12

* Changed dependency to use XP ~6.0 (instead of dev-master) - @thekid

## 0.8.4 / 2014-12-31

* Made available via composer - @thekid

## 0.8.3 / 2014-09-23

* First public release - (@thekid)
