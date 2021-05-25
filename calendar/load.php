<?php

//load.php

$connect = new PDO('mysql:host=localhost;dbname=tms_db', 'root', '');

$data = array();
$pid = 0;
$pid = isset($_REQUEST['project_id'])? $_REQUEST['project_id']: 0;

$query = "SELECT * FROM task_list WHERE project_id = ".$pid." ORDER BY id";

$statement = $connect->prepare($query);

$statement->execute();

$result = $statement->fetchAll();

foreach($result as $row)
{
 $data[] = array(
  'id'   => $row["id"],
  'title'   => $row["task"],
  'start'   => $row["start_event"],
  'end'   => $row["end_event"]
 );
}

echo json_encode($data);

?>