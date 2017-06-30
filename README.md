![logo](https://patabase.kristuff.fr/inc/img/logo/logo-square-48x48.png)
 Kristuff Patabase 
=================

A database/server SQL query builder for PHP.

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/398308d1225049f58ae583065608c460)](https://www.codacy.com/app/kristuff_/patabase?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=kristuff/patabase&amp;utm_campaign=Badge_Grade)
[![Code Climate](https://codeclimate.com/github/kristuff/patabase/badges/gpa.svg)](https://codeclimate.com/github/kristuff/patabase)
[![Build Status](https://travis-ci.org/kristuff/patabase.svg?branch=master)](https://travis-ci.org/kristuff/patabase)
[![codecov](https://codecov.io/gh/kristuff/patabase/branch/master/graph/badge.svg)](https://codecov.io/gh/kristuff/patabase)


Website
-------

[patabase.kristuff.fr](https://patabase.kristuff.fr) 


Features
--------
- Easy to use, easy to hack, fast and very lightweight
- Supported drivers: Sqlite, Mysql, Postgresql
- Requires only PDO
- Full [online documentation](https://patabase.kristuff.fr/doc) 

SQL Features
------------

Database queries:
```
- SELECT FROM:   
    DISTINCT, all / column(s), columns(s)/alias, function(COUNT, SUM), sub select, 
    JOIN        (INNER JOIN, LEFT OUTER JOIN, RIGHT OUTER JOIN (*), FULL OUTER JOIN (*))
    WHERE       (=, !=, <, <=, >, >=, IN, NOT IN, NULL, NOT NULL), 
    GROUP BY, 
    HAVING      (COUNT, SUM), 
    ORDER BY    (ASC, DESC, RAND (*)), 
    LIMIT, 
    OFFSET
- INSERT INTO 
- DELETE FROM
    WHERE       (=, !=, <, <=, >, >=, IN, NOT IN, NULL, NOT NULL), 
- UPDATE 
    WHERE       (=, !=, <, <=, >, >=, IN, NOT IN, NULL, NOT NULL), 
- CREATE TABLE (DEFAULT, PRIMARY KEYS, FOREIGN KEYS, NULL/NOT NULL)
- RENAME TABLE
- TABLE EXISTS
- SHOW TABLES
- DROP TABLE
- ENABLE FOREIGN KEY (*)
- DISABLE FOREIGN KEY (*)
- ADD FOREIGN KEY (*)
- DROP FOREIGN KEY (*)
```

Server queries (*):
```
- CREATE DATABASE
- CREATE USER
- USER EXISTS
- DATABASE EXISTS
- SHOW USERS 
- SWOW DATABASES
- GRANT USER
- DROP DATABASE
- DROP USER
```
(*) some feature may be unavailable on some driver.

Requirements
------------

- PHP >= 5.6
- PDO extension: Sqlite, Mysql or Postgresql


License
-------

The MIT License (MIT)

Copyright (c) 2017 Kristuff

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
