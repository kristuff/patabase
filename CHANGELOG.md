# Changelog
All notable changes to this project will be documented in this file.


## [v.0.2] - 2020-04-XX
### Changed
- ** Possible break change** Drop support for php < 7.1
- ** Possible break change** No primary key Columns are now nullable by default when creating a table (constaint 'NULL'). In previous releases, columns created without explicit 'NULL' or 'NOT NULL' argument was created with 'NOT NULL' constraint. 
### Added
- Best support for sub queries filters : when using the WHERE or HAVING clause, the prefix PATABASE_COLUMN_LITERALL in the filter value allows to match with the result of the main query (insead of a non dynamic value).

## [v.0.1] - 2017-06-30
### Initial release
- Support for Sqlite, Mysql, Postgresql


