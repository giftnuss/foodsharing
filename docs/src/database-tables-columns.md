# Database tables and columns

Since tables are sorted alphabetically in [phpmyadmin](http://localhost:18081), sort them here alphabetically:

## fs_abholer
* decribes who fetches when where and if confirmed
* confirmed `1`, not yet confirmed `0`

## fs_abholzeiten
Contains information about regurlary reoccuring pickup slots. Additional single pickup slots are stored in #TODO: find table.
The columns to be described are

column | description | possible values |
--- | --- | --- |
dow | day of week | 1 = Monday, 0 = Sunday |
time | when on the day the pickup is | time |
fetcher | number of slots | >= 0, >= 1 enforced by frontend |

## fs_bezirk
describes the regions
* type: description in `src\Modules\Core\DBConstants\Region\Type`

## fs_foodsaver
* `deleted_at`: deletion day of account, if `NULL`, account is active

## fs_foodsaver_has_bezirk
describes which foodsaver is in which workgroups and regions

## anything with `theme`
`theme` are threads in the forum.

