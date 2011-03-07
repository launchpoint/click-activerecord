<?

$models = array();

function compute_model_settings($klass, $tn)
{
	global $attribute_names;
	global $model_settings;
	
	$arr = query_assoc("desc $tn");
	$attr=array();
	foreach($arr as $rec)
	{
		$attr[] = $rec['Field'];

 		$parts = preg_split("/[\\(\\)]/", $rec['Type']);
		$typeinfo[0] = $parts[0];
		$typeinfo[1] = null;
		if (count($parts)>1) $typeinfo[1] = $parts[1];

		$model_settings[$klass]['type'][$rec['Field']] = $typeinfo;
		$model_settings[$klass]['is_nullable'][$rec['Field']] = $rec['Null']=='YES' || in($rec['Field'], 'id', 'created_at', 'updated_at');
		$model_settings[$klass]['is_auto_increment'][$rec['Field']] = ($rec['Extra'] == 'auto_increment');
		$parts = preg_split("/[\\(\\)]/", $rec['Type']);
		$type = array_shift($parts);
		if (count($parts)>0) $length = array_shift($parts);
		switch($type)
		{
			case 'varchar':
				$model_settings[$klass]['max_length'][$rec['Field']] = $length;
				break;
    		case 'char':
    			$model_settings[$klass]['max_length'][$rec['Field']] = $length;
    			break;
			case 'int':
				$model_settings[$klass]['min_value'][$rec['Field']] = -2147483648;
				$model_settings[$klass]['max_value'][$rec['Field']] = 2147483647;
				$regex = '^\s*-?(\d+)\s*$';
				if ($model_settings[$klass]['is_auto_increment'][$rec['Field']] || $model_settings[$klass]['is_nullable'][$rec['Field']]) $regex = '(?:^\s*$)|'.$regex;
				$model_settings[$klass]['db_format'][$rec['Field']] = '/'.$regex.'/';
				$model_settings[$klass]['format'][$rec['Field']] = '/'.$regex.'/';
				break;
  		case 'bigint':
  			$model_settings[$klass]['min_value'][$rec['Field']] = -9223372036854775808;
  			$model_settings[$klass]['max_value'][$rec['Field']] = 9223372036854775807;
				$regex = '^\s*(\d+)\s*$';
				if ($model_settings[$klass]['is_auto_increment'][$rec['Field']] || $model_settings[$klass]['is_nullable'][$rec['Field']]) $regex = '(?:^\s*$)|'.$regex;
				$model_settings[$klass]['db_format'][$rec['Field']] = '/'.$regex.'/';
				$model_settings[$klass]['format'][$rec['Field']] = '/'.$regex.'/';
  			break;
			case 'tinyint':
				$model_settings[$klass]['min_value'][$rec['Field']] = -128;
				$model_settings[$klass]['max_value'][$rec['Field']] = 127;
				$regex = '^\s*(\d+)\s*$';
				if ($model_settings[$klass]['is_auto_increment'][$rec['Field']] || $model_settings[$klass]['is_nullable'][$rec['Field']]) $regex = '(?:^\s*$)|'.$regex;
				$model_settings[$klass]['db_format'][$rec['Field']] = '/'.$regex.'/';
				$model_settings[$klass]['format'][$rec['Field']] = '/'.$regex.'/';
				break;
			case 'bool':
				$model_settings[$klass]['value_set'][$rec['Field']] = array(0,1);
				break;
      case 'longtext':
        $model_settiongs[$klass]['max_length'][$rec['Field']] = pow(2,64);
        break;
      case 'datetime':
				$regex = '^\s*(\d+)\s*$';
				if ($model_settings[$klass]['is_auto_increment'][$rec['Field']] || $model_settings[$klass]['is_nullable'][$rec['Field']]) $regex = '(?:^\s*$)|'.$regex;
				$model_settings[$klass]['db_format'][$rec['Field']] = '/'.$regex.'/';
				$model_settings[$klass]['format'][$rec['Field']] = '/'.$regex.'/';
        break;
      case 'timestamp':
				$regex = '^\s*(\d+)\s*$';
				if ($model_settings[$klass]['is_auto_increment'][$rec['Field']] || $model_settings[$klass]['is_nullable'][$rec['Field']]) $regex = '(?:^\s*$)|'.$regex;
				$model_settings[$klass]['db_format'][$rec['Field']] = '/'.$regex.'/';
				$model_settings[$klass]['format'][$rec['Field']] = '/'.$regex.'/';
        break;
      case 'double':
      case 'float':
				$model_settings[$klass]['min_value'][$rec['Field']] = -99999.0;
				$model_settings[$klass]['max_value'][$rec['Field']] = 99999.0;
				$regex = '^-?[0-9]*\.?[0-9]+$';
				if ($model_settings[$klass]['is_auto_increment'][$rec['Field']] || $model_settings[$klass]['is_nullable'][$rec['Field']]) $regex = '(?:^\s*$)|'.$regex;
				$model_settings[$klass]['db_format'][$rec['Field']] = '/'.$regex.'/';
				$model_settings[$klass]['format'][$rec['Field']] = '/'.$regex.'/';
				break;
			case 'decimal':
				$regex = '^[-+]?[0-9]*\.?[0-9]+$';
				if ($model_settings[$klass]['is_auto_increment'][$rec['Field']] || $model_settings[$klass]['is_nullable'][$rec['Field']]) $regex = '(?:^\s*$)|'.$regex;
				$model_settings[$klass]['db_format'][$rec['Field']] = '/'.$regex.'/';
				$model_settings[$klass]['max_length'][$rec['Field']] = 10;
				$model_settings[$klass]['format'][$rec['Field']] = '/'.$regex.'/';
				break;
		  case 'blob':
		    break;
		  default:
		    click_error("Unsupported data type {$rec['Type']}", array($klass, $rec));
		    break;
		}
		$model_settings[$klass]['default_value'][$rec['Field']] = $rec['Default'];
	}
	$attribute_names[$klass] = $attr;
	return $attr;
}


