<?php
include ('./PMA/PmaTix.class.php');
include ('wp-config.php');
include ('./PMA/PmaDb.class.php');

echo (ABSPATH . PMAINC);

$dbo = PmaPdoDb::getInstance();
$dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);

$sql = "call sp_getDeptrequestTypes(1)";

$dbo->query($sql);

$rows = $dbo->resultset();
echo "<select name='ReqType'><option/>";
foreach ($rows as $row){
    echo "<option value='{$row['id']}'>{$row['request_type']}</option>";
}
echo "</select>";
//echo "<pre>";
//print_r($rows);
//echo "</pre>";

//$dbo->queryForObjs($sql);

//$rows = $dbo->queryForObjs($sql);
//echo "<pre>";
//print_r($rows);
//echo "</pre>";

//foreach($rows as $row){
//    echo $row->full_name . '<br/>';
//}

//while($rows){
//    echo 'biteme<br/>';
//    echo $rows->first_name . ' ' . $rows->last_name . '<br/>';
//}


//if($result = $dbo->DoQuery($sql)){
//    while($row = $result->fetch(PDO::FETCH_ASSOC)){
 //       echo $row;
  //  }
//}

//$query->setFetchMode(PDO::FETCH_CLASS,'PmaTix');
//while($r = $query->fetch()){
//    echo $r->summary, '<br>';
//    echo '<pre>' . print_r($r) . '</pre>';
//}


?>

