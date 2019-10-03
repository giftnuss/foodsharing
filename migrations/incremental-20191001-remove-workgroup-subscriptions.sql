DELETE
    fs_theme_follower
FROM
    fs_theme_follower
    JOIN fs_bezirk_has_theme ON fs_bezirk_has_theme.theme_id = fs_theme_follower.theme_id
    LEFT JOIN fs_foodsaver_has_bezirk ON fs_foodsaver_has_bezirk.bezirk_id = fs_bezirk_has_theme.bezirk_id AND fs_foodsaver_has_bezirk.foodsaver_id = fs_theme_follower.foodsaver_id
WHERE fs_foodsaver_has_bezirk.foodsaver_id IS NULL;