function find_belongs_tos($tables)
{
  $belongs_to = array();
  foreach($tables as $table_name=>$fields)
  {
		$belongs_to[$table_name] = array();
		foreach($fields as $data)
		{
			$field_name = $data['Field'];
			if (endswith($field_name, '_id'))
			{
			  $bt_alias = startof($field_name,'_id');
			  $bt_table_name = pluralize($bt_alias);
			  if ($data['Comment']!='')
			  {
			    $bt_table_name = $data['Comment'];
			    if ( !array_key_exists( $bt_table_name, $tables ) )
			    {
			      click_error("Error in table $table_name.$field_name - mapping $bt_table_name in comment is not a valid table.", $tables);
			    }
			  } 
				$belongs_to[$table_name][$bt_alias] = array($bt_table_name, $field_name);
			}
		}
  }
  return $belongs_to;
}

function find_has_manys($tables)
{
  $has_many = array();
  foreach($tables as $table_name=>$fields)
  {
    $stn = singularize($table_name);
		$has_many[$table_name] = array();
		$hm_duplicates = array();
		foreach($tables as $hm_table_name=>$hm_fields)
		{
			foreach($hm_fields as $data)
			{
				$field_name = $data['Field'];
 			  if ($data['Comment']!='') $field_name = singularize($data['Comment']) .'_id';
				if ($field_name != $stn.'_id') continue;

			  if (isset($hm_duplicates[$hm_table_name]))
			  {
			    if($hm_duplicates[$hm_table_name]===false) // duplicate found, but no fixup yet
			    {
			     $new_alias = $hm_table_name . '_by_' . $has_many[$table_name][$hm_table_name][1];
			     $has_many[$table_name][$new_alias] = $has_many[$table_name][$hm_table_name];
			     unset($has_many[$table_name][$hm_table_alias]);
			     $hm_duplicates[$hm_table_name] = true;
			    }
			    $hm_table_alias = $hm_table_name . '_by_' . $data['Field'];
			  } else {
  			  $hm_duplicates[$hm_table_name] = false; // not duplicated yet
  			  $hm_table_alias = $hm_table_name;
			  }
				$has_many[$table_name][$hm_table_alias] = array($hm_table_name, $data['Field']);
			}
		}
  }
  return $has_many;
}

