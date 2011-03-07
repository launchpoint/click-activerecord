<?

function get_property($o, $name)
{
  foreach( array($name, $name.'__builtin') as $prop)
  {
    $f = singularize(tableize(get_class($o)))."_get_$prop";
    if (function_exists($f))
    {
      return call_user_func($f, $o);
    }
    $cf=$f."__d";
    if (function_exists($cf))
    {
      $prop = "__cached__$prop";
      if (isset($o->$prop)) return $o->$prop;;
      $o->$prop = call_user_func($cf, $o);
      return $o->$prop;
    }
  }

  if (array_key_exists($name, eval("return {$o->klass}::\$belongs_to;"))!==FALSE)
  {
    $o->a($name);
    return $o->$name;
  }
  if (array_key_exists($name, eval("return {$o->klass}::\$has_many;"))!==FALSE)
  {
    $o->a($name);
    return $o->$name;
  }
  if (array_key_exists($name, eval("return {$o->klass}::\$has_many_through;"))!==FALSE)
  {
    $o->a($name);
    return $o->$name;
  }
  
  $hms = eval("return {$o->klass}::\$has_many;");
	foreach($hms as $hm=>$arr)
	{
	  $fk = $arr[1];
	  $tn = singularize($hm);
	  $kn = classify(singularize($arr[0]));
    if(preg_match("/^{$tn}_count$/", $name, $matches))
    {
      return get_model_count($o, $kn, $fk);
    }
  }
  
  if(preg_match("/^is_(.+)_dirty$/",$name,$matches))
  {
    list($junk,$prop_name) = $matches;
    $ov = "{$prop_name}_original_value";
    return $o->$prop_name != $o->$ov;
  }

  click_error("No getter defined $f");
}

function call_ar_func($o, $name, $arguments)
{
    $f = singularize(tableize(get_class($o)))."_$name";
    $args = array_merge(array($o), $arguments);
    if (function_exists($f))
    {
      return call_user_func_array($f, $args);
    }
    $cf = $f."__d";
    if (function_exists($cf))
    {
       $prop = "__cached__{$name}__" . array_md5($arguments);
       if (isset($o->$prop)) return $o->$prop;;
      $o->$prop = call_user_func_array($cf, $args);
      return $o->$prop;
    }
    
    $hms = eval("return {$o->klass}::\$has_many;");
    if(preg_match('/^find_(.+)_by_(.+)$/', $name, $matches))
    {
      list($junk,$hm_name, $prop_name) = $matches;
      if(array_key_exists($hm_name,$hms))
      {
        $val = array_shift($arguments);
        $sort_by = null;
        if($arguments) $sort_by = array_shift($arguments);
        $v = get_collection_members_by_prop_val($o->$hm_name, $prop_name, $val, $sort_by);
      } else {
        list($val) = $arguments;
        $hm_name = pluralize($hm_name);
        $v = get_collection_member_by_prop($o->$hm_name, $prop_name, $val);
      }
      return $v;
    }

    if(preg_match('/^purge(.+)$/', $name, $matches))
    {
      list($junk,$prop_name) = $matches;
      $o->purge($prop_name);
      return;
    }

    if(isset($hms[$name]))
    {
      $params = array();
      if(count($arguments)>0) $params = array_shift($arguments);
      $params = ActiveRecord::add_condition($params, "{$hms[$name][1]} = ?", $o->id);
      $objs = ActiveRecord::_find_all(classify(singularize($hms[$name][0])), $params);
      return $objs;
    }
    
    
        
    click_error("No function $f()");
}


function get_model_count($o, $kn, $fk)
{
  $params = array(
    'columns'=>'count(id) s',
    'conditions'=>array("$fk = ?", $o->id)
  );

  $res = eval("return $kn::select_assoc(\$params);");
  return (int)($res[0]['s']);
}      

function get_collection_member_by_prop($collection, $field_name, $val)
{
  if(!$collection) return null;
  foreach($collection as $o)
  {
    if ($o->$field_name == $val) return $o;
  }
  return null;
}

function &get_collection_members_by_prop_val($collection, $field_name, $val, $sort_by)
{
  $res=array();
  if(!$collection) return $res;
  foreach($collection as $o)
  {
    if ($o->$field_name == $val) $res[]=$o;
  }
  if($sort_by)
  {
    qsort($res, $sort_by);
  }
  return $res;
}

function has_word($s, $word)
{
  $words = explode(' ', strtolower(humanize($s)));
  $word = strtolower($word);
  return array_search($word, $words)!==false;
}

function activerecord_responds_to($klass, $name)
{
  foreach( array($name, $name.'__builtin') as $prop)
  {
    $f = singularize(tableize($klass))."_get_$prop";
    if (function_exists($f)) return true;
    $cf=$f."__d";
    if (function_exists($cf)) return true;
  }
  
  foreach( array($name, $name.'__builtin') as $prop)
  {
    $f = singularize(tableize($klass))."_$prop";
    if (function_exists($f)) return true;
    $cf=$f."__d";
    if (function_exists($cf)) return true;
  }
    
  if (array_key_exists($name, eval("return {$klass}::\$belongs_to;"))!==FALSE) return true;
  if (array_key_exists($name, eval("return {$klass}::\$has_many;"))!==FALSE) return true;
  if (array_key_exists($name, eval("return {$klass}::\$has_many_through;"))!==FALSE) return true;
  
  $hms = eval("return {$klass}::\$has_many;");
	foreach($hms as $hm=>$arr)
	{
	  $fk = $arr[1];
	  $tn = singularize($hm);
	  $kn = classify(singularize($arr[0]));
    if(preg_match("/^{$tn}_count$/", $name, $matches)) return true;
  }
  
  if(preg_match("/^is_(.+)_dirty$/",$name,$matches)) return true;
  
  return false;
}
