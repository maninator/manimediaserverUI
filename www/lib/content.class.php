<?php
  /**
   * Content Class
   *
   * @package Wojo Framework
   * @author wojoscripts.com
   * @copyright 2016
   * @version $Id: content.class.php, v1.00 2016-04-20 18:20:24 gewa Exp $
   */
  if (!defined("_WOJO"))
      die('Direct access to this location is not allowed.');
	  

  class Content
  {

	  const cTable = "countries";
	  const dcTable = "coupons";
	  const eTable = "email_templates";
	  const cfTable = "custom_fields";
	  const nTable = "news";


      /**
       * Content::__construct()
       * 
       * @return
       */
      public function __construct()
      {

      }

      /**
       * Content::Templates()
       * 
       * @return
       */
      public function Templates()
      {

		  $tpl = App::View(BASEPATH . 'view/');
		  $tpl->dir = "admin/";
		  $tpl->crumbs = ['admin', 'email templates'];
		  $tpl->template = 'admin/templates.tpl.php';
		  $tpl->data = Db::run()->select(self::eTable, null, null, "ORDER BY name DESC")->results(); 
		  $tpl->title = Lang::$word->META_T10; 

      }

      /**
       * Content::TemplateEdit()
       * 
	   * @param mixed $id
       * @return
       */
	  public function TemplateEdit($id)
	  {
		  $tpl = App::View(BASEPATH . 'view/');
		  $tpl->dir = "admin/";
		  $tpl->title = Lang::$word->META_T11;
		  $tpl->crumbs = ['admin', 'templates', 'edit'];
	
		  if (!$row = Db::run()->first(self::eTable, null, array("id =" => $id))) {
			  $tpl->template = 'admin/error.tpl.php';
			  $tpl->error = DEBUG ? "Invalid ID ($id) detected [Content.class.php, ln.:" . __line__ . "]" : Lang::$word->META_ERROR;
		  } else {
			  $tpl->data = $row;
			  $tpl->template = 'admin/templates.tpl.php';
		  }
	  }

      /**
       * Content::processTemplate()
       * 
       * @return
       */
	  public function processTemplate()
	  {
	
		  $rules = array(
			  'name' => array('required|string|min_len,3|max_len,60', Lang::$word->ET_NAME),
			  'subject' => array('required|string|min_len,3|max_len,100', Lang::$word->ET_SUBJECT),
			  'id' => array('required|numeric', "ID"),
			  );
	
		  $filters = array(
			  'body' => 'advanced_tags',
			  'help' => 'string',
			  );

		  $validate = Validator::instance();
		  $safe = $validate->doValidate($_POST, $rules);
		  $safe = $validate->doFilter($_POST, $filters);
		  
		  if (empty(Message::$msgs)) {
			  $data = array(
				  'name' => $safe->name,
				  'subject' => $safe->subject,
				  'help' => $safe->help,
				  'body' => str_replace(SITEURL, "[SITEURL]", $safe->body),
				  );
	
			  Db::run()->update(self::eTable, $data, array("id" => Filter::$id)); 
			  Message::msgReply(Db::run()->affected(), 'success', Message::formatSuccessMessage($data['name'], Lang::$word->ET_UPDATED));
		  } else {
			  Message::msgSingleStatus();
		  }
	  }

      /**
       * Content::Countries()
       * 
       * @return
       */
      public function Countries()
      {

		  $tpl = App::View(BASEPATH . 'view/');
		  $tpl->dir = "admin/";
		  $tpl->template = 'admin/countries.tpl.php';
		  $tpl->data = Db::run()->select(self::cTable, null, null, "ORDER BY sorting DESC")->results(); 
		  $tpl->title = Lang::$word->CNT_TITLE; 

      }

      /**
       * Content::CountryEdit()
       * 
	   * @param mixed $id
       * @return
       */
	  public function CountryEdit($id)
	  {
		  $tpl = App::View(BASEPATH . 'view/');
		  $tpl->dir = "admin/";
		  $tpl->title = Lang::$word->CNT_EDIT;
		  $tpl->crumbs = ['admin', 'countries', 'edit'];
	
		  if (!$row = Db::run()->first(self::cTable, null, array("id =" => $id))) {
			  $tpl->template = 'admin/error.tpl.php';
			  $tpl->error = DEBUG ? "Invalid ID ($id) detected [Content.class.php, ln.:" . __line__ . "]" : Lang::$word->META_ERROR;
		  } else {
			  $tpl->data = $row;
			  $tpl->template = 'admin/countries.tpl.php';
		  }
	  }

      /**
       * Content::processCountry()
       * 
       * @return
       */
	  public function processCountry()
	  {
	
		  $rules = array(
			  'name' => array('required|string|min_len,3|max_len,60', Lang::$word->NAME),
			  'abbr' => array('required|string|min_len,2|max_len,2', Lang::$word->CNT_ABBR),
			  'active' => array('required|numeric', Lang::$word->CNT_ABBR),
			  'home' => array('required|numeric', Lang::$word->CNT_ABBR),
			  'sorting' => array('required|numeric', Lang::$word->CNT_ABBR),
			  'vat' => array('required|numeric|min_numeric,0|max_numeric,50', Lang::$word->TRX_TAX),
			  'id' => array('required|numeric', "ID"),
			  );

		  $validate = Validator::instance();
		  $safe = $validate->doValidate($_POST, $rules);
		  
		  if (empty(Message::$msgs)) {
			  $data = array(
				  'name' => $safe->name,
				  'abbr' => $safe->abbr,
				  'sorting' => $safe->sorting,
				  'home' => $safe->home,
				  'active' => $safe->active,
				  'vat' => $safe->vat,
				  );

			  if ($data['home'] == 1) {
				  Db::run()->pdoQuery("UPDATE `" . self::cTable . "` SET `home`= DEFAULT(home);");
			  }	
			  
			  Db::run()->update(self::cTable, $data, array("id" => Filter::$id)); 
			  Message::msgReply(Db::run()->affected(), 'success', Message::formatSuccessMessage($data['name'], Lang::$word->CNT_UPDATED));
		  } else {
			  Message::msgSingleStatus();
		  }
	  }
	  
      /**
       * Content::getCountryList()
       * 
       * @return
       */
      public function getCountryList()
      {

		  $row = Db::run()->select(self::cTable, null, null, "ORDER BY sorting DESC")->results();

          return ($row) ? $row : 0; 

      }

      /**
       * Content::Coupons()
       * 
       * @return
       */
      public function Coupons()
      {

		  $tpl = App::View(BASEPATH . 'view/');
		  $tpl->dir = "admin/";
		  $tpl->template = 'admin/coupons.tpl.php';
		  $tpl->data = Db::run()->select(self::dcTable)->results(); 
		  $tpl->title = Lang::$word->META_T12; 

      }

      /**
       * Content::CouponEdit()
       * 
	   * @param mixed $id
       * @return
       */
	  public function CouponEdit($id)
	  {
		  $tpl = App::View(BASEPATH . 'view/');
		  $tpl->dir = "admin/";
		  $tpl->title = Lang::$word->META_T13;
		  $tpl->crumbs = ['admin', 'coupons', 'edit'];
	
		  if (!$row = Db::run()->first(self::dcTable, null, array("id =" => $id))) {
			  $tpl->template = 'admin/error.tpl.php';
			  $tpl->error = DEBUG ? "Invalid ID ($id) detected [Content.class.php, ln.:" . __line__ . "]" : Lang::$word->META_ERROR;
		  } else {
			  $tpl->data = $row;
			  $tpl->mlist  = App::Membership()->getMembershipList();
			  $tpl->template = 'admin/coupons.tpl.php';
		  }
	  }

      /**
       * Content::CouponSave()
       * 
       * @return
       */
	  public function CouponSave()
	  {
		  $tpl = App::View(BASEPATH . 'view/');
		  $tpl->dir = "admin/";
		  $tpl->title = Lang::$word->META_T14;
		  $tpl->mlist  = App::Membership()->getMembershipList();
		  $tpl->template = 'admin/coupons.tpl.php';
	  }

      /**
       * Content::processCoupon()
       * 
       * @return
       */
	  public function processCoupon()
	  {
	
		  $rules = array(
			  'title' => array('required|string|min_len,3|max_len,60', Lang::$word->NAME),
			  'code' => array('required|string', Lang::$word->DC_CODE),
			  'discount' => array('required|numeric|min_numeric,1|max_numeric,99', Lang::$word->DC_DISC),
			  'type' => array('required|string', Lang::$word->DC_TYPE),
			  'active' => array('required|numeric', Lang::$word->PUBLISHED),
			  );

		  $validate = Validator::instance();
		  $safe = $validate->doValidate($_POST, $rules);
		  
		  if (empty(Message::$msgs)) {
			  $data = array(
				  'title' => $safe->title,
				  'code' => $safe->code,
				  'discount' => $safe->discount,
				  'type' => $safe->type,
				  'membership_id' => Validator::post('membership_id') ? Utility::implodeFields($_POST['membership_id']) : 0,
				  'active' => $safe->active,
				  );
				  
			  (Filter::$id) ? Db::run()->update(self::dcTable, $data, array("id" => Filter::$id)) : $last_id = Db::run()->insert(self::dcTable, $data)->getLastInsertId(); 
			  
			  $message = Filter::$id ? 
			  Message::formatSuccessMessage($data['title'], Lang::$word->DC_UPDATE_OK) : 
			  Message::formatSuccessMessage($data['title'], Lang::$word->DC_ADDED_OK);
			  
			  Message::msgReply(Db::run()->affected(), 'success', $message);
		  } else {
			  Message::msgSingleStatus();
		  }
	  }

      /**
       * Content::Fields()
       * 
       * @return
       */
      public function Fields()
      {

		  $tpl = App::View(BASEPATH . 'view/');
		  $tpl->dir = "admin/";
		  $tpl->crumbs = ['admin', Lang::$word->META_T15];
		  $tpl->template = 'admin/fields.tpl.php';
		  $tpl->data = Db::run()->select(self::cfTable, null, null, "ORDER BY sorting")->results(); 
		  $tpl->title = Lang::$word->META_T10; 

      }

      /**
       * Content::FieldEdit()
       * 
	   * @param mixed $id
       * @return
       */
	  public function FieldEdit($id)
	  {
		  $tpl = App::View(BASEPATH . 'view/');
		  $tpl->dir = "admin/";
		  $tpl->title = Lang::$word->META_T16;
		  $tpl->crumbs = ['admin', 'fields', 'edit'];
	
		  if (!$row = Db::run()->first(self::cfTable, null, array("id =" => $id))) {
			  $tpl->template = 'admin/error.tpl.php';
			  $tpl->error = DEBUG ? "Invalid ID ($id) detected [Content.class.php, ln.:" . __line__ . "]" : Lang::$word->META_ERROR;
		  } else {
			  $tpl->data = $row;
			  $tpl->template = 'admin/fields.tpl.php';
		  }
	  }

      /**
       * Content::FieldSave()
       * 
       * @return
       */
	  public function FieldSave()
	  {
		  $tpl = App::View(BASEPATH . 'view/');
		  $tpl->dir = "admin/";
		  $tpl->title = Lang::$word->META_T17;
		  $tpl->template = 'admin/fields.tpl.php';
	  }

      /**
       * Content::processField()
       * 
       * @return
       */
	  public function processField()
	  {
	
		  $rules = array(
			  'title' => array('required|string|min_len,3|max_len,60', Lang::$word->NAME),
			  'required' => array('required|numeric', Lang::$word->CF_REQUIRED),
			  'active' => array('required|numeric', Lang::$word->PUBLISHED),
			  );

		  $filters = array(
			  'tooltip' => 'string',
			  );
			  
		  $validate = Validator::instance();
		  $safe = $validate->doValidate($_POST, $rules);
		  $safe = $validate->doFilter($_POST, $filters);
		  
		  if (empty(Message::$msgs)) {
			  $data = array(
				  'title' => $safe->title,
				  'tooltip' => $safe->tooltip,
				  'required' => $safe->required,
				  'active' => $safe->active,
				  );
				  
			  if (!Filter::$id) {
				  $data['name'] = Utility::randomString(6);
			  }
			  
			  (Filter::$id) ? Db::run()->update(self::cfTable, $data, array("id" => Filter::$id)) : Db::run()->insert(self::cfTable, $data); 
			  
			  $message = Filter::$id ? 
			  Message::formatSuccessMessage($data['title'], Lang::$word->CF_UPDATE_OK) : 
			  Message::formatSuccessMessage($data['title'], Lang::$word->CF_ADDED_OK);
			  
			  Message::msgReply(Db::run()->affected(), 'success', $message);
		  } else {
			  Message::msgSingleStatus();
		  }
	  }
	  
	  /**
	   * Content::rendertCustomFields()
	   * 
	   * @param mixed $data
	   * @return
	   */
	  public static function rendertCustomFields($data)
	  {
	
		  $html = '';
		  if ($fdata = Db::run()->select(self::cfTable, null, null, "ORDER BY sorting")->results()) {
			  $value = ($data) ? explode("::", $data) : null;
			  foreach ($fdata as $i => $row) {
				  $tootltip = $row->tooltip ? ' <i data-content="' . $row->tooltip . '" class="icon question sign"></i>' : '';
				  $required = $row->required ? ' <i class="icon asterisk"></i>' : '';
				  $html .= '<div class="wojo fields align-middle">';
				  $html .= '<div class="field four wide labeled">';
				  $html .= '<label class="content-right mobile-content-left">' . $row->title . $required . $tootltip . '</label>';
				  $html .= '</div>';
				  $html .= '<div class="six wide field">';
				  if(!empty($value[$i])){
				      $html .= '<input name="custom_' . $row->name . '" type="text" placeholder="' . $row->title . '" value="' . $value[$i] . '">';
				  } else {
					  $html .= '<input name="custom_' . $row->name . '" type="text" placeholder="' . $row->title . '">';
				  }
				  $html .= '</div>';
	              $html .= '</div>';
			  }
			  unset($cfrow);
		  }
	
		  return $html;
	  }

	  /**
	   * Content::rendertCustomFieldsFront()
	   * 
	   * @param mixed $data
	   * @return
	   */
	  public static function rendertCustomFieldsFront($data)
	  {
	
		  $html = '';
		  if ($fdata = Db::run()->select(self::cfTable, null, null, "ORDER BY sorting")->results()) {
			  $value = ($data) ? explode("::", $data) : null;
			  foreach ($fdata as $i => $row) {
				  $tootltip = $row->tooltip ? ' <i data-content="' . $row->tooltip . '" class="icon question sign"></i>' : '';
				  $required = $row->required ? ' <i class="icon asterisk"></i>' : '';
				  $html .= '<div class="wojo block fields">';
				  $html .= '<div class="field">';
				  if(!empty($value[$i])){
				      $html .= '<input name="custom_' . $row->name . '" type="text" placeholder="' . $row->title . '" value="' . $value[$i] . '">';
				  } else {
					  $html .= '<input name="custom_' . $row->name . '" type="text" placeholder="' . $row->title . '">';
				  }
				  $html .= '</div>';
	              $html .= '</div>';
			  }
			  unset($cfrow);
		  }
	
		  return $html;
	  }
	  
	  /**
	   * Content::verifyCustomFields()
	   * 
	   * @param mixed $type
	   * @return
	   */
	  public static function verifyCustomFields()
	  {
	
		  if ($data = Db::run()->select(self::cfTable, null, array("active" => 1, "required" => 1))->results()) {
			  foreach ($data as $row) {
				  Validator::checkPost('custom_' . $row->name, Lang::$word->FIELD_R0 . ' "' . $row->title . '" ' . Lang::$word->FIELD_R100);
			  }
		  }
	  } 
	  
      /**
       * Content::News()
       * 
       * @return
       */
      public function News()
      {

		  $tpl = App::View(BASEPATH . 'view/');
		  $tpl->dir = "admin/";
		  $tpl->crumbs = ['admin', Lang::$word->META_T18];
		  $tpl->template = 'admin/news.tpl.php';
		  $tpl->data = Db::run()->select(self::nTable, null, null, "ORDER BY created DESC")->results(); 
		  $tpl->title = Lang::$word->META_T18; 

      }
	  
      /**
       * Content::NewsEdit()
       * 
	   * @param mixed $id
       * @return
       */
	  public function NewsEdit($id)
	  {
		  $tpl = App::View(BASEPATH . 'view/');
		  $tpl->dir = "admin/";
		  $tpl->title = Lang::$word->META_T19;
		  $tpl->crumbs = ['admin', 'news', 'edit'];
	
		  if (!$row = Db::run()->first(self::nTable, null, array("id =" => $id))) {
			  $tpl->template = 'admin/error.tpl.php';
			  $tpl->error = DEBUG ? "Invalid ID ($id) detected [Content.class.php, ln.:" . __line__ . "]" : Lang::$word->META_ERROR;
		  } else {
			  $tpl->data = $row;
			  $tpl->template = 'admin/news.tpl.php';
		  }
	  }
	  
      /**
       * Content::NewsSave()
       * 
       * @return
       */
	  public function NewsSave()
	  {
		  $tpl = App::View(BASEPATH . 'view/');
		  $tpl->dir = "admin/";
		  $tpl->title = Lang::$word->META_T20;
		  $tpl->template = 'admin/news.tpl.php';
	  }
	  
      /**
       * Content::processNews()
       * 
       * @return
       */
	  public function processNews()
	  {
	
		  $rules = array(
			  'title' => array('required|string|min_len,3|max_len,100', Lang::$word->NAME),
			  'active' => array('required|numeric', Lang::$word->PUBLISHED),
			  );

		  $filters = array(
			  'body' => 'advanced_tags',
			  );
			  
		  $validate = Validator::instance();
		  $safe = $validate->doValidate($_POST, $rules);
		  $safe = $validate->doFilter($_POST, $filters);
		  
		  if (empty(Message::$msgs)) {
			  $data = array(
				  'title' => $safe->title,
				  'body' => $safe->body,
				  'author' => App::Auth()->name,
				  'active' => $safe->active,
				  );
			  
			  (Filter::$id) ? Db::run()->update(self::nTable, $data, array("id" => Filter::$id)) : Db::run()->insert(self::nTable, $data); 
			  
			  $message = Filter::$id ? 
			  Message::formatSuccessMessage($data['title'], Lang::$word->NW_UPDATE_OK) : 
			  Message::formatSuccessMessage($data['title'], Lang::$word->NW_ADDED_OK);
			  
			  Message::msgReply(Db::run()->affected(), 'success', $message);
		  } else {
			  Message::msgSingleStatus();
		  }
	  }
	  
      /**
       * Content::renderNews()
       * 
       * @return
       */
      public function renderNews()
      {

		  return Db::run()->select(self::nTable, null, array("active" => 1), "ORDER BY created DESC")->result();

      }
  }
