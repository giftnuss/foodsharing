# Database Fixup
The current database has broken foreign key relationships:
 - No foreign key relationships defined
 - No ON DELETE triggers (as well as no programatic deletion)
 - The wish for some invalid data to be there, for some not.

## What should be done?
The goal is to get the database free from logic errors, so an ORM can handle it.
The most important tables for now are
 - fs_foodsaver
 - fs_betrieb
 - fs_betrieb_team
 - fs_bezirk
 - fs_conversation
 - fs_foodsaver_has_conversation
 - fs_msg

# Table-by-table analysis
## fs_abholer
Stores filled pickup slots, references foodsaver_id betrieb_id
Needed to generate statistics (count, but not weight).

### todo
Have deleted entries from *fs_foodsaver* and *fs_betrieb* reappear.
Don't care about that in existing code. Used to show pickup history, but broken old entries seem fine.

## fs_abholzeiten
Stores recurring, total pickup slots, references betrieb_id

### todo
Remove all non-existent references to *fs_betrieb*, add `ON DELETE CASCADE`.
Add foreign key relationship to *fs_betrieb*
Reasoning: The data only affects future pickups that will not occur in case a store is removed.

## fs_answer
Stores answers to quiz.

### todo
Nothing. Maybe add foreign key relationship to *fs_question* as well as `ON DELETE CASCADE`.
Reasoning: Answers to removed questions are not needed, existing quiz sessions get a copy in `fs_quiz_session.quiz_result`.

## fs_apitoken
Stores tokens that are currently only used to get an ICAL calendar of future events/pickups.

### todo
Remove tokens for not existing users, add foreign key relationship to *fs_foodsaver* as well as `ON DELETE CASCADE`.

## fs_application_has_wallpost
Link table between applications (for groups) and the wallposts. *Design broken*: Links foodsaver ids to the wallposts, e.g. all applications a foodsaver does to somewhere

### todo
Remove entries for non-existing users, add `ON DELETE CASCADE` to *fs_foodsaver*.
Remove entries for non-existing wallposts, add `ON DELETE CASCADE` to *fs_wallpost*
Reasoning: Applications texts for deleted users are not useful anymore

## fs_basket
Lists all foodbaskets.

### todo
Check code to never display foodbaskets from removed users except to admins.

## fs_basket_anfrage
Lists foodbasket requests.

### todo
Remove entries for non-existing users, add `ON DELETE CASCADE` to *fs_foodsaver*.
Remove entries for non-existing baskets, add `ON DELETE CASCADE` to *fs_basket*.
Reasoning: Requests for non existant users do not need to be kept, basket FK is purely defensive.

## fs_basket_has_art
Combines foodbaskets with different types of food. *Unused*: Has to be entered but is never evaluated.

### todo
remove in code (in future), leave database as is (for now)

## fs_basket_has_types
See fs_basket_has_art

## fs_basket_has_wallpost
Link table between baskets and the wallposts (public)

### todo
Remove entries for non-existing wallposts, add `ON DELETE CASCADE` to *fs_wallpost*
Remove entries for non-existing baskets, add `ON DELETE CASCADE` to *fs_basket*
Reasoning: It is just a link table, remove entries where data is missing.

## fs_bell
Stores arbitrary notifications

## fs_betrieb
Stores stores.

### todo
