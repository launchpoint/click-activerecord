class <?=$klass?> extends ActiveRecord
{
	static $has_many = <?=$s_has_many?>;
	static $belongs_to = <?=$s_belongs_to?>;
	static $has_many_through = <?=$s_hmt?>;
  static $eager_load = array();
  static $validates_presence_of = array();
  static $validates_length_of = array();
  static $validates_uniqueness_of = <?=$s_uniques?>;
  static $validates_format_of = array();
  static $table_name = '<?=$table_name?>';
  static $attribute_types = <?=$s_attribute_types?>;
  var $klass = '<?=$klass?>';
  var $tableized_klass = '<?=$stn?>';

  static function new_model_instance($params=array())
  {
    return ActiveRecord::_new_model_instance('<?=$klass?>',$params);
  }

  static function count($params=array())
  {
    return ActiveRecord::_count('<?=$klass?>', $params);
  }
 
  static function create($params=array())
  {
    return ActiveRecord::_create('<?=$klass?>', $params);
  }

  static function delete_all($params=array())
  {
    return ActiveRecord::_delete_all('<?=$klass?>', $params);
  }
  
  static function find($params=array())
  {
    return ActiveRecord::_find('<?=$klass?>', $params);
  }
  

  static function find_all($params=array())
  {
    return ActiveRecord::_find_all('<?=$klass?>', $params);
  }

  static function find_or_create_by($params=array())
  {
    return ActiveRecord::_find_or_create_by('<?=$klass?>', $params);
  }

  static function create_or_update_by($params=array())
  {
    return ActiveRecord::_create_or_update_by('<?=$klass?>', $params);
  }


  static function find_or_new_by($params=array())
  {
    return ActiveRecord::_find_or_new_by('<?=$klass?>', $params);
  }
    
  static function select_assoc($params)
  {
    return ActiveRecord::_select_assoc('<?=$klass?>', $params);
  }

  <? foreach($fields as $data) {
			$field_name = $data['Field'];
  ?>
			
  static function sort_by_<?=$field_name?>(&$objs)
  {
    sort_by('<?=$field_name?>', $objs);
  }
  
  static function find_by_<?=$field_name?>($val, $params=array())
  {
    $params = ActiveRecord::add_condition($params, "`<?=$table_name?>`.`<?=$field_name?>` = ?", $val);
    return ActiveRecord::_find('<?=$klass?>', $params);
  }


  static function find_all_by_<?=$field_name?>($val, $params=array())
  {
    $params = ActiveRecord::add_condition($params, "`<?=$table_name?>`.`<?=$field_name?>` = ?", $val);
    return ActiveRecord::_find_all('<?=$klass?>', $params);
  }
  
  static function find_or_create_by_<?=$field_name?>($val, $params=array())
  {
    $params = ActiveRecord::add_condition($params, "`<?=$table_name?>`.`<?=$field_name?>` = ?", $val);
    $params['attributes']['<?=$field_name?>'] = $val;
    return ActiveRecord::_find_or_create_by('<?=$klass?>', $params);
  }

  static function find_or_new_by_<?=$field_name?>($val, $params=array())
  {
    $params = ActiveRecord::add_condition($params, "`<?=$table_name?>`.`<?=$field_name?>` = ?", $val);
    $params['attributes']['<?=$field_name?>'] = $val;
    return ActiveRecord::_find_or_new_by('<?=$klass?>', $params);
  } 
  <? } ?>
}