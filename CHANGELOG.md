# Changelog
All notable changes to this project will be documented in this file.


## [v0.2] - 2020-04-15

### Added
- Best support for sub queries filters : when using the WHERE or HAVING clause, the prefix PATABASE_COLUMN_LITERALL in the filter value allows to match with the result of the main query (insead of a non dynamic value).

### Changed
- **Possible break change** Drop support for php < 7.1
- **Possible break change** Columns, other than primary key,  are now nullable by default when creating a table (constraint 'NULL'). In previous releases, columns other than primary key created without explicit 'NULL' or 'NOT NULL' argument was created with 'NOT NULL' constraint. 
- **Possible break change** Removed DatabaseDriver::getVersion() and Datasource::getVersion().


## [v0.1] - 2017-06-30

### Initial release
- Support for Sqlite, Mysql, Postgresql


