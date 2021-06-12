# Changelog
All notable changes to this project will be documented in this file.

## [v1.0] - 2021-06-12

### Changed
- **Possible break change** PHP Strict types.
- New `DatabaseDriver::isForeignKeyEnabled()` method (`bool`) added for compatibility (implemented on sqlite, return false by other drivers).

## [v0.5] - 2020-10-08

### Changed
- **Possible break change** Fix typo with Output class (instead of *Outpout*)


## [v0.4] - 2020-09-25

### Added
- Support for MIN select query.
- Support for MAX select query.


## [v0.3] - 2020-04-23

### Changed
- **Possible break change** Improve output formats definition. Using abstract class Output and its constants. `ASSO` change to `ASSOC` for associative arrays. 'COLS' change to 'COLUMN'  


## [v0.2] - 2020-04-15

### Added
- Best support for sub queries filters : when using the WHERE or HAVING clause, the prefix `_PATABASE_COLUMN_LITERALL_` in the filter value allows to match with the result of the main query (instead of a non dynamic value). 

### Changed
- **Possible break change** Drop support for php < 7.1
- **Possible break change** Columns, other than primary key,  are now nullable by default when creating a table (constraint 'NULL'). In previous releases, columns other than primary key created without explicit `NULL` or `NOT NULL` argument was created with `NOT NULL` constraint. 
- **Possible break change** Removed `DatabaseDriver::getVersion()` and `Datasource::getVersion()`.


## [v0.1] - 2017-06-30

### Initial release
- Support for Sqlite, Mysql, Postgresql
