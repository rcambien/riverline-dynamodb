CHANGELOG
=========

* 2.0.2
    * Fix Reapter query method

* 2.0.1
    * Fix composer requirement

* 2.0.0
    * Migration to AWS PHP SDK 2.*

* 1.4.0
    * Add Logger
    * Add Scan and Query method to the Repeater
    * Fix BatchGet method in the Repeater
    * Replace GetLastKey method by GetNextContext in Collection
    * Replace SetLastKey method by setExclusiveStartKey in Context\Collection

* 1.3.0
    * Add BatchGet method
    * Add BatchWrite method
    * Add Repeater helper class for batch request
    * Add special ProvisionedThroughputExceededException

* 1.2.0
    * Merge table operation methods by Tomonori Kusanagi
    * Remove the return of tableDescription object for methods create, update and delete (for consistency)
    * Move the waitForTableToBeInState method to the Connection class (can be useful outside the tests)
    * Add more test for table operations

* 1.1.2
    * Use raw JSON response from the API to avoid bugged XML convertion (Fix #3)

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
