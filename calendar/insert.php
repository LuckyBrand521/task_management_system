<?php

//insert.php

$connect = new PDO('mysql:host=localhost;dbname=tms_db', 'root', '');

if(isset($_POST["title"]))
{
 $query = "
 INSERT INTO task_list 
 (task, start_event, end_event, project_id, user_id) 
 VALUES (:title, :start_event, :end_event, :project_id, :user_id)
 ";
 $statement = $connect->prepare($query);
 $statement->execute(
  array(
   ':title'  => $_POST['title'],
   ':start_event' => $_POST['start'],
   ':end_event' => $_POST['end'],
   ':project_id' => $_POST['project_id'],
   ':user_id' => $_POST['user_id']
  )
 );
}


?>
