<?


global $activerecord_settings;

if(!isset($activerecord_settings['ft_min_word_len'])) $activerecord_settings['ft_min_word_len'] = 2;

if(!isset($activerecord_settings['type_mappings'])) $activerecord_settings['type_mappings'] = array();
$mappings = array(
  'int'=>'integer',
  'tinyint'=>'check',
  'datetime'=>'date',
  'longtext'=>'textarea',
  'text'=>'text',
  'varchar'=>'text',
  'char'=>'text',
  'decimal'=>'float',
  'double'=>'float',
  'bigint'=>'integer',
  'blob'=>'blob',
  'float'=>'float',
);
foreach($mappings as $k=>$v)
{
  if(!isset($activerecord_settings['type_mappings'][$k])) $activerecord_settings['type_mappings'][$k] = $v;
}

if(!isset($activerecord_settings['conventions'])) $activerecord_settings['conventions'] = array();
$conventions = array(
  'decimal'=>array(
    'price'=>'currency',
    'budget'=>'currency',
  ),
  'varchar'=>array(
    'status'=>'title',
    'email'=>'email_address',
    'zip'=>'zip_code',
    'phone'=>'phone_number',
  ),
);
foreach($conventions as $k=>$v)
{
  if(!isset($activerecord_settings['conventions'][$k])) $activerecord_settings['conventions'][$k] = $v;
}