function find_tables()
{
  global $codegen;
  global $models;
  global $model_settings, $attribute_names;
  
  $tables = array();
  $recs = query_assoc("show tables");
  foreach($recs as $table)
  {
    foreach($table as $k=>$table_name)
    {
      $tables[$table_name] = query_assoc("show full columns from $table_name");
      $stn = singularize($table_name);
      $klass=classify($stn);
      compute_model_settings($klass, $table_name);
      $models[] = $klass;
      
    }
  }
  return $tables;
}

function find_has_many_throughs($has_many, $belongs_to)
{
  $has_many_through = array();
  foreach($has_many as $table_name=>$hm_data)
  {
    $has_many_through[$table_name] = array();
    foreach($hm_data as $hm_name=>$hm_info)
    {
      foreach($belongs_to[$hm_info[0]] as $bt_name=>$bt_info)
      {
        if($bt_info[1]==$hm_info[1]) continue;
        $hmt_name = pluralize($bt_name);
        if(array_key_exists($hmt_name, $has_many[$table_name])) $hmt_name = "{$hmt_name}_through_{$hm_name}";
        $has_many_through[$table_name][$hmt_name] = array($hm_name, $bt_name);
      }
    }
  }
  
  return $has_many_through;
}

function find_attribute_types($tables, $belongs_to, $has_many, $has_many_through)
{
  global $model_settings;
  
  $attribute_types = array();
  foreach($tables as $table_name=>$fields)
  {
    $stn = singularize($table_name);
    $klass=classify($stn);
  
		$attribute_types[$table_name] = array();

    global $activerecord_settings;
  
    foreach($model_settings[$klass]['type'] as $k=>$column_info)
    {
      list($type,$length) = $column_info;
      $v = array('type'=>$activerecord_settings['type_mappings'][$type] , 'required'=>!$model_settings[$klass]['is_nullable'][$k], 'default'=>$model_settings[$klass]['default_value'][$k]);
      if(isset($activerecord_settings['conventions'][$type]))
      {
        foreach($activerecord_settings['conventions'][$type] as $c_word=>$c_value)
        {
          if(has_word($k, $c_word))
          {
            $v['type'] = $c_value;
          }
        }
      }
      $attribute_types[$table_name][$k] = $v;
    }
    foreach($belongs_to[$table_name] as $k=>$info)
    {
      $attribute_types[$table_name][$info[1]]['type'] = 'select';
      $attribute_types[$table_name][$info[1]]['item_array'] = 'available_'.pluralize($k);
      $attribute_types[$table_name][$info[1]]['display_field']='name';
      $attribute_types[$table_name][$info[1]]['value_field']='id';
      $attribute_types[$table_name][$k]['type'] = 'select';
      $attribute_types[$table_name][$k]['item_array'] = 'available_'.pluralize($k);
      $attribute_types[$table_name][$k]['display_field']='name';
      $attribute_types[$table_name][$k]['value_field']='id';
    }
    
    foreach($has_many[$table_name] as $k=>$info)
    {
      $attribute_types[$table_name][$k] = array('type'=>'mutex', 'item_array'=>'available_'.$k, 'selected_item_array'=>$k, 'display_field'=>'name', 'value_field'=>'id', 'klass'=>singularize(classify($info[0])));
    }
    foreach($has_many_through[$table_name]  as $k=>$hmk)
    {
      $hm_table_name = $has_many[$table_name][$hmk[0]][0];
      $klass = singularize(classify($belongs_to[$hm_table_name][$hmk[1]][0])); // Have to look up the underlying table from the $belongs_to via the $has_many assoc name
      $attribute_types[$table_name][$k] = array('type'=>'mutex', 'item_array'=>'available_'.$k, 'selected_item_array'=>$k, 'display_field'=>'name', 'value_field'=>'id', 'klass'=>$klass);
    }
  }
  return $attribute_types;
}

function find_uniques($tables)
{
  $uniques = array();
  foreach($tables as $table_name=>$fields)
  {
    $uniques[$table_name] = array();
    foreach($fields as $field_info)
    {
      if($field_info['Key']=='UNI')
      {
        $uniques[$table_name][] = $field_info['Field'];
      }
    }
  }
  return $uniques;
}

