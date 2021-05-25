<?php
    session_start();
    $connect = new PDO('mysql:host=localhost;dbname=tms_db', 'root', '');
 
    $today = new DateTime();
    if(isset($_POST["year"]) && isset($_POST["week"])){
        $year = $_POST["year"];
        $week = $_POST["week"];
    } else {
        $year = $today->format('Y');
        $week = $today->format('W');
    }

    function getStartAndEndDate($week, $year) {
        $dto = new DateTime();
        $dto->setISODate($year, $week);
        $ret['week_start'] = $dto->format('Y-m-d 00:00:00');
        $dto->modify('+6 days');
        $ret['week_end'] = $dto->format('Y-m-d 00:00:00');
        return $ret;
    }
    //get task for the week requested
        $data = array();
        $where = "";
        if($_SESSION['login_type'] != 4){
            $where = " p.manager_id = '{$_SESSION['login_id']}' ";
        }
        $start_date = getStartAndEndDate(19, $year)['week_start'];
        $end_date = getStartAndEndDate(19, $year)['week_end'];
        $query = 'SELECT t.*, p.id as project_id, p.name as project_name FROM task_list t join project_list p WHERE t.start_event < :end_date AND t.end_event > :start_date AND'. $where;
        
        $statement = $connect->prepare($query);
        $statement->execute(
            array(
                'start_date' => $start_date,
                'end_date' => $end_date
            )
        );
        $plans = $statement->fetchAll();
        foreach($plans as $row){
            //calculate the time interval of each plan
            $plan_interval = abs(strtotime($row["start_event"]) - strtotime($row["end_event"])) / 60; //period of plan for minutes
            //calculate the available time interval of each plan for certain week
            $interval = $plan_interval;
            if((strtotime($start_date) - strtotime($row["start_event"])) > 0)
                $interval = $plan_interval - (strtotime($start_date) - strtotime($row["start_event"]))/60;
            if((strtotime($row["end_event"]) - strtotime($end_date)) > 0)
                $interval = $interval - (strtotime($row["end_event"]) - strtotime($end_date))/60;
            $data[] = array(
                'id' => $row["id"],
                'project_id' =>$row["project_id"],
                'project_name' => $row["project_name"],
                'start' => $row["start_event"],
                'end' => $row["end_event"],
                'title' => $row["task"],
                'plan_interval' => $plan_interval,
                'interval' => $interval
            );
        }
        echo json_encode($data);
