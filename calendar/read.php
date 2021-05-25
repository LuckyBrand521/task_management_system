<?php

//update.php

$connect = new PDO('mysql:host=localhost;dbname=tms_db', 'root', '');

if(isset($_POST["id"])){
    $query = "
        SELECT *
        FROM task_list
        WHERE id=:id
    ";
    $statement = $connect->prepare($query);
    $statement->execute(
        array(
        ':id'   => $_POST['id']
        )
    );
    $result = $statement->fetch();
    echo json_encode($result);
}
if(isset($_POST["week"])){
    $data = array();
    $start_date = $_POST["start_date"];
    $end_date = date('Y-m-d',strtotime(' + 7 day', strtotime($start_date)));
    $query = "SELECT * FROM task_list WHERE start_event < :end_date AND end_event > :start_date";
    $statement = $connect->prepare($query);
    $statement->execute(
        array(
            'start_date' => $start_date.' 00:00:00',
            'end_date' => $end_date.' 00:00:00'
        )
    );
    $plans = $statement->fetchAll();
    foreach($plans as $row){
        //calculate the time interval of each plan
        $plan_interval = abs(strtotime($row["start_event"]) - strtotime($row["end_event"])) / 60; //period of plan for minutes
        //calculate the available time interval of each plan for certain week
        $interval = $plan_interval;
        if((strtotime($start_date.' 00:00:00') - strtotime($row["start_event"])) > 0)
            $interval = $plan_interval - (strtotime($start_date.' 00:00:00') - strtotime($row["start_event"]))/60;
        if((strtotime($row["end_event"]) - strtotime($end_date.' 00:00:00')) > 0)
            $interval = $interval - (strtotime($row["end_event"]) - strtotime($end_date.' 00:00:00'))/60;
        $data[] = array(
            'id' => $row["id"],
            'start' => $row["start_event"],
            'end' => $row["end_event"],
            'title' => $row["task"],
            'plan_interval' => $plan_interval,
            'interval' => $interval
        );
    }
    echo json_encode($data);
}

?>
