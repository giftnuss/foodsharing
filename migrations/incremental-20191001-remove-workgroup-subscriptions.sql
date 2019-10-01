DELETE * FROM(
	SELECT fs_theme_follower.*, fs_bezirk_has_theme.bezirk_id, fs_foodsaver_has_bezirk.foodsaver_id AS fsbId
	FROM fs_theme_follower
	JOIN fs_bezirk_has_theme ON fs_bezirk_has_theme.theme_id = fs_theme_follower.theme_id
	LEFT JOIN fs_foodsaver_has_bezirk ON fs_foodsaver_has_bezirk.bezirk_id = fs_bezirk_has_theme.bezirk_id AND fs_foodsaver_has_bezirk.foodsaver_id = fs_theme_follower.foodsaver_id
) AS x WHERE x.fsbId IS NULL;
