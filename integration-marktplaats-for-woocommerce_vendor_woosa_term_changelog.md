## 1.1.0 - 2024-10-31

### Added

* New method `Module_Term::get_mapped_terms()` to retrieve the mapped terms

### Removed

* The method `Module_Term::is_already_created()` has been removed

## 1.0.4 - 2024-01-23

### Fixed

* While searching for an existing term, in case there is already another term with the same parent as the one that is searched then that term ID will be returned causing that the searched term will never be created

## 1.0.3 - 2023-12-07

### Fixed

* Searching terms will return both mapped and supplier categories which forces the plugin to use the first result

## 1.0.2 - 2023-08-03

### Changed

* Add an extra parameter to decide whether or not to delete empty terms
* The method `Module_Term::is_already_created()` is searching now only by the meta data in case is available
* The method `Module_Term::create()` adds the necessary meta data only under strict conditions

## 1.0.1 - 2023-07-04

### Fixed

* Remove the backslashes from the term name at creation