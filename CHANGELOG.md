CHANGELOG
=========

* 1.1.1
    * Add getArrayCopy method on the Item class to extract all attributes
    * Fix Tests for parallel executions
    * Add Travis CI configuration

* 1.1.0 (Contribution by Tomonori Kusanagi )
    * Add update method
    * Add Excepted and ReturnValues options for Put and Delete methods
    * Put, Delete and Update methods return attribute array (when ReturnValues is not NONE).

* 1.0.3
    * Fix Context internal properties name
    * Remove useless constants
    * Add docs (PhpDocumentor2)
    * Update README

* 1.0.2
    * Fix Scan/Query result with no Items
    * Fix Put with empty attributes
    * Add ScanIndexForward option for Query Context

* 1.0.1
    * Add Readme
    * Add Changelog
    * Add cache config in the Connection constructor
    * Fixed typo

* 1.0.0
    * Initial import
