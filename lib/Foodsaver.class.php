<?php
class FoodsaverDb extends Db
{
	public function __construct()
	{
		parent::__construct();
		$this->cache = array();
		
		$this->cache['fs_abholen'] = array();
		$this->cache['fs_abholer'] = array();
		$this->cache['fs_abholzeiten'] = array();
		$this->cache['fs_activity'] = array();
		$this->cache['fs_autokennzeichen'] = array();
		$this->cache['fs_betrieb'] = array();
		$this->cache['fs_betrieb_has_lebensmittel'] = array();
		$this->cache['fs_betrieb_kategorie'] = array();
		$this->cache['fs_betrieb_notiz'] = array();
		$this->cache['fs_betrieb_status'] = array();
		$this->cache['fs_betrieb_team'] = array();
		$this->cache['fs_bezirk'] = array();
		$this->cache['fs_blog_entry'] = array();
		$this->cache['fs_botschafter'] = array();
		$this->cache['fs_bundesland'] = array();
		$this->cache['fs_content'] = array();
		$this->cache['fs_document'] = array();
		$this->cache['fs_email_status'] = array();
		$this->cache['fs_faq'] = array();
		$this->cache['fs_faq_category'] = array();
		$this->cache['fs_foodsaver'] = array();
		$this->cache['fs_foodsaver_has_bezirk'] = array();
		$this->cache['fs_geoRegion'] = array();
		$this->cache['fs_glocke'] = array();
		$this->cache['fs_glocke_read'] = array();
		$this->cache['fs_kette'] = array();
		$this->cache['fs_land'] = array();
		$this->cache['fs_language'] = array();
		$this->cache['fs_lebensmittel'] = array();
		$this->cache['fs_login'] = array();
		$this->cache['fs_mail_error'] = array();
		$this->cache['fs_message'] = array();
		$this->cache['fs_message_tpl'] = array();
		$this->cache['fs_pass_gen'] = array();
		$this->cache['fs_pass_request'] = array();
		$this->cache['fs_plz'] = array();
		$this->cache['fs_region'] = array();
		$this->cache['fs_send_email'] = array();
		$this->cache['fs_stadt'] = array();
		$this->cache['fs_stadt_kennzeichen'] = array();
		$this->cache['fs_stadtteil'] = array();
		$this->cache['fs_upgrade_request'] = array();
	}
		
	
	public function get_abholen()
	{
		$out = $this->q('
			SELECT 	 	
			`foodsaver_id`,
			`betrieb_id`,
			`dow`,
			`time`
					
			FROM 		`'.PREFIX.'abholen`');
		
		return $out;
	}
					
	public function getBasics_abholen()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'abholen`');
	}
					
	public function getOne_abholen($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`foodsaver_id`,
			`betrieb_id`,
			`dow`,
			`time`
					
			FROM 		`'.PREFIX.'abholen`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_abholer()
	{
		$out = $this->q('
			SELECT 	 	
			`foodsaver_id`,
			`betrieb_id`,
			`date`,
			`confirmed`
					
			FROM 		`'.PREFIX.'abholer`');
		
		return $out;
	}
					
	public function getBasics_abholer()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'abholer`');
	}
					
	public function getOne_abholer($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`foodsaver_id`,
			`betrieb_id`,
			`date`,
			`confirmed`
					
			FROM 		`'.PREFIX.'abholer`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_abholzeiten()
	{
		$out = $this->q('
			SELECT 	 	
			`betrieb_id`,
			`dow`,
			`time`,
			`fetcher`
					
			FROM 		`'.PREFIX.'abholzeiten`');
		
		return $out;
	}
					
	public function getBasics_abholzeiten()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'abholzeiten`');
	}
					
	public function getOne_abholzeiten($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`betrieb_id`,
			`dow`,
			`time`,
			`fetcher`
					
			FROM 		`'.PREFIX.'abholzeiten`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_activity()
	{
		$out = $this->q('
			SELECT 	 	
			`foodsaver_id`,
			`zeit`
					
			FROM 		`'.PREFIX.'activity`');
		
		return $out;
	}
					
	public function getBasics_activity()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'activity`');
	}
					
	public function getOne_activity($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`foodsaver_id`,
			`zeit`
					
			FROM 		`'.PREFIX.'activity`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_autokennzeichen()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`land_id`,
			`name`,
			`title`
					
			FROM 		`'.PREFIX.'autokennzeichen`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_autokennzeichen()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'autokennzeichen`
			ORDER BY `name`');
	}
					
	public function getOne_autokennzeichen($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`land_id`,
			`name`,
			`title`
					
			FROM 		`'.PREFIX.'autokennzeichen`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_betrieb()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`betrieb_status_id`,
			`bezirk_id`,
			`plz`,
			`stadt`,
			`lat`,
			`lon`,
			`kette_id`,
			`betrieb_kategorie_id`,
			`name`,
			`str`,
			`hsnr`,
			`status_date`,
			`status`,
			`ansprechpartner`,
			`telefon`,
			`fax`,
			`email`,
			`begin`,
			`besonderheiten`,
			`ueberzeugungsarbeit`,
			`presse`,
			`sticker`,
			`abholmenge`
					
			FROM 		`'.PREFIX.'betrieb`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_betrieb()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'betrieb`
			ORDER BY `name`');
	}
					
	public function getOne_betrieb($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`betrieb_status_id`,
			`bezirk_id`,
			`plz`,
			`stadt`,
			`lat`,
			`lon`,
			`kette_id`,
			`betrieb_kategorie_id`,
			`name`,
			`str`,
			`hsnr`,
			`status_date`,
			`status`,
			`ansprechpartner`,
			`telefon`,
			`fax`,
			`email`,
			`begin`,
			`besonderheiten`,
			`ueberzeugungsarbeit`,
			`presse`,
			`sticker`,
			`abholmenge`
					
			FROM 		`'.PREFIX.'betrieb`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
			$out['foodsaver'] = $this->qCol('
				SELECT 		`foodsaver_id`

				FROM 		`'.PREFIX.'abholen`
				WHERE 		`betrieb_id` = '.$this->intval($id).'
			');
			$out['foodsaver'] = $this->qCol('
				SELECT 		`foodsaver_id`

				FROM 		`'.PREFIX.'abholer`
				WHERE 		`betrieb_id` = '.$this->intval($id).'
			');
			$out['dow'] = $this->qCol('
				SELECT 		`dow_id`

				FROM 		`'.PREFIX.'abholzeiten`
				WHERE 		`betrieb_id` = '.$this->intval($id).'
			');
			$out['lebensmittel'] = $this->qCol('
				SELECT 		`lebensmittel_id`

				FROM 		`'.PREFIX.'betrieb_has_lebensmittel`
				WHERE 		`betrieb_id` = '.$this->intval($id).'
			');
			$out['foodsaver'] = $this->qCol('
				SELECT 		`foodsaver_id`

				FROM 		`'.PREFIX.'betrieb_team`
				WHERE 		`betrieb_id` = '.$this->intval($id).'
			');
				
		return $out;
	}
	
	public function get_betrieb_has_lebensmittel()
	{
		$out = $this->q('
			SELECT 	 	
			`betrieb_id`,
			`lebensmittel_id`
					
			FROM 		`'.PREFIX.'betrieb_has_lebensmittel`');
		
		return $out;
	}
					
	public function getBasics_betrieb_has_lebensmittel()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'betrieb_has_lebensmittel`');
	}
					
	public function getOne_betrieb_has_lebensmittel($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`betrieb_id`,
			`lebensmittel_id`
					
			FROM 		`'.PREFIX.'betrieb_has_lebensmittel`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_betrieb_kategorie()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'betrieb_kategorie`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_betrieb_kategorie()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'betrieb_kategorie`
			ORDER BY `name`');
	}
					
	public function getOne_betrieb_kategorie($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'betrieb_kategorie`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_betrieb_notiz()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`foodsaver_id`,
			`betrieb_id`,
			`milestone`,
			`text`,
			`zeit`
					
			FROM 		`'.PREFIX.'betrieb_notiz`');
		
		return $out;
	}
					
	public function getBasics_betrieb_notiz()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'betrieb_notiz`');
	}
					
	public function getOne_betrieb_notiz($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`foodsaver_id`,
			`betrieb_id`,
			`milestone`,
			`text`,
			`zeit`
					
			FROM 		`'.PREFIX.'betrieb_notiz`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_betrieb_status()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'betrieb_status`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_betrieb_status()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'betrieb_status`
			ORDER BY `name`');
	}
					
	public function getOne_betrieb_status($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'betrieb_status`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_betrieb_team()
	{
		$out = $this->q('
			SELECT 	 	
			`foodsaver_id`,
			`betrieb_id`,
			`verantwortlich`,
			`active`
					
			FROM 		`'.PREFIX.'betrieb_team`');
		
		return $out;
	}
					
	public function getBasics_betrieb_team()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'betrieb_team`');
	}
					
	public function getOne_betrieb_team($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`foodsaver_id`,
			`betrieb_id`,
			`verantwortlich`,
			`active`
					
			FROM 		`'.PREFIX.'betrieb_team`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_bezirk()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`parent_id`,
			`has_children`,
			`name`,
			`email`,
			`email_pass`,
			`email_name`
					
			FROM 		`'.PREFIX.'bezirk`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_bezirk()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'bezirk`
			ORDER BY `name`');
	}
					
	public function getOne_bezirk($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`parent_id`,
			`has_children`,
			`name`,
			`email`,
			`email_pass`,
			`email_name`
					
			FROM 		`'.PREFIX.'bezirk`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
			$out['foodsaver'] = $this->qCol('
				SELECT 		`foodsaver_id`

				FROM 		`'.PREFIX.'botschafter`
				WHERE 		`bezirk_id` = '.$this->intval($id).'
			');
			$out['foodsaver'] = $this->qCol('
				SELECT 		`foodsaver_id`

				FROM 		`'.PREFIX.'foodsaver_has_bezirk`
				WHERE 		`bezirk_id` = '.$this->intval($id).'
			');
			$out['foodsaver'] = $this->qCol('
				SELECT 		`foodsaver_id`

				FROM 		`'.PREFIX.'upgrade_request`
				WHERE 		`bezirk_id` = '.$this->intval($id).'
			');
				
		return $out;
	}
	
	public function get_blog_entry()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`bezirk_id`,
			`foodsaver_id`,
			`active`,
			`name`,
			`teaser`,
			`body`,
			`time`,
			`picture`
					
			FROM 		`'.PREFIX.'blog_entry`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_blog_entry()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'blog_entry`
			ORDER BY `name`');
	}
	
	public function get_botschafter()
	{
		$out = $this->q('
			SELECT 	 	
			`foodsaver_id`,
			`bezirk_id`
					
			FROM 		`'.PREFIX.'botschafter`');
		
		return $out;
	}
					
	public function getBasics_botschafter()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'botschafter`');
	}
					
	public function getOne_botschafter($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`foodsaver_id`,
			`bezirk_id`
					
			FROM 		`'.PREFIX.'botschafter`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_bundesland()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`land_id`,
			`name`
					
			FROM 		`'.PREFIX.'bundesland`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_bundesland()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'bundesland`
			ORDER BY `name`');
	}
					
	public function getOne_bundesland($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`land_id`,
			`name`
					
			FROM 		`'.PREFIX.'bundesland`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_content()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`name`,
			`title`,
			`body`,
			`last_mod`
					
			FROM 		`'.PREFIX.'content`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_content()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'content`
			ORDER BY `name`');
	}
					
	public function getOne_content($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`name`,
			`title`,
			`body`,
			`last_mod`
					
			FROM 		`'.PREFIX.'content`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_document()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`name`,
			`file`,
			`body`,
			`rolle`
					
			FROM 		`'.PREFIX.'document`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_document()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'document`
			ORDER BY `name`');
	}
					
	public function getOne_document($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`name`,
			`file`,
			`body`,
			`rolle`
					
			FROM 		`'.PREFIX.'document`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_email_status()
	{
		$out = $this->q('
			SELECT 	 	
			`email_id`,
			`foodsaver_id`,
			`status`
					
			FROM 		`'.PREFIX.'email_status`');
		
		return $out;
	}
					
	public function getBasics_email_status()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'email_status`');
	}
					
	public function getOne_email_status($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`email_id`,
			`foodsaver_id`,
			`status`
					
			FROM 		`'.PREFIX.'email_status`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_faq()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`foodsaver_id`,
			`faq_kategorie_id`,
			`name`,
			`answer`
					
			FROM 		`'.PREFIX.'faq`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_faq()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'faq`
			ORDER BY `name`');
	}
					
	public function getOne_faq($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`foodsaver_id`,
			`faq_kategorie_id`,
			`name`,
			`answer`
					
			FROM 		`'.PREFIX.'faq`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_faq_category()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'faq_category`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_faq_category()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'faq_category`
			ORDER BY `name`');
	}
					
	public function getOne_faq_category($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'faq_category`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_foodsaver()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`autokennzeichen_id`,
			`bezirk_id`,
			`new_bezirk`,
			`want_new`,
			`rolle`,
			`plz`,
			`stadt`,
			`bundesland_id`,
			`lat`,
			`lon`,
			`photo`,
			`photo_public`,
			`email`,
			`passwd`,
			`name`,
			`admin`,
			`nachname`,
			`anschrift`,
			`telefon`,
			`handy`,
			`geschlecht`,
			`geb_datum`,
			`fs_id`,
			`anmeldedatum`,
			`orgateam`,
			`active`,
			`data`,
			`about_me_public`
					
			FROM 		`'.PREFIX.'foodsaver`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_foodsaver()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'foodsaver`
			ORDER BY `name`');
	}
					
	public function getOne_foodsaver($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`autokennzeichen_id`,
			`bezirk_id`,
			`new_bezirk`,
			`want_new`,
			`rolle`,
			`plz`,
			`stadt`,
			`bundesland_id`,
			`lat`,
			`lon`,
			`photo`,
			`photo_public`,
			`email`,
			`passwd`,
			`name`,
			`admin`,
			`nachname`,
			`anschrift`,
			`telefon`,
			`handy`,
			`geschlecht`,
			`geb_datum`,
			`fs_id`,
			`anmeldedatum`,
			`orgateam`,
			`active`,
			`data`,
			`about_me_public`
					
			FROM 		`'.PREFIX.'foodsaver`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
			$out['betrieb'] = $this->qCol('
				SELECT 		`betrieb_id`

				FROM 		`'.PREFIX.'abholen`
				WHERE 		`foodsaver_id` = '.$this->intval($id).'
			');
			$out['betrieb'] = $this->qCol('
				SELECT 		`betrieb_id`

				FROM 		`'.PREFIX.'abholer`
				WHERE 		`foodsaver_id` = '.$this->intval($id).'
			');
			$out['betrieb'] = $this->qCol('
				SELECT 		`betrieb_id`

				FROM 		`'.PREFIX.'betrieb_team`
				WHERE 		`foodsaver_id` = '.$this->intval($id).'
			');
			$out['bezirk'] = $this->qCol('
				SELECT 		`bezirk_id`

				FROM 		`'.PREFIX.'botschafter`
				WHERE 		`foodsaver_id` = '.$this->intval($id).'
			');
			$out['email'] = $this->qCol('
				SELECT 		`email_id`

				FROM 		`'.PREFIX.'email_status`
				WHERE 		`foodsaver_id` = '.$this->intval($id).'
			');
			$out['bezirk'] = $this->qCol('
				SELECT 		`bezirk_id`

				FROM 		`'.PREFIX.'foodsaver_has_bezirk`
				WHERE 		`foodsaver_id` = '.$this->intval($id).'
			');
			$out['glocke'] = $this->qCol('
				SELECT 		`glocke_id`

				FROM 		`'.PREFIX.'glocke_read`
				WHERE 		`foodsaver_id` = '.$this->intval($id).'
			');
			$out['date'] = $this->qCol('
				SELECT 		`date_id`

				FROM 		`'.PREFIX.'pass_gen`
				WHERE 		`foodsaver_id` = '.$this->intval($id).'
			');
			$out['rolle'] = $this->qCol('
				SELECT 		`rolle_id`

				FROM 		`'.PREFIX.'upgrade_request`
				WHERE 		`foodsaver_id` = '.$this->intval($id).'
			');
				
		return $out;
	}
	
	public function get_foodsaver_has_bezirk()
	{
		$out = $this->q('
			SELECT 	 	
			`foodsaver_id`,
			`bezirk_id`,
			`active`
					
			FROM 		`'.PREFIX.'foodsaver_has_bezirk`');
		
		return $out;
	}
					
	public function getBasics_foodsaver_has_bezirk()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'foodsaver_has_bezirk`');
	}
					
	public function getOne_foodsaver_has_bezirk($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`foodsaver_id`,
			`bezirk_id`,
			`active`
					
			FROM 		`'.PREFIX.'foodsaver_has_bezirk`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_geoRegion()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'geoRegion`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_geoRegion()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'geoRegion`
			ORDER BY `name`');
	}
					
	public function getOne_geoRegion($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'geoRegion`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_glocke()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`name`,
			`msg`,
			`url`,
			`time`
					
			FROM 		`'.PREFIX.'glocke`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_glocke()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'glocke`
			ORDER BY `name`');
	}
					
	public function getOne_glocke($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`name`,
			`msg`,
			`url`,
			`time`
					
			FROM 		`'.PREFIX.'glocke`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
			$out['foodsaver'] = $this->qCol('
				SELECT 		`foodsaver_id`

				FROM 		`'.PREFIX.'glocke_read`
				WHERE 		`glocke_id` = '.$this->intval($id).'
			');
				
		return $out;
	}
	
	public function get_glocke_read()
	{
		$out = $this->q('
			SELECT 	 	
			`glocke_id`,
			`foodsaver_id`,
			`unread`
					
			FROM 		`'.PREFIX.'glocke_read`');
		
		return $out;
	}
					
	public function getBasics_glocke_read()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'glocke_read`');
	}
					
	public function getOne_glocke_read($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`glocke_id`,
			`foodsaver_id`,
			`unread`
					
			FROM 		`'.PREFIX.'glocke_read`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_kette()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`name`,
			`logo`
					
			FROM 		`'.PREFIX.'kette`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_kette()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'kette`
			ORDER BY `name`');
	}
					
	public function getOne_kette($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`name`,
			`logo`
					
			FROM 		`'.PREFIX.'kette`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_land()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'land`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_land()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'land`
			ORDER BY `name`');
	}
					
	public function getOne_land($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'land`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_language()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`name`,
			`short`
					
			FROM 		`'.PREFIX.'language`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_language()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'language`
			ORDER BY `name`');
	}
					
	public function getOne_language($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`name`,
			`short`
					
			FROM 		`'.PREFIX.'language`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_lebensmittel()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'lebensmittel`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_lebensmittel()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'lebensmittel`
			ORDER BY `name`');
	}
					
	public function getOne_lebensmittel($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'lebensmittel`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
			$out['betrieb'] = $this->qCol('
				SELECT 		`betrieb_id`

				FROM 		`'.PREFIX.'betrieb_has_lebensmittel`
				WHERE 		`lebensmittel_id` = '.$this->intval($id).'
			');
				
		return $out;
	}
	
	public function get_login()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`foodsaver_id`,
			`ip`,
			`agent`,
			`time`
					
			FROM 		`'.PREFIX.'login`');
		
		return $out;
	}
					
	public function getBasics_login()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'login`');
	}
					
	public function getOne_login($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`foodsaver_id`,
			`ip`,
			`agent`,
			`time`
					
			FROM 		`'.PREFIX.'login`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_mail_error()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`send_mail_id`,
			`foodsaver_id`
					
			FROM 		`'.PREFIX.'mail_error`');
		
		return $out;
	}
					
	public function getBasics_mail_error()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'mail_error`');
	}
					
	public function getOne_mail_error($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`send_mail_id`,
			`foodsaver_id`
					
			FROM 		`'.PREFIX.'mail_error`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_message()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`sender_id`,
			`recip_id`,
			`unread`,
			`name`,
			`msg`,
			`time`,
			`attach`
					
			FROM 		`'.PREFIX.'message`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_message()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'message`
			ORDER BY `name`');
	}
					
	public function getOne_message($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`sender_id`,
			`recip_id`,
			`unread`,
			`name`,
			`msg`,
			`time`,
			`attach`
					
			FROM 		`'.PREFIX.'message`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_message_tpl()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`language_id`,
			`name`,
			`subject`,
			`body`
					
			FROM 		`'.PREFIX.'message_tpl`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_message_tpl()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'message_tpl`
			ORDER BY `name`');
	}
					
	public function getOne_message_tpl($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`language_id`,
			`name`,
			`subject`,
			`body`
					
			FROM 		`'.PREFIX.'message_tpl`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_pass_gen()
	{
		$out = $this->q('
			SELECT 	 	
			`foodsaver_id`,
			`date`
					
			FROM 		`'.PREFIX.'pass_gen`');
		
		return $out;
	}
					
	public function getBasics_pass_gen()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'pass_gen`');
	}
					
	public function getOne_pass_gen($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`foodsaver_id`,
			`date`
					
			FROM 		`'.PREFIX.'pass_gen`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_pass_request()
	{
		$out = $this->q('
			SELECT 	 	
			`foodsaver_id`,
			`name`,
			`time`
					
			FROM 		`'.PREFIX.'pass_request`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_pass_request()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'pass_request`
			ORDER BY `name`');
	}
					
	public function getOne_pass_request($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`foodsaver_id`,
			`name`,
			`time`
					
			FROM 		`'.PREFIX.'pass_request`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_plz()
	{
		$out = $this->q('
			SELECT 	 	
			`plz`,
			`stadt_id`,
			`stadt_kennzeichen_id`,
			`bundesland_id`,
			`geoRegion_id`,
			`land_id`,
			`lat`,
			`lon`
					
			FROM 		`'.PREFIX.'plz`');
		
		return $out;
	}
					
	public function getBasics_plz()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'plz`');
	}
					
	public function getOne_plz($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`plz`,
			`stadt_id`,
			`stadt_kennzeichen_id`,
			`bundesland_id`,
			`geoRegion_id`,
			`land_id`,
			`lat`,
			`lon`
					
			FROM 		`'.PREFIX.'plz`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_region()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'region`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_region()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'region`
			ORDER BY `name`');
	}
					
	public function getOne_region($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'region`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_send_email()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`foodsaver_id`,
			`mode`,
			`complete`,
			`name`,
			`message`,
			`zeit`,
			`recip`,
			`attach`
					
			FROM 		`'.PREFIX.'send_email`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_send_email()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'send_email`
			ORDER BY `name`');
	}
					
	public function getOne_send_email($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`foodsaver_id`,
			`mode`,
			`complete`,
			`name`,
			`message`,
			`zeit`,
			`recip`,
			`attach`
					
			FROM 		`'.PREFIX.'send_email`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_stadt()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'stadt`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_stadt()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'stadt`
			ORDER BY `name`');
	}
					
	public function getOne_stadt($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'stadt`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_stadt_kennzeichen()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'stadt_kennzeichen`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_stadt_kennzeichen()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'stadt_kennzeichen`
			ORDER BY `name`');
	}
					
	public function getOne_stadt_kennzeichen($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`name`
					
			FROM 		`'.PREFIX.'stadt_kennzeichen`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_stadtteil()
	{
		$out = $this->q('
			SELECT 	 	
			`id`,
			`stadt_id`,
			`name`
					
			FROM 		`'.PREFIX.'stadtteil`
			ORDER BY `name`');
		
		return $out;
	}
					
	public function getBasics_stadtteil()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'stadtteil`
			ORDER BY `name`');
	}
					
	public function getOne_stadtteil($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`id`,
			`stadt_id`,
			`name`
					
			FROM 		`'.PREFIX.'stadtteil`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
	public function get_upgrade_request()
	{
		$out = $this->q('
			SELECT 	 	
			`foodsaver_id`,
			`rolle`,
			`bezirk_id`,
			`time`,
			`data`
					
			FROM 		`'.PREFIX.'upgrade_request`');
		
		return $out;
	}
					
	public function getBasics_upgrade_request()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`
					
			FROM 		`'.PREFIX.'upgrade_request`');
	}
					
	public function getOne_upgrade_request($id)
	{
		$out = $this->qRow('
			SELECT 	 	
			`foodsaver_id`,
			`rolle`,
			`bezirk_id`,
			`time`,
			`data`
					
			FROM 		`'.PREFIX.'upgrade_request`
					
			WHERE 		`id` = ' . $this->intval($id));
		
		
				
		return $out;
	}
	
		
	
	public function add_abholen($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'abholen`
			(
			`foodsaver_id`,
			`betrieb_id`,
			`dow`,
			`time`		
			)
			VALUES
			(
			'.$this->intval($data['foodsaver_id']).',
			'.$this->intval($data['betrieb_id']).',
			'.$this->intval($data['dow']).',
			'.$this->dateval($data['time']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_abholer($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'abholer`
			(
			`foodsaver_id`,
			`betrieb_id`,
			`date`,
			`confirmed`		
			)
			VALUES
			(
			'.$this->intval($data['foodsaver_id']).',
			'.$this->intval($data['betrieb_id']).',
			'.$this->dateval($data['date']).',
			'.$this->intval($data['confirmed']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_abholzeiten($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'abholzeiten`
			(
			`betrieb_id`,
			`dow`,
			`time`,
			`fetcher`		
			)
			VALUES
			(
			'.$this->intval($data['betrieb_id']).',
			'.$this->intval($data['dow']).',
			'.$this->dateval($data['time']).',
			'.$this->intval($data['fetcher']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_activity($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'activity`
			(
			`foodsaver_id`,
			`zeit`		
			)
			VALUES
			(
			'.$this->intval($data['foodsaver_id']).',
			'.$this->dateval($data['zeit']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_autokennzeichen($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'autokennzeichen`
			(
			`land_id`,
			`name`,
			`title`		
			)
			VALUES
			(
			'.$this->intval($data['land_id']).',
			'.$this->strval($data['name']).',
			'.$this->strval($data['title']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_betrieb($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'betrieb`
			(
			`betrieb_status_id`,
			`bezirk_id`,
			`plz`,
			`stadt`,
			`lat`,
			`lon`,
			`kette_id`,
			`betrieb_kategorie_id`,
			`name`,
			`str`,
			`hsnr`,
			`status_date`,
			`status`,
			`ansprechpartner`,
			`telefon`,
			`fax`,
			`email`,
			`begin`,
			`besonderheiten`,
			`ueberzeugungsarbeit`,
			`presse`,
			`sticker`,
			`abholmenge`		
			)
			VALUES
			(
			'.$this->intval($data['betrieb_status_id']).',
			'.$this->intval($data['bezirk_id']).',
			'.$this->strval($data['plz']).',
			'.$this->strval($data['stadt']).',
			'.$this->strval($data['lat']).',
			'.$this->strval($data['lon']).',
			'.$this->intval($data['kette_id']).',
			'.$this->intval($data['betrieb_kategorie_id']).',
			'.$this->strval($data['name']).',
			'.$this->strval($data['str']).',
			'.$this->strval($data['hsnr']).',
			'.$this->dateval($data['status_date']).',
			'.$this->intval($data['status']).',
			'.$this->strval($data['ansprechpartner']).',
			'.$this->strval($data['telefon']).',
			'.$this->strval($data['fax']).',
			'.$this->strval($data['email']).',
			'.$this->dateval($data['begin']).',
			'.$this->strval($data['besonderheiten']).',
			'.$this->intval($data['ueberzeugungsarbeit']).',
			'.$this->intval($data['presse']).',
			'.$this->intval($data['sticker']).',
			'.$this->intval($data['abholmenge']).'
			)');
		
		
			if(isset($data['foodsaver']) && is_array($data['foodsaver']))
			{
				foreach($data['foodsaver'] as $foodsaver_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'abholen`	
						(
							`betrieb_id`,
							`foodsaver_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($foodsaver_id).'			
						)
					');
				}
			}
			if(isset($data['foodsaver']) && is_array($data['foodsaver']))
			{
				foreach($data['foodsaver'] as $foodsaver_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'abholer`	
						(
							`betrieb_id`,
							`foodsaver_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($foodsaver_id).'			
						)
					');
				}
			}
			if(isset($data['dow']) && is_array($data['dow']))
			{
				foreach($data['dow'] as $dow_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'abholzeiten`	
						(
							`betrieb_id`,
							`dow_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($dow_id).'			
						)
					');
				}
			}
			if(isset($data['lebensmittel']) && is_array($data['lebensmittel']))
			{
				foreach($data['lebensmittel'] as $lebensmittel_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'betrieb_has_lebensmittel`	
						(
							`betrieb_id`,
							`lebensmittel_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($lebensmittel_id).'			
						)
					');
				}
			}
			if(isset($data['foodsaver']) && is_array($data['foodsaver']))
			{
				foreach($data['foodsaver'] as $foodsaver_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'betrieb_team`	
						(
							`betrieb_id`,
							`foodsaver_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($foodsaver_id).'			
						)
					');
				}
			}
				
		return $id;
	}
	
	public function add_betrieb_has_lebensmittel($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'betrieb_has_lebensmittel`
			(
			`betrieb_id`,
			`lebensmittel_id`		
			)
			VALUES
			(
			'.$this->intval($data['betrieb_id']).',
			'.$this->intval($data['lebensmittel_id']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_betrieb_kategorie($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'betrieb_kategorie`
			(
			`name`		
			)
			VALUES
			(
			'.$this->strval($data['name']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_betrieb_notiz($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'betrieb_notiz`
			(
			`foodsaver_id`,
			`betrieb_id`,
			`milestone`,
			`text`,
			`zeit`		
			)
			VALUES
			(
			'.$this->intval($data['foodsaver_id']).',
			'.$this->intval($data['betrieb_id']).',
			'.$this->intval($data['milestone']).',
			'.$this->strval($data['text']).',
			'.$this->dateval($data['zeit']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_betrieb_status($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'betrieb_status`
			(
			`name`		
			)
			VALUES
			(
			'.$this->strval($data['name']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_betrieb_team($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'betrieb_team`
			(
			`foodsaver_id`,
			`betrieb_id`,
			`verantwortlich`,
			`active`		
			)
			VALUES
			(
			'.$this->intval($data['foodsaver_id']).',
			'.$this->intval($data['betrieb_id']).',
			'.$this->intval($data['verantwortlich']).',
			'.$this->intval($data['active']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_blog_entry($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'blog_entry`
			(
			`bezirk_id`,
			`foodsaver_id`,
			`active`,
			`name`,
			`teaser`,
			`body`,
			`time`,
			`picture`		
			)
			VALUES
			(
			'.$this->intval($data['bezirk_id']).',
			'.$this->intval($data['foodsaver_id']).',
			'.$this->intval($data['active']).',
			'.$this->strval($data['name']).',
			'.$this->strval($data['teaser']).',
			'.$this->strval($data['body']).',
			'.$this->dateval($data['time']).',
			'.$this->strval($data['picture']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_botschafter($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'botschafter`
			(
			`foodsaver_id`,
			`bezirk_id`		
			)
			VALUES
			(
			'.$this->intval($data['foodsaver_id']).',
			'.$this->intval($data['bezirk_id']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_bundesland($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'bundesland`
			(
			`land_id`,
			`name`		
			)
			VALUES
			(
			'.$this->intval($data['land_id']).',
			'.$this->strval($data['name']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_content($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'content`
			(
			`name`,
			`title`,
			`body`,
			`last_mod`		
			)
			VALUES
			(
			'.$this->strval($data['name']).',
			'.$this->strval($data['title']).',
			'.$this->strval($data['body']).',
			'.$this->dateval($data['last_mod']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_document($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'document`
			(
			`name`,
			`file`,
			`body`,
			`rolle`		
			)
			VALUES
			(
			'.$this->strval($data['name']).',
			'.$this->strval($data['file']).',
			'.$this->strval($data['body']).',
			'.$this->intval($data['rolle']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_email_status($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'email_status`
			(
			`email_id`,
			`foodsaver_id`,
			`status`		
			)
			VALUES
			(
			'.$this->intval($data['email_id']).',
			'.$this->intval($data['foodsaver_id']).',
			'.$this->intval($data['status']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_faq($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'faq`
			(
			`foodsaver_id`,
			`faq_kategorie_id`,
			`name`,
			`answer`		
			)
			VALUES
			(
			'.$this->intval($data['foodsaver_id']).',
			'.$this->intval($data['faq_kategorie_id']).',
			'.$this->strval($data['name']).',
			'.$this->strval($data['answer']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_faq_category($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'faq_category`
			(
			`name`		
			)
			VALUES
			(
			'.$this->strval($data['name']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_foodsaver($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'foodsaver`
			(
			`autokennzeichen_id`,
			`bezirk_id`,
			`new_bezirk`,
			`want_new`,
			`rolle`,
			`plz`,
			`stadt`,
			`bundesland_id`,
			`lat`,
			`lon`,
			`photo`,
			`photo_public`,
			`email`,
			`passwd`,
			`name`,
			`admin`,
			`nachname`,
			`anschrift`,
			`telefon`,
			`handy`,
			`geschlecht`,
			`geb_datum`,
			`fs_id`,
			`anmeldedatum`,
			`orgateam`,
			`active`,
			`data`,
			`about_me_public`		
			)
			VALUES
			(
			'.$this->intval($data['autokennzeichen_id']).',
			'.$this->intval($data['bezirk_id']).',
			'.$this->strval($data['new_bezirk']).',
			'.$this->intval($data['want_new']).',
			'.$this->intval($data['rolle']).',
			'.$this->strval($data['plz']).',
			'.$this->strval($data['stadt']).',
			'.$this->intval($data['bundesland_id']).',
			'.$this->strval($data['lat']).',
			'.$this->strval($data['lon']).',
			'.$this->strval($data['photo']).',
			'.$this->intval($data['photo_public']).',
			'.$this->strval($data['email']).',
			'.$this->strval($data['passwd']).',
			'.$this->strval($data['name']).',
			'.$this->intval($data['admin']).',
			'.$this->strval($data['nachname']).',
			'.$this->strval($data['anschrift']).',
			'.$this->strval($data['telefon']).',
			'.$this->strval($data['handy']).',
			'.$this->intval($data['geschlecht']).',
			'.$this->dateval($data['geb_datum']).',
			'.$this->strval($data['fs_id']).',
			'.$this->dateval($data['anmeldedatum']).',
			'.$this->intval($data['orgateam']).',
			'.$this->intval($data['active']).',
			'.$this->strval($data['data']).',
			'.$this->strval($data['about_me_public']).'
			)');
		
		
			if(isset($data['betrieb']) && is_array($data['betrieb']))
			{
				foreach($data['betrieb'] as $betrieb_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'abholen`	
						(
							`foodsaver_id`,
							`betrieb_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($betrieb_id).'			
						)
					');
				}
			}
			if(isset($data['betrieb']) && is_array($data['betrieb']))
			{
				foreach($data['betrieb'] as $betrieb_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'abholer`	
						(
							`foodsaver_id`,
							`betrieb_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($betrieb_id).'			
						)
					');
				}
			}
			if(isset($data['betrieb']) && is_array($data['betrieb']))
			{
				foreach($data['betrieb'] as $betrieb_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'betrieb_team`	
						(
							`foodsaver_id`,
							`betrieb_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($betrieb_id).'			
						)
					');
				}
			}
			if(isset($data['bezirk']) && is_array($data['bezirk']))
			{
				foreach($data['bezirk'] as $bezirk_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'botschafter`	
						(
							`foodsaver_id`,
							`bezirk_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($bezirk_id).'			
						)
					');
				}
			}
			if(isset($data['email']) && is_array($data['email']))
			{
				foreach($data['email'] as $email_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'email_status`	
						(
							`foodsaver_id`,
							`email_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($email_id).'			
						)
					');
				}
			}
			if(isset($data['bezirk']) && is_array($data['bezirk']))
			{
				foreach($data['bezirk'] as $bezirk_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'foodsaver_has_bezirk`	
						(
							`foodsaver_id`,
							`bezirk_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($bezirk_id).'			
						)
					');
				}
			}
			if(isset($data['glocke']) && is_array($data['glocke']))
			{
				foreach($data['glocke'] as $glocke_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'glocke_read`	
						(
							`foodsaver_id`,
							`glocke_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($glocke_id).'			
						)
					');
				}
			}
			if(isset($data['date']) && is_array($data['date']))
			{
				foreach($data['date'] as $date_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'pass_gen`	
						(
							`foodsaver_id`,
							`date_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($date_id).'			
						)
					');
				}
			}
			if(isset($data['rolle']) && is_array($data['rolle']))
			{
				foreach($data['rolle'] as $rolle_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'upgrade_request`	
						(
							`foodsaver_id`,
							`rolle_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($rolle_id).'			
						)
					');
				}
			}
				
		return $id;
	}
	
	public function add_foodsaver_has_bezirk($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'foodsaver_has_bezirk`
			(
			`foodsaver_id`,
			`bezirk_id`,
			`active`		
			)
			VALUES
			(
			'.$this->intval($data['foodsaver_id']).',
			'.$this->intval($data['bezirk_id']).',
			'.$this->intval($data['active']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_geoRegion($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'geoRegion`
			(
			`name`		
			)
			VALUES
			(
			'.$this->strval($data['name']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_glocke($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'glocke`
			(
			`name`,
			`msg`,
			`url`,
			`time`		
			)
			VALUES
			(
			'.$this->strval($data['name']).',
			'.$this->strval($data['msg']).',
			'.$this->strval($data['url']).',
			'.$this->dateval($data['time']).'
			)');
		
		
			if(isset($data['foodsaver']) && is_array($data['foodsaver']))
			{
				foreach($data['foodsaver'] as $foodsaver_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'glocke_read`	
						(
							`glocke_id`,
							`foodsaver_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($foodsaver_id).'			
						)
					');
				}
			}
				
		return $id;
	}
	
	public function add_glocke_read($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'glocke_read`
			(
			`glocke_id`,
			`foodsaver_id`,
			`unread`		
			)
			VALUES
			(
			'.$this->intval($data['glocke_id']).',
			'.$this->intval($data['foodsaver_id']).',
			'.$this->intval($data['unread']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_kette($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'kette`
			(
			`name`,
			`logo`		
			)
			VALUES
			(
			'.$this->strval($data['name']).',
			'.$this->strval($data['logo']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_land($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'land`
			(
			`name`		
			)
			VALUES
			(
			'.$this->strval($data['name']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_language($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'language`
			(
			`name`,
			`short`		
			)
			VALUES
			(
			'.$this->strval($data['name']).',
			'.$this->strval($data['short']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_lebensmittel($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'lebensmittel`
			(
			`name`		
			)
			VALUES
			(
			'.$this->strval($data['name']).'
			)');
		
		
			if(isset($data['betrieb']) && is_array($data['betrieb']))
			{
				foreach($data['betrieb'] as $betrieb_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'betrieb_has_lebensmittel`	
						(
							`lebensmittel_id`,
							`betrieb_id`		
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($betrieb_id).'			
						)
					');
				}
			}
				
		return $id;
	}
	
	public function add_login($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'login`
			(
			`foodsaver_id`,
			`ip`,
			`agent`,
			`time`		
			)
			VALUES
			(
			'.$this->intval($data['foodsaver_id']).',
			'.$this->strval($data['ip']).',
			'.$this->strval($data['agent']).',
			'.$this->dateval($data['time']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_mail_error($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'mail_error`
			(
			`send_mail_id`,
			`foodsaver_id`		
			)
			VALUES
			(
			'.$this->intval($data['send_mail_id']).',
			'.$this->intval($data['foodsaver_id']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_message($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'message`
			(
			`sender_id`,
			`recip_id`,
			`unread`,
			`name`,
			`msg`,
			`time`,
			`attach`		
			)
			VALUES
			(
			'.$this->intval($data['sender_id']).',
			'.$this->intval($data['recip_id']).',
			'.$this->intval($data['unread']).',
			'.$this->strval($data['name']).',
			'.$this->strval($data['msg']).',
			'.$this->dateval($data['time']).',
			'.$this->strval($data['attach']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_message_tpl($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'message_tpl`
			(
			`language_id`,
			`name`,
			`subject`,
			`body`		
			)
			VALUES
			(
			'.$this->intval($data['language_id']).',
			'.$this->strval($data['name']).',
			'.$this->strval($data['subject']).',
			'.$this->strval($data['body']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_pass_gen($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'pass_gen`
			(
			`foodsaver_id`,
			`date`		
			)
			VALUES
			(
			'.$this->intval($data['foodsaver_id']).',
			'.$this->dateval($data['date']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_pass_request($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'pass_request`
			(
			`foodsaver_id`,
			`name`,
			`time`		
			)
			VALUES
			(
			'.$this->intval($data['foodsaver_id']).',
			'.$this->strval($data['name']).',
			'.$this->dateval($data['time']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_plz($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'plz`
			(
			`plz`,
			`stadt_id`,
			`stadt_kennzeichen_id`,
			`bundesland_id`,
			`geoRegion_id`,
			`land_id`,
			`lat`,
			`lon`		
			)
			VALUES
			(
			'.$this->strval($data['plz']).',
			'.$this->intval($data['stadt_id']).',
			'.$this->intval($data['stadt_kennzeichen_id']).',
			'.$this->intval($data['bundesland_id']).',
			'.$this->intval($data['geoRegion_id']).',
			'.$this->intval($data['land_id']).',
			'.$this->strval($data['lat']).',
			'.$this->strval($data['lon']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_region($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'region`
			(
			`name`		
			)
			VALUES
			(
			'.$this->strval($data['name']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_send_email($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'send_email`
			(
			`foodsaver_id`,
			`mode`,
			`complete`,
			`name`,
			`message`,
			`zeit`,
			`recip`,
			`attach`		
			)
			VALUES
			(
			'.$this->intval($data['foodsaver_id']).',
			'.$this->intval($data['mode']).',
			'.$this->intval($data['complete']).',
			'.$this->strval($data['name']).',
			'.$this->strval($data['message']).',
			'.$this->dateval($data['zeit']).',
			'.$this->strval($data['recip']).',
			'.$this->strval($data['attach']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_stadt($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'stadt`
			(
			`name`		
			)
			VALUES
			(
			'.$this->strval($data['name']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_stadt_kennzeichen($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'stadt_kennzeichen`
			(
			`name`		
			)
			VALUES
			(
			'.$this->strval($data['name']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_stadtteil($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'stadtteil`
			(
			`stadt_id`,
			`name`		
			)
			VALUES
			(
			'.$this->intval($data['stadt_id']).',
			'.$this->strval($data['name']).'
			)');
		
		
				
		return $id;
	}
	
	public function add_upgrade_request($data)
	{
		$id = $this->insert('
			INSERT INTO 	`'.PREFIX.'upgrade_request`
			(
			`foodsaver_id`,
			`rolle`,
			`bezirk_id`,
			`time`,
			`data`		
			)
			VALUES
			(
			'.$this->intval($data['foodsaver_id']).',
			'.$this->intval($data['rolle']).',
			'.$this->intval($data['bezirk_id']).',
			'.$this->dateval($data['time']).',
			'.$this->strval($data['data']).'
			)');
		
		
				
		return $id;
	}
	
			
	
	public function update_abholen($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'abholen`
				
		SET 	`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`betrieb_id` =  '.$this->intval($data['betrieb_id']).',
				`dow` =  '.$this->intval($data['dow']).',
				`time` =  '.$this->dateval($data['time']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_abholer($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'abholer`
				
		SET 	`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`betrieb_id` =  '.$this->intval($data['betrieb_id']).',
				`date` =  '.$this->dateval($data['date']).',
				`confirmed` =  '.$this->intval($data['confirmed']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_abholzeiten($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'abholzeiten`
				
		SET 	`betrieb_id` =  '.$this->intval($data['betrieb_id']).',
				`dow` =  '.$this->intval($data['dow']).',
				`time` =  '.$this->dateval($data['time']).',
				`fetcher` =  '.$this->intval($data['fetcher']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_activity($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'activity`
				
		SET 	`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`zeit` =  '.$this->dateval($data['zeit']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_autokennzeichen($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'autokennzeichen`
				
		SET 	`land_id` =  '.$this->intval($data['land_id']).',
				`name` =  '.$this->strval($data['name']).',
				`title` =  '.$this->strval($data['title']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_betrieb($id,$data)
	{
		
			if(isset($data['foodsaver']) && is_array($data['foodsaver']))
			{
				
				$this->del('
					DELETE FROM 	`fs_abholen`
					WHERE 			`betrieb_id` = '.$this->intval($id).' 
				');
							
				foreach($data['foodsaver'] as $foodsaver_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'abholen`
						(
							`betrieb_id`,
							`foodsaver_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($foodsaver_id).'
						)
					');
				}
			}
			if(isset($data['foodsaver']) && is_array($data['foodsaver']))
			{
				
				$this->del('
					DELETE FROM 	`fs_abholer`
					WHERE 			`betrieb_id` = '.$this->intval($id).' 
				');
							
				foreach($data['foodsaver'] as $foodsaver_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'abholer`
						(
							`betrieb_id`,
							`foodsaver_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($foodsaver_id).'
						)
					');
				}
			}
			if(isset($data['dow']) && is_array($data['dow']))
			{
				
				$this->del('
					DELETE FROM 	`fs_abholzeiten`
					WHERE 			`betrieb_id` = '.$this->intval($id).' 
				');
							
				foreach($data['dow'] as $dow_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'abholzeiten`
						(
							`betrieb_id`,
							`dow_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($dow_id).'
						)
					');
				}
			}
			if(isset($data['lebensmittel']) && is_array($data['lebensmittel']))
			{
				
				$this->del('
					DELETE FROM 	`fs_betrieb_has_lebensmittel`
					WHERE 			`betrieb_id` = '.$this->intval($id).' 
				');
							
				foreach($data['lebensmittel'] as $lebensmittel_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'betrieb_has_lebensmittel`
						(
							`betrieb_id`,
							`lebensmittel_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($lebensmittel_id).'
						)
					');
				}
			}
			if(isset($data['foodsaver']) && is_array($data['foodsaver']))
			{
				
				$this->del('
					DELETE FROM 	`fs_betrieb_team`
					WHERE 			`betrieb_id` = '.$this->intval($id).' 
				');
							
				foreach($data['foodsaver'] as $foodsaver_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'betrieb_team`
						(
							`betrieb_id`,
							`foodsaver_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($foodsaver_id).'
						)
					');
				}
			}
		
		return $this->update('
		UPDATE 	`'.PREFIX.'betrieb`
				
		SET 	`betrieb_status_id` =  '.$this->intval($data['betrieb_status_id']).',
				`bezirk_id` =  '.$this->intval($data['bezirk_id']).',
				`plz` =  '.$this->strval($data['plz']).',
				`stadt` =  '.$this->strval($data['stadt']).',
				`lat` =  '.$this->strval($data['lat']).',
				`lon` =  '.$this->strval($data['lon']).',
				`kette_id` =  '.$this->intval($data['kette_id']).',
				`betrieb_kategorie_id` =  '.$this->intval($data['betrieb_kategorie_id']).',
				`name` =  '.$this->strval($data['name']).',
				`str` =  '.$this->strval($data['str']).',
				`hsnr` =  '.$this->strval($data['hsnr']).',
				`status_date` =  '.$this->dateval($data['status_date']).',
				`status` =  '.$this->intval($data['status']).',
				`ansprechpartner` =  '.$this->strval($data['ansprechpartner']).',
				`telefon` =  '.$this->strval($data['telefon']).',
				`fax` =  '.$this->strval($data['fax']).',
				`email` =  '.$this->strval($data['email']).',
				`begin` =  '.$this->dateval($data['begin']).',
				`besonderheiten` =  '.$this->strval($data['besonderheiten']).',
				`ueberzeugungsarbeit` =  '.$this->intval($data['ueberzeugungsarbeit']).',
				`presse` =  '.$this->intval($data['presse']).',
				`sticker` =  '.$this->intval($data['sticker']).',
				`abholmenge` =  '.$this->intval($data['abholmenge']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_betrieb_has_lebensmittel($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'betrieb_has_lebensmittel`
				
		SET 	`betrieb_id` =  '.$this->intval($data['betrieb_id']).',
				`lebensmittel_id` =  '.$this->intval($data['lebensmittel_id']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_betrieb_kategorie($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'betrieb_kategorie`
				
		SET 	`name` =  '.$this->strval($data['name']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_betrieb_notiz($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'betrieb_notiz`
				
		SET 	`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`betrieb_id` =  '.$this->intval($data['betrieb_id']).',
				`milestone` =  '.$this->intval($data['milestone']).',
				`text` =  '.$this->strval($data['text']).',
				`zeit` =  '.$this->dateval($data['zeit']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_betrieb_status($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'betrieb_status`
				
		SET 	`name` =  '.$this->strval($data['name']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_betrieb_team($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'betrieb_team`
				
		SET 	`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`betrieb_id` =  '.$this->intval($data['betrieb_id']).',
				`verantwortlich` =  '.$this->intval($data['verantwortlich']).',
				`active` =  '.$this->intval($data['active']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_bezirk($id,$data)
	{
		
			if(isset($data['foodsaver']) && is_array($data['foodsaver']))
			{
				
				$this->del('
					DELETE FROM 	`fs_botschafter`
					WHERE 			`bezirk_id` = '.$this->intval($id).' 
				');
							
				foreach($data['foodsaver'] as $foodsaver_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'botschafter`
						(
							`bezirk_id`,
							`foodsaver_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($foodsaver_id).'
						)
					');
				}
			}
			if(isset($data['foodsaver']) && is_array($data['foodsaver']))
			{
				
				$this->del('
					DELETE FROM 	`fs_foodsaver_has_bezirk`
					WHERE 			`bezirk_id` = '.$this->intval($id).' 
				');
							
				foreach($data['foodsaver'] as $foodsaver_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'foodsaver_has_bezirk`
						(
							`bezirk_id`,
							`foodsaver_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($foodsaver_id).'
						)
					');
				}
			}
			if(isset($data['foodsaver']) && is_array($data['foodsaver']))
			{
				
				$this->del('
					DELETE FROM 	`fs_upgrade_request`
					WHERE 			`bezirk_id` = '.$this->intval($id).' 
				');
							
				foreach($data['foodsaver'] as $foodsaver_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'upgrade_request`
						(
							`bezirk_id`,
							`foodsaver_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($foodsaver_id).'
						)
					');
				}
			}
		
		return $this->update('
		UPDATE 	`'.PREFIX.'bezirk`
				
		SET 	`parent_id` =  '.$this->intval($data['parent_id']).',
				`has_children` =  '.$this->intval($data['has_children']).',
				`name` =  '.$this->strval($data['name']).',
				`email` =  '.$this->strval($data['email']).',
				`email_pass` =  '.$this->strval($data['email_pass']).',
				`email_name` =  '.$this->strval($data['email_name']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_blog_entry($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'blog_entry`
				
		SET 	`bezirk_id` =  '.$this->intval($data['bezirk_id']).',
				`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`active` =  '.$this->intval($data['active']).',
				`name` =  '.$this->strval($data['name']).',
				`teaser` =  '.$this->strval($data['teaser']).',
				`body` =  '.$this->strval($data['body']).',
				`time` =  '.$this->dateval($data['time']).',
				`picture` =  '.$this->strval($data['picture']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_botschafter($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'botschafter`
				
		SET 	`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`bezirk_id` =  '.$this->intval($data['bezirk_id']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_bundesland($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'bundesland`
				
		SET 	`land_id` =  '.$this->intval($data['land_id']).',
				`name` =  '.$this->strval($data['name']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_content($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'content`
				
		SET 	`name` =  '.$this->strval($data['name']).',
				`title` =  '.$this->strval($data['title']).',
				`body` =  '.$this->strval($data['body']).',
				`last_mod` =  '.$this->dateval($data['last_mod']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_document($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'document`
				
		SET 	`name` =  '.$this->strval($data['name']).',
				`file` =  '.$this->strval($data['file']).',
				`body` =  '.$this->strval($data['body']).',
				`rolle` =  '.$this->intval($data['rolle']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_email_status($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'email_status`
				
		SET 	`email_id` =  '.$this->intval($data['email_id']).',
				`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`status` =  '.$this->intval($data['status']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_faq($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'faq`
				
		SET 	`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`faq_kategorie_id` =  '.$this->intval($data['faq_kategorie_id']).',
				`name` =  '.$this->strval($data['name']).',
				`answer` =  '.$this->strval($data['answer']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_faq_category($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'faq_category`
				
		SET 	`name` =  '.$this->strval($data['name']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_foodsaver($id,$data)
	{
		
			if(isset($data['betrieb']) && is_array($data['betrieb']))
			{
				
				$this->del('
					DELETE FROM 	`fs_abholen`
					WHERE 			`foodsaver_id` = '.$this->intval($id).' 
				');
							
				foreach($data['betrieb'] as $betrieb_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'abholen`
						(
							`foodsaver_id`,
							`betrieb_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($betrieb_id).'
						)
					');
				}
			}
			if(isset($data['betrieb']) && is_array($data['betrieb']))
			{
				
				$this->del('
					DELETE FROM 	`fs_abholer`
					WHERE 			`foodsaver_id` = '.$this->intval($id).' 
				');
							
				foreach($data['betrieb'] as $betrieb_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'abholer`
						(
							`foodsaver_id`,
							`betrieb_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($betrieb_id).'
						)
					');
				}
			}
			if(isset($data['betrieb']) && is_array($data['betrieb']))
			{
				
				$this->del('
					DELETE FROM 	`fs_betrieb_team`
					WHERE 			`foodsaver_id` = '.$this->intval($id).' 
				');
							
				foreach($data['betrieb'] as $betrieb_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'betrieb_team`
						(
							`foodsaver_id`,
							`betrieb_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($betrieb_id).'
						)
					');
				}
			}
			if(isset($data['bezirk']) && is_array($data['bezirk']))
			{
				
				$this->del('
					DELETE FROM 	`fs_botschafter`
					WHERE 			`foodsaver_id` = '.$this->intval($id).' 
				');
							
				foreach($data['bezirk'] as $bezirk_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'botschafter`
						(
							`foodsaver_id`,
							`bezirk_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($bezirk_id).'
						)
					');
				}
			}
			if(isset($data['email']) && is_array($data['email']))
			{
				
				$this->del('
					DELETE FROM 	`fs_email_status`
					WHERE 			`foodsaver_id` = '.$this->intval($id).' 
				');
							
				foreach($data['email'] as $email_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'email_status`
						(
							`foodsaver_id`,
							`email_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($email_id).'
						)
					');
				}
			}
			if(isset($data['bezirk']) && is_array($data['bezirk']))
			{
				
				$this->del('
					DELETE FROM 	`fs_foodsaver_has_bezirk`
					WHERE 			`foodsaver_id` = '.$this->intval($id).' 
				');
							
				foreach($data['bezirk'] as $bezirk_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'foodsaver_has_bezirk`
						(
							`foodsaver_id`,
							`bezirk_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($bezirk_id).'
						)
					');
				}
			}
			if(isset($data['glocke']) && is_array($data['glocke']))
			{
				
				$this->del('
					DELETE FROM 	`fs_glocke_read`
					WHERE 			`foodsaver_id` = '.$this->intval($id).' 
				');
							
				foreach($data['glocke'] as $glocke_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'glocke_read`
						(
							`foodsaver_id`,
							`glocke_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($glocke_id).'
						)
					');
				}
			}
			if(isset($data['date']) && is_array($data['date']))
			{
				
				$this->del('
					DELETE FROM 	`fs_pass_gen`
					WHERE 			`foodsaver_id` = '.$this->intval($id).' 
				');
							
				foreach($data['date'] as $date_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'pass_gen`
						(
							`foodsaver_id`,
							`date_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($date_id).'
						)
					');
				}
			}
			if(isset($data['rolle']) && is_array($data['rolle']))
			{
				
				$this->del('
					DELETE FROM 	`fs_upgrade_request`
					WHERE 			`foodsaver_id` = '.$this->intval($id).' 
				');
							
				foreach($data['rolle'] as $rolle_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'upgrade_request`
						(
							`foodsaver_id`,
							`rolle_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($rolle_id).'
						)
					');
				}
			}
		
		return $this->update('
		UPDATE 	`'.PREFIX.'foodsaver`
				
		SET 	`autokennzeichen_id` =  '.$this->intval($data['autokennzeichen_id']).',
				`bezirk_id` =  '.$this->intval($data['bezirk_id']).',
				`new_bezirk` =  '.$this->strval($data['new_bezirk']).',
				`want_new` =  '.$this->intval($data['want_new']).',
				`rolle` =  '.$this->intval($data['rolle']).',
				`plz` =  '.$this->strval($data['plz']).',
				`stadt` =  '.$this->strval($data['stadt']).',
				`bundesland_id` =  '.$this->intval($data['bundesland_id']).',
				`lat` =  '.$this->strval($data['lat']).',
				`lon` =  '.$this->strval($data['lon']).',
				`photo` =  '.$this->strval($data['photo']).',
				`photo_public` =  '.$this->intval($data['photo_public']).',
				`email` =  '.$this->strval($data['email']).',
				`passwd` =  '.$this->strval($data['passwd']).',
				`name` =  '.$this->strval($data['name']).',
				`admin` =  '.$this->intval($data['admin']).',
				`nachname` =  '.$this->strval($data['nachname']).',
				`anschrift` =  '.$this->strval($data['anschrift']).',
				`telefon` =  '.$this->strval($data['telefon']).',
				`handy` =  '.$this->strval($data['handy']).',
				`geschlecht` =  '.$this->intval($data['geschlecht']).',
				`geb_datum` =  '.$this->dateval($data['geb_datum']).',
				`fs_id` =  '.$this->strval($data['fs_id']).',
				`anmeldedatum` =  '.$this->dateval($data['anmeldedatum']).',
				`orgateam` =  '.$this->intval($data['orgateam']).',
				`active` =  '.$this->intval($data['active']).',
				`data` =  '.$this->strval($data['data']).',
				`about_me_public` =  '.$this->strval($data['about_me_public']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_foodsaver_has_bezirk($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'foodsaver_has_bezirk`
				
		SET 	`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`bezirk_id` =  '.$this->intval($data['bezirk_id']).',
				`active` =  '.$this->intval($data['active']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_geoRegion($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'geoRegion`
				
		SET 	`name` =  '.$this->strval($data['name']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_glocke($id,$data)
	{
		
			if(isset($data['foodsaver']) && is_array($data['foodsaver']))
			{
				
				$this->del('
					DELETE FROM 	`fs_glocke_read`
					WHERE 			`glocke_id` = '.$this->intval($id).' 
				');
							
				foreach($data['foodsaver'] as $foodsaver_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'glocke_read`
						(
							`glocke_id`,
							`foodsaver_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($foodsaver_id).'
						)
					');
				}
			}
		
		return $this->update('
		UPDATE 	`'.PREFIX.'glocke`
				
		SET 	`name` =  '.$this->strval($data['name']).',
				`msg` =  '.$this->strval($data['msg']).',
				`url` =  '.$this->strval($data['url']).',
				`time` =  '.$this->dateval($data['time']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_glocke_read($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'glocke_read`
				
		SET 	`glocke_id` =  '.$this->intval($data['glocke_id']).',
				`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`unread` =  '.$this->intval($data['unread']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_kette($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'kette`
				
		SET 	`name` =  '.$this->strval($data['name']).',
				`logo` =  '.$this->strval($data['logo']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_land($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'land`
				
		SET 	`name` =  '.$this->strval($data['name']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_language($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'language`
				
		SET 	`name` =  '.$this->strval($data['name']).',
				`short` =  '.$this->strval($data['short']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_lebensmittel($id,$data)
	{
		
			if(isset($data['betrieb']) && is_array($data['betrieb']))
			{
				
				$this->del('
					DELETE FROM 	`fs_betrieb_has_lebensmittel`
					WHERE 			`lebensmittel_id` = '.$this->intval($id).' 
				');
							
				foreach($data['betrieb'] as $betrieb_id)
				{
					$this->insert('
						INSERT INTO `'.PREFIX.'betrieb_has_lebensmittel`
						(
							`lebensmittel_id`,
							`betrieb_id`
						)
						VALUES
						(
							'.$this->intval($id).',
							'.$this->intval($betrieb_id).'
						)
					');
				}
			}
		
		return $this->update('
		UPDATE 	`'.PREFIX.'lebensmittel`
				
		SET 	`name` =  '.$this->strval($data['name']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_login($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'login`
				
		SET 	`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`ip` =  '.$this->strval($data['ip']).',
				`agent` =  '.$this->strval($data['agent']).',
				`time` =  '.$this->dateval($data['time']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_mail_error($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'mail_error`
				
		SET 	`send_mail_id` =  '.$this->intval($data['send_mail_id']).',
				`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_message($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'message`
				
		SET 	`sender_id` =  '.$this->intval($data['sender_id']).',
				`recip_id` =  '.$this->intval($data['recip_id']).',
				`unread` =  '.$this->intval($data['unread']).',
				`name` =  '.$this->strval($data['name']).',
				`msg` =  '.$this->strval($data['msg']).',
				`time` =  '.$this->dateval($data['time']).',
				`attach` =  '.$this->strval($data['attach']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_message_tpl($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'message_tpl`
				
		SET 	`language_id` =  '.$this->intval($data['language_id']).',
				`name` =  '.$this->strval($data['name']).',
				`subject` =  '.$this->strval($data['subject']).',
				`body` =  '.$this->strval($data['body']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_pass_gen($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'pass_gen`
				
		SET 	`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`date` =  '.$this->dateval($data['date']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_pass_request($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'pass_request`
				
		SET 	`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`name` =  '.$this->strval($data['name']).',
				`time` =  '.$this->dateval($data['time']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_plz($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'plz`
				
		SET 	`plz` =  '.$this->strval($data['plz']).',
				`stadt_id` =  '.$this->intval($data['stadt_id']).',
				`stadt_kennzeichen_id` =  '.$this->intval($data['stadt_kennzeichen_id']).',
				`bundesland_id` =  '.$this->intval($data['bundesland_id']).',
				`geoRegion_id` =  '.$this->intval($data['geoRegion_id']).',
				`land_id` =  '.$this->intval($data['land_id']).',
				`lat` =  '.$this->strval($data['lat']).',
				`lon` =  '.$this->strval($data['lon']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_region($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'region`
				
		SET 	`name` =  '.$this->strval($data['name']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_send_email($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'send_email`
				
		SET 	`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`mode` =  '.$this->intval($data['mode']).',
				`complete` =  '.$this->intval($data['complete']).',
				`name` =  '.$this->strval($data['name']).',
				`message` =  '.$this->strval($data['message']).',
				`zeit` =  '.$this->dateval($data['zeit']).',
				`recip` =  '.$this->strval($data['recip']).',
				`attach` =  '.$this->strval($data['attach']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_stadt($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'stadt`
				
		SET 	`name` =  '.$this->strval($data['name']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_stadt_kennzeichen($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'stadt_kennzeichen`
				
		SET 	`name` =  '.$this->strval($data['name']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_stadtteil($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'stadtteil`
				
		SET 	`stadt_id` =  '.$this->intval($data['stadt_id']).',
				`name` =  '.$this->strval($data['name']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
	public function update_upgrade_request($id,$data)
	{
		
		
		return $this->update('
		UPDATE 	`'.PREFIX.'upgrade_request`
				
		SET 	`foodsaver_id` =  '.$this->intval($data['foodsaver_id']).',
				`rolle` =  '.$this->intval($data['rolle']).',
				`bezirk_id` =  '.$this->intval($data['bezirk_id']).',
				`time` =  '.$this->dateval($data['time']).',
				`data` =  '.$this->strval($data['data']).'
				
		WHERE 	`id` = '.$this->intval($id));
	} 
	
			
	
	public function del_abholen($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'abholen`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_abholer($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'abholer`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_abholzeiten($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'abholzeiten`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_activity($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'activity`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_autokennzeichen($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'autokennzeichen`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_betrieb($id)
	{
		
			$this->del('
				DELETE FROM 	`fs_abholen`
				WHERE 			`betrieb_id` = '.$this->intval($id).' 
			');
			$this->del('
				DELETE FROM 	`fs_abholer`
				WHERE 			`betrieb_id` = '.$this->intval($id).' 
			');
			$this->del('
				DELETE FROM 	`fs_abholzeiten`
				WHERE 			`betrieb_id` = '.$this->intval($id).' 
			');
			$this->del('
				DELETE FROM 	`fs_betrieb_has_lebensmittel`
				WHERE 			`betrieb_id` = '.$this->intval($id).' 
			');
			$this->del('
				DELETE FROM 	`fs_betrieb_team`
				WHERE 			`betrieb_id` = '.$this->intval($id).' 
			');
		return $this->del('
			DELETE FROM 	`'.PREFIX.'betrieb`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_betrieb_has_lebensmittel($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'betrieb_has_lebensmittel`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_betrieb_kategorie($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'betrieb_kategorie`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_betrieb_notiz($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'betrieb_notiz`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_betrieb_status($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'betrieb_status`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_betrieb_team($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'betrieb_team`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_bezirk($id)
	{
		
			$this->del('
				DELETE FROM 	`fs_botschafter`
				WHERE 			`bezirk_id` = '.$this->intval($id).' 
			');
			$this->del('
				DELETE FROM 	`fs_foodsaver_has_bezirk`
				WHERE 			`bezirk_id` = '.$this->intval($id).' 
			');
			$this->del('
				DELETE FROM 	`fs_upgrade_request`
				WHERE 			`bezirk_id` = '.$this->intval($id).' 
			');
		return $this->del('
			DELETE FROM 	`'.PREFIX.'bezirk`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_botschafter($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'botschafter`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_bundesland($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'bundesland`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_content($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'content`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_document($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'document`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_email_status($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'email_status`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_faq($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'faq`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_faq_category($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'faq_category`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_foodsaver($id)
	{
		
			$this->del('
				DELETE FROM 	`fs_abholen`
				WHERE 			`foodsaver_id` = '.$this->intval($id).' 
			');
			$this->del('
				DELETE FROM 	`fs_abholer`
				WHERE 			`foodsaver_id` = '.$this->intval($id).' 
			');
			$this->del('
				DELETE FROM 	`fs_betrieb_team`
				WHERE 			`foodsaver_id` = '.$this->intval($id).' 
			');
			$this->del('
				DELETE FROM 	`fs_botschafter`
				WHERE 			`foodsaver_id` = '.$this->intval($id).' 
			');
			$this->del('
				DELETE FROM 	`fs_email_status`
				WHERE 			`foodsaver_id` = '.$this->intval($id).' 
			');
			$this->del('
				DELETE FROM 	`fs_foodsaver_has_bezirk`
				WHERE 			`foodsaver_id` = '.$this->intval($id).' 
			');
			$this->del('
				DELETE FROM 	`fs_glocke_read`
				WHERE 			`foodsaver_id` = '.$this->intval($id).' 
			');
			$this->del('
				DELETE FROM 	`fs_pass_gen`
				WHERE 			`foodsaver_id` = '.$this->intval($id).' 
			');
			$this->del('
				DELETE FROM 	`fs_upgrade_request`
				WHERE 			`foodsaver_id` = '.$this->intval($id).' 
			');
		return $this->del('
			DELETE FROM 	`'.PREFIX.'foodsaver`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_foodsaver_has_bezirk($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'foodsaver_has_bezirk`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_geoRegion($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'geoRegion`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_glocke($id)
	{
		
			$this->del('
				DELETE FROM 	`fs_glocke_read`
				WHERE 			`glocke_id` = '.$this->intval($id).' 
			');
		return $this->del('
			DELETE FROM 	`'.PREFIX.'glocke`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_glocke_read($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'glocke_read`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_kette($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'kette`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_land($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'land`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_language($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'language`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_lebensmittel($id)
	{
		
			$this->del('
				DELETE FROM 	`fs_betrieb_has_lebensmittel`
				WHERE 			`lebensmittel_id` = '.$this->intval($id).' 
			');
		return $this->del('
			DELETE FROM 	`'.PREFIX.'lebensmittel`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_login($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'login`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_mail_error($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'mail_error`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_message($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'message`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_message_tpl($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'message_tpl`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_pass_gen($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'pass_gen`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_pass_request($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'pass_request`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_plz($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'plz`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_region($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'region`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_send_email($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'send_email`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_stadt($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'stadt`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_stadt_kennzeichen($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'stadt_kennzeichen`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_stadtteil($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'stadtteil`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
	public function del_upgrade_request($id)
	{
		
		return $this->del('
			DELETE FROM 	`'.PREFIX.'upgrade_request`
			WHERE 			`id` = '.$this->intval($id).'
			');		
	}
	
			
	
	public function getId_abholen($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'abholen` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_abholen($name)
	{
		if(isset($this->cache['fs_abholen'][$name]))
		{
			return $this->cache['fs_abholen'][$name];	
		}
		if($id = $this->getId_abholen($name))
		{
			$this->cache['fs_abholen'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'abholen`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_abholen'][$name] = $id;
			return $this->cache['fs_abholen'][$name];
		}
	}
	public function getId_abholer($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'abholer` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_abholer($name)
	{
		if(isset($this->cache['fs_abholer'][$name]))
		{
			return $this->cache['fs_abholer'][$name];	
		}
		if($id = $this->getId_abholer($name))
		{
			$this->cache['fs_abholer'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'abholer`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_abholer'][$name] = $id;
			return $this->cache['fs_abholer'][$name];
		}
	}
	public function getId_abholzeiten($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'abholzeiten` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_abholzeiten($name)
	{
		if(isset($this->cache['fs_abholzeiten'][$name]))
		{
			return $this->cache['fs_abholzeiten'][$name];	
		}
		if($id = $this->getId_abholzeiten($name))
		{
			$this->cache['fs_abholzeiten'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'abholzeiten`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_abholzeiten'][$name] = $id;
			return $this->cache['fs_abholzeiten'][$name];
		}
	}
	public function getId_activity($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'activity` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_activity($name)
	{
		if(isset($this->cache['fs_activity'][$name]))
		{
			return $this->cache['fs_activity'][$name];	
		}
		if($id = $this->getId_activity($name))
		{
			$this->cache['fs_activity'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'activity`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_activity'][$name] = $id;
			return $this->cache['fs_activity'][$name];
		}
	}
	public function getId_autokennzeichen($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'autokennzeichen` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_autokennzeichen($name)
	{
		if(isset($this->cache['fs_autokennzeichen'][$name]))
		{
			return $this->cache['fs_autokennzeichen'][$name];	
		}
		if($id = $this->getId_autokennzeichen($name))
		{
			$this->cache['fs_autokennzeichen'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'autokennzeichen`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_autokennzeichen'][$name] = $id;
			return $this->cache['fs_autokennzeichen'][$name];
		}
	}
	public function getId_betrieb($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'betrieb` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_betrieb($name)
	{
		if(isset($this->cache['fs_betrieb'][$name]))
		{
			return $this->cache['fs_betrieb'][$name];	
		}
		if($id = $this->getId_betrieb($name))
		{
			$this->cache['fs_betrieb'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'betrieb`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_betrieb'][$name] = $id;
			return $this->cache['fs_betrieb'][$name];
		}
	}
	public function getId_betrieb_has_lebensmittel($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'betrieb_has_lebensmittel` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_betrieb_has_lebensmittel($name)
	{
		if(isset($this->cache['fs_betrieb_has_lebensmittel'][$name]))
		{
			return $this->cache['fs_betrieb_has_lebensmittel'][$name];	
		}
		if($id = $this->getId_betrieb_has_lebensmittel($name))
		{
			$this->cache['fs_betrieb_has_lebensmittel'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'betrieb_has_lebensmittel`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_betrieb_has_lebensmittel'][$name] = $id;
			return $this->cache['fs_betrieb_has_lebensmittel'][$name];
		}
	}
	public function getId_betrieb_kategorie($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'betrieb_kategorie` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_betrieb_kategorie($name)
	{
		if(isset($this->cache['fs_betrieb_kategorie'][$name]))
		{
			return $this->cache['fs_betrieb_kategorie'][$name];	
		}
		if($id = $this->getId_betrieb_kategorie($name))
		{
			$this->cache['fs_betrieb_kategorie'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'betrieb_kategorie`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_betrieb_kategorie'][$name] = $id;
			return $this->cache['fs_betrieb_kategorie'][$name];
		}
	}
	public function getId_betrieb_notiz($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'betrieb_notiz` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_betrieb_notiz($name)
	{
		if(isset($this->cache['fs_betrieb_notiz'][$name]))
		{
			return $this->cache['fs_betrieb_notiz'][$name];	
		}
		if($id = $this->getId_betrieb_notiz($name))
		{
			$this->cache['fs_betrieb_notiz'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'betrieb_notiz`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_betrieb_notiz'][$name] = $id;
			return $this->cache['fs_betrieb_notiz'][$name];
		}
	}
	public function getId_betrieb_status($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'betrieb_status` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_betrieb_status($name)
	{
		if(isset($this->cache['fs_betrieb_status'][$name]))
		{
			return $this->cache['fs_betrieb_status'][$name];	
		}
		if($id = $this->getId_betrieb_status($name))
		{
			$this->cache['fs_betrieb_status'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'betrieb_status`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_betrieb_status'][$name] = $id;
			return $this->cache['fs_betrieb_status'][$name];
		}
	}
	public function getId_betrieb_team($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'betrieb_team` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_betrieb_team($name)
	{
		if(isset($this->cache['fs_betrieb_team'][$name]))
		{
			return $this->cache['fs_betrieb_team'][$name];	
		}
		if($id = $this->getId_betrieb_team($name))
		{
			$this->cache['fs_betrieb_team'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'betrieb_team`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_betrieb_team'][$name] = $id;
			return $this->cache['fs_betrieb_team'][$name];
		}
	}
	public function getId_bezirk($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'bezirk` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_bezirk($name)
	{
		if(isset($this->cache['fs_bezirk'][$name]))
		{
			return $this->cache['fs_bezirk'][$name];	
		}
		if($id = $this->getId_bezirk($name))
		{
			$this->cache['fs_bezirk'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'bezirk`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_bezirk'][$name] = $id;
			return $this->cache['fs_bezirk'][$name];
		}
	}
	public function getId_blog_entry($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'blog_entry` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_blog_entry($name)
	{
		if(isset($this->cache['fs_blog_entry'][$name]))
		{
			return $this->cache['fs_blog_entry'][$name];	
		}
		if($id = $this->getId_blog_entry($name))
		{
			$this->cache['fs_blog_entry'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'blog_entry`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_blog_entry'][$name] = $id;
			return $this->cache['fs_blog_entry'][$name];
		}
	}
	public function getId_botschafter($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'botschafter` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_botschafter($name)
	{
		if(isset($this->cache['fs_botschafter'][$name]))
		{
			return $this->cache['fs_botschafter'][$name];	
		}
		if($id = $this->getId_botschafter($name))
		{
			$this->cache['fs_botschafter'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'botschafter`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_botschafter'][$name] = $id;
			return $this->cache['fs_botschafter'][$name];
		}
	}
	public function getId_bundesland($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'bundesland` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_bundesland($name)
	{
		if(isset($this->cache['fs_bundesland'][$name]))
		{
			return $this->cache['fs_bundesland'][$name];	
		}
		if($id = $this->getId_bundesland($name))
		{
			$this->cache['fs_bundesland'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'bundesland`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_bundesland'][$name] = $id;
			return $this->cache['fs_bundesland'][$name];
		}
	}
	public function getId_content($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'content` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_content($name)
	{
		if(isset($this->cache['fs_content'][$name]))
		{
			return $this->cache['fs_content'][$name];	
		}
		if($id = $this->getId_content($name))
		{
			$this->cache['fs_content'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'content`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_content'][$name] = $id;
			return $this->cache['fs_content'][$name];
		}
	}
	public function getId_document($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'document` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_document($name)
	{
		if(isset($this->cache['fs_document'][$name]))
		{
			return $this->cache['fs_document'][$name];	
		}
		if($id = $this->getId_document($name))
		{
			$this->cache['fs_document'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'document`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_document'][$name] = $id;
			return $this->cache['fs_document'][$name];
		}
	}
	public function getId_email_status($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'email_status` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_email_status($name)
	{
		if(isset($this->cache['fs_email_status'][$name]))
		{
			return $this->cache['fs_email_status'][$name];	
		}
		if($id = $this->getId_email_status($name))
		{
			$this->cache['fs_email_status'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'email_status`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_email_status'][$name] = $id;
			return $this->cache['fs_email_status'][$name];
		}
	}
	public function getId_faq($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'faq` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_faq($name)
	{
		if(isset($this->cache['fs_faq'][$name]))
		{
			return $this->cache['fs_faq'][$name];	
		}
		if($id = $this->getId_faq($name))
		{
			$this->cache['fs_faq'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'faq`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_faq'][$name] = $id;
			return $this->cache['fs_faq'][$name];
		}
	}
	public function getId_faq_category($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'faq_category` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_faq_category($name)
	{
		if(isset($this->cache['fs_faq_category'][$name]))
		{
			return $this->cache['fs_faq_category'][$name];	
		}
		if($id = $this->getId_faq_category($name))
		{
			$this->cache['fs_faq_category'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'faq_category`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_faq_category'][$name] = $id;
			return $this->cache['fs_faq_category'][$name];
		}
	}
	public function getId_foodsaver($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'foodsaver` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_foodsaver($name)
	{
		if(isset($this->cache['fs_foodsaver'][$name]))
		{
			return $this->cache['fs_foodsaver'][$name];	
		}
		if($id = $this->getId_foodsaver($name))
		{
			$this->cache['fs_foodsaver'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'foodsaver`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_foodsaver'][$name] = $id;
			return $this->cache['fs_foodsaver'][$name];
		}
	}
	public function getId_foodsaver_has_bezirk($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'foodsaver_has_bezirk` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_foodsaver_has_bezirk($name)
	{
		if(isset($this->cache['fs_foodsaver_has_bezirk'][$name]))
		{
			return $this->cache['fs_foodsaver_has_bezirk'][$name];	
		}
		if($id = $this->getId_foodsaver_has_bezirk($name))
		{
			$this->cache['fs_foodsaver_has_bezirk'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'foodsaver_has_bezirk`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_foodsaver_has_bezirk'][$name] = $id;
			return $this->cache['fs_foodsaver_has_bezirk'][$name];
		}
	}
	public function getId_geoRegion($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'geoRegion` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_geoRegion($name)
	{
		if(isset($this->cache['fs_geoRegion'][$name]))
		{
			return $this->cache['fs_geoRegion'][$name];	
		}
		if($id = $this->getId_geoRegion($name))
		{
			$this->cache['fs_geoRegion'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'geoRegion`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_geoRegion'][$name] = $id;
			return $this->cache['fs_geoRegion'][$name];
		}
	}
	public function getId_glocke($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'glocke` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_glocke($name)
	{
		if(isset($this->cache['fs_glocke'][$name]))
		{
			return $this->cache['fs_glocke'][$name];	
		}
		if($id = $this->getId_glocke($name))
		{
			$this->cache['fs_glocke'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'glocke`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_glocke'][$name] = $id;
			return $this->cache['fs_glocke'][$name];
		}
	}
	public function getId_glocke_read($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'glocke_read` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_glocke_read($name)
	{
		if(isset($this->cache['fs_glocke_read'][$name]))
		{
			return $this->cache['fs_glocke_read'][$name];	
		}
		if($id = $this->getId_glocke_read($name))
		{
			$this->cache['fs_glocke_read'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'glocke_read`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_glocke_read'][$name] = $id;
			return $this->cache['fs_glocke_read'][$name];
		}
	}
	public function getId_kette($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'kette` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_kette($name)
	{
		if(isset($this->cache['fs_kette'][$name]))
		{
			return $this->cache['fs_kette'][$name];	
		}
		if($id = $this->getId_kette($name))
		{
			$this->cache['fs_kette'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'kette`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_kette'][$name] = $id;
			return $this->cache['fs_kette'][$name];
		}
	}
	public function getId_land($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'land` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_land($name)
	{
		if(isset($this->cache['fs_land'][$name]))
		{
			return $this->cache['fs_land'][$name];	
		}
		if($id = $this->getId_land($name))
		{
			$this->cache['fs_land'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'land`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_land'][$name] = $id;
			return $this->cache['fs_land'][$name];
		}
	}
	public function getId_language($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'language` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_language($name)
	{
		if(isset($this->cache['fs_language'][$name]))
		{
			return $this->cache['fs_language'][$name];	
		}
		if($id = $this->getId_language($name))
		{
			$this->cache['fs_language'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'language`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_language'][$name] = $id;
			return $this->cache['fs_language'][$name];
		}
	}
	public function getId_lebensmittel($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'lebensmittel` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_lebensmittel($name)
	{
		if(isset($this->cache['fs_lebensmittel'][$name]))
		{
			return $this->cache['fs_lebensmittel'][$name];	
		}
		if($id = $this->getId_lebensmittel($name))
		{
			$this->cache['fs_lebensmittel'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'lebensmittel`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_lebensmittel'][$name] = $id;
			return $this->cache['fs_lebensmittel'][$name];
		}
	}
	public function getId_login($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'login` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_login($name)
	{
		if(isset($this->cache['fs_login'][$name]))
		{
			return $this->cache['fs_login'][$name];	
		}
		if($id = $this->getId_login($name))
		{
			$this->cache['fs_login'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'login`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_login'][$name] = $id;
			return $this->cache['fs_login'][$name];
		}
	}
	public function getId_mail_error($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'mail_error` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_mail_error($name)
	{
		if(isset($this->cache['fs_mail_error'][$name]))
		{
			return $this->cache['fs_mail_error'][$name];	
		}
		if($id = $this->getId_mail_error($name))
		{
			$this->cache['fs_mail_error'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'mail_error`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_mail_error'][$name] = $id;
			return $this->cache['fs_mail_error'][$name];
		}
	}
	public function getId_message($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'message` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_message($name)
	{
		if(isset($this->cache['fs_message'][$name]))
		{
			return $this->cache['fs_message'][$name];	
		}
		if($id = $this->getId_message($name))
		{
			$this->cache['fs_message'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'message`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_message'][$name] = $id;
			return $this->cache['fs_message'][$name];
		}
	}
	public function getId_message_tpl($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'message_tpl` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_message_tpl($name)
	{
		if(isset($this->cache['fs_message_tpl'][$name]))
		{
			return $this->cache['fs_message_tpl'][$name];	
		}
		if($id = $this->getId_message_tpl($name))
		{
			$this->cache['fs_message_tpl'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'message_tpl`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_message_tpl'][$name] = $id;
			return $this->cache['fs_message_tpl'][$name];
		}
	}
	public function getId_pass_gen($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'pass_gen` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_pass_gen($name)
	{
		if(isset($this->cache['fs_pass_gen'][$name]))
		{
			return $this->cache['fs_pass_gen'][$name];	
		}
		if($id = $this->getId_pass_gen($name))
		{
			$this->cache['fs_pass_gen'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'pass_gen`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_pass_gen'][$name] = $id;
			return $this->cache['fs_pass_gen'][$name];
		}
	}
	public function getId_pass_request($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'pass_request` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_pass_request($name)
	{
		if(isset($this->cache['fs_pass_request'][$name]))
		{
			return $this->cache['fs_pass_request'][$name];	
		}
		if($id = $this->getId_pass_request($name))
		{
			$this->cache['fs_pass_request'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'pass_request`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_pass_request'][$name] = $id;
			return $this->cache['fs_pass_request'][$name];
		}
	}
	public function getId_plz($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'plz` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_plz($name)
	{
		if(isset($this->cache['fs_plz'][$name]))
		{
			return $this->cache['fs_plz'][$name];	
		}
		if($id = $this->getId_plz($name))
		{
			$this->cache['fs_plz'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'plz`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_plz'][$name] = $id;
			return $this->cache['fs_plz'][$name];
		}
	}
	public function getId_region($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'region` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_region($name)
	{
		if(isset($this->cache['fs_region'][$name]))
		{
			return $this->cache['fs_region'][$name];	
		}
		if($id = $this->getId_region($name))
		{
			$this->cache['fs_region'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'region`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_region'][$name] = $id;
			return $this->cache['fs_region'][$name];
		}
	}
	public function getId_send_email($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'send_email` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_send_email($name)
	{
		if(isset($this->cache['fs_send_email'][$name]))
		{
			return $this->cache['fs_send_email'][$name];	
		}
		if($id = $this->getId_send_email($name))
		{
			$this->cache['fs_send_email'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'send_email`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_send_email'][$name] = $id;
			return $this->cache['fs_send_email'][$name];
		}
	}
	public function getId_stadt($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'stadt` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_stadt($name)
	{
		if(isset($this->cache['fs_stadt'][$name]))
		{
			return $this->cache['fs_stadt'][$name];	
		}
		if($id = $this->getId_stadt($name))
		{
			$this->cache['fs_stadt'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'stadt`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_stadt'][$name] = $id;
			return $this->cache['fs_stadt'][$name];
		}
	}
	public function getId_stadt_kennzeichen($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'stadt_kennzeichen` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_stadt_kennzeichen($name)
	{
		if(isset($this->cache['fs_stadt_kennzeichen'][$name]))
		{
			return $this->cache['fs_stadt_kennzeichen'][$name];	
		}
		if($id = $this->getId_stadt_kennzeichen($name))
		{
			$this->cache['fs_stadt_kennzeichen'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'stadt_kennzeichen`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_stadt_kennzeichen'][$name] = $id;
			return $this->cache['fs_stadt_kennzeichen'][$name];
		}
	}
	public function getId_stadtteil($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'stadtteil` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_stadtteil($name)
	{
		if(isset($this->cache['fs_stadtteil'][$name]))
		{
			return $this->cache['fs_stadtteil'][$name];	
		}
		if($id = $this->getId_stadtteil($name))
		{
			$this->cache['fs_stadtteil'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'stadtteil`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_stadtteil'][$name] = $id;
			return $this->cache['fs_stadtteil'][$name];
		}
	}
	public function getId_upgrade_request($name)
	{
		return $this->qOne('SELECT `id` FROM `'.PREFIX.'upgrade_request` WHERE `name` = '.$this->strval($name).' ');
	}
	
	public function addOrGet_upgrade_request($name)
	{
		if(isset($this->cache['fs_upgrade_request'][$name]))
		{
			return $this->cache['fs_upgrade_request'][$name];	
		}
		if($id = $this->getId_upgrade_request($name))
		{
			$this->cache['fs_upgrade_request'][$name] = $id;
			return $id;
		}
		else
		{
			$id = $this->insert('INSERT INTO `'.PREFIX.'upgrade_request`(`name`)VALUES('.$this->strval($name).')');
			$this->cache['fs_upgrade_request'][$name] = $id;
			return $this->cache['fs_upgrade_request'][$name];
		}
	}
}
			?>
