# Flourish Library
## History
- 2017-10-05: The library contained within here is version 0.9.0 which has been tweaked in two files as shown below. 

## Modifications
- 2015-08-21: fImage is changed on line 418 by ML on commit f33a4a36
- 2016-04-20: fMailbox is changed on lines 967-974 and 978-980 by ML on commit 025f1fca
- 2016-04-20: fMailbox is changed on line 987 by ML on commit abac3d99  
- 2017-10-05: Deleted the following files as they have not been used:
    - fBuffer
    - fCookie
    - fCRUD
    - fJSON
    - fLoader
    - fMessaging
    - fMoney
    - fORMColumn
    - fORMDate
    - fORMFile
    - fORMJSON
    - fORMMoney
    - fORMOrdering
    - fPagination
    - fTemplating
    - fValidation
    - fXML
- 2017-11-10: Namespaced, fixed codestyle 
- 2019-07-25 removed fMailbox as it is not used
- 2020-11-30: removed debug logging, it is never enabled
- 2020-11-30: removed PHP version checks - Flourish is older than PHP 7, so they don't matter anymore
- 2020-11-30: removed OS checks and resulting dead code - we only run on linux anyway
- 2020-11-30: Deleted all database related classes, they are not in use:
    - fActiveRecord
    - fDatabase
    - fORM
    - fORMDatabase
    - fORMRelated
    - fORMSchema
    - fORMValidation
    - fRecordSet
    - fResult
    - fSchema
    - fSQLException
    - fSQLSchemaTranslation
    - fSQLTranslation
    - fStatement
    - fUnbufferedResult
- 2020-11-30: Deleted fCache, and code that used it in fSession.
    We are using the native `redis` save handler, so this can go.
- 2020-11-30: Got rid of some leftover classes after the database cleanup
    - fAuthorizationException
    - fEmptySetException
    - fNoRowsException
    - fNotFoundException
    - fNumber
    - fRequest
- 2020-11-30: removed some more unused Flourish classes
    - fText
    - fEmail, fSMTP
    - fConnectivityException
- 2020-12-01: removed more Flourish classes by moving the functions used by other classes into those classes
    - fCryptography
    - fGrammar
    - fHTML
- 2020-12-01: slimmed down fAuthorization to only contain code that's actively used.
Also, get rid of two more classes that are now unused:
    - fURL
    - fUTF8
- 2020-12-01: slimmed down fUpload

## General descriptions of this folder
The folder is containing the classes of https://github.com/flourishlib/flourish-classes
It also contains a revision file and this description file
