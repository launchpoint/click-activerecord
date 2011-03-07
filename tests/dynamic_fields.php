<?

$u = User::find();
foreach(User::$has_many as $hm_name=>$data)
{
  $n = singularize($hm_name);
  $f = "find_{$n}_by_id";
  $u->$f(4);
  $f = "{$n}_count";
  $u->$f;
}
ae($u->is_username_dirty, false);
$u->username = 'tester';
ae($u->is_username_dirty, true);  
