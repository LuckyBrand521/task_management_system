<?php

//update.php

$connect = new PDO('mysql:host=localhost;dbname=tms_db', 'root', '');

if(isset($_POST["id"]))
{
    if(isset($_POST["content"])){
        $query = "
        UPDATE task_list 
        SET task=:title, start_event=:start_event, end_event=:end_event, description=:content
        WHERE id=:id
        ";
        $statement = $connect->prepare($query);
        $statement->execute(
            array(
                ':title'  => $_POST['title'],
                ':start_event' => $_POST['start'],
                ':end_event' => $_POST['end'],
                ':id'   => $_POST['id'],
                ':content'   => $_POST['content']
            )
        );
    } else{
        $query = "
        UPDATE task_list 
        SET task=:title, start_event=:start_event, end_event=:end_event
        WHERE id=:id
        ";
        $statement = $connect->prepare($query);
        $statement->execute(
        array(
            ':title'  => $_POST['title'],
            ':start_event' => $_POST['start'],
            ':end_event' => $_POST['end'],
            ':id'   => $_POST['id']
        )
        );
    }
}

?>