function codegen_models()
{
  global $codegen;
  global $models;
  global $model_settings, $attribute_names;
  
  $tables = find_tables();
  $belongs_to = find_belongs_tos($tables);
  $has_many = find_has_manys($tables);
  $has_many_through = find_has_many_throughs($has_many, $belongs_to);
  $attribute_types = find_attribute_types($tables, $belongs_to, $has_many, $has_many_through);
  $uniques = find_uniques($tables);
  
  foreach($tables as $table_name=>$fields)
  {
    $stn = singularize($table_name);
    $klass=classify($stn);
  
		$s_belongs_to = s_var_export($belongs_to[$table_name]);
		$s_has_many = s_var_export($has_many[$table_name]);
		$s_hmt = s_var_export($has_many_through[$table_name] );
    $s_attribute_types = s_var_export($attribute_types[$table_name]);
    $s_uniques = s_var_export($uniques[$table_name]); 
  		
		$php = "<?\n".eval_php(ACTIVERECORD_FPATH."/codegen/class_stub.php", 
		  array(
		    'klass'=>$klass,
		    's_belongs_to'=>$s_belongs_to,
		    's_has_many'=>$s_has_many,
		    's_hmt'=>$s_hmt,
		    's_attribute_types'=>$s_attribute_types,
		    'fields'=>$fields,
		    'table_name'=>$table_name,
		    'stn'=>$stn,
		    's_uniques'=>$s_uniques,
		  )
		);

    $php .= codegen_model_extension($stn);
 
    file_put_contents(CODEGEN_CLASSES_CACHE_FPATH."/{$klass}.php", $php);
	}
	$codegen[] = '$models = '.s_var_export($models).';';
	$codegen[] = '$attribute_names = '.s_var_export($attribute_names).';';
	$codegen[] = '$model_settings = '.s_var_export($model_settings).';';
}


function codegen_model_extension($stn)
{
  global $codegen;
  global $manifests, $models, $run_mode;

  $php = '';
  foreach($manifests as $module_name=>$manifest)
  {
    $this_module_fpath = $manifest['path'];
    $lib_path = "$this_module_fpath/models/$stn.php";
    if (!file_exists($lib_path)) continue;
    $php .= "require('$lib_path');\n";
  }
  return $php;
}


if ($run_mode==RUN_MODE_DEVELOPMENT)
{
  $res = query_assoc("show variables where variable_name = 'ft_min_word_len'");
  if ($res[0]['Value'] > $activerecord_settings['ft_min_word_len']) click_error("mySQL FullText searching error. Set ft_min_word_len >= {$activerecord_settings['ft_min_word_len']}. Currently set to: ". $res[0]['Value']);
}

$mtime = 0;
$start = microtime();
$keys = array();
$recs = query_assoc("show tables");
$tables = collect($recs, "Tables_in_{$__click['build']['database']['catalog']}");
foreach($tables as $k)
{
  $recs = query_assoc("show full columns from `$k`");
  $cols = array("Field", "Type", "Null", "Key", "Default", "Comment");
  $digest = array();
  for($i=0;$i<count($recs);$i++)
  {
    $digest[$i] = $k.":";
    foreach($cols as $col)
    {
      $digest[$i] .= ":".$recs[$i][$col];
    }
  }
  $digest = md5(join('|',$digest));
  $keys[] = $digest;
}

foreach($manifests as $module_name=>$manifest)
{
  $fpath = $manifest['path']."/models";
  foreach(glob($fpath."/*.php") as $fname)
  {
    $keys[] = md5($fname);
    $keys[] = md5_file($fname);
  }
}

$keys[] = md5_file(ACTIVERECORD_FPATH."/codegen/class_stub.php");
$keys[] = md5_file(ACTIVERECORD_FPATH."/codegen.php");
$keys[] = md5_file(ACTIVERECORD_FPATH."/codegen.php");
sort($keys);
$md5 = md5(join('|',$keys));
$fpath = ACTIVERECORD_CACHE_FPATH."/$md5";

if(file_exists($fpath) && $run_mode!=RUN_MODE_TEST) 
{
  $codegen = null;
}

if($codegen!==null)
{
  clear_cache(ACTIVERECORD_CACHE_FPATH);
  touch($fpath);
  codegen_models();
}
