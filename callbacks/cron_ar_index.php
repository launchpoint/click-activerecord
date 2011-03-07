<?
ini_set("max_execution_time", 900);
foreach($models as $model_name)
{
  $tn = strtolower(spacify($model_name,'_'));
  if ( !responds_to($tn."_index") ) continue;
  $page = 1;
  while (true) {
    // INSERT NEW
    $params = array(
      "conditions" => "id not in (select record_id from search where model_name = '$model_name')",
      "page_size" => 1000,
      "current_page" => $page
    );
/*
    // UPDATE EVERYTHING
    $params = array(
      "page_size" => 1000,
      "current_page" => $page
    );
*/
    $objs = eval("return $model_name::find_all(\$params);");
    if (!$objs) break;
    foreach($objs as $obj)
    {
      $obj->index();
    }
    $page++;
  }
}
