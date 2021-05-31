<?php

header('Content-type: text/plain; charset=utf-8');

//Db
$user = "Username";
$pass = "Password";
$host = "Host";
$DataBase = "Database";
$tableName = "TheTableName";

$sqlFill = "";
 $sqlFill .= "SELECT ";
 $sqlFill .= "	`".$DataBase."`.`tblticket`.`ticketId` AS `ticketId`, ";
 $sqlFill .= "	`".$DataBase."`.`tblticket`.`colAo` AS `colAo`, ";
 $sqlFill .= "	`".$DataBase."`.`tblticket`.`colSb` AS `colSb`, ";
 $sqlFill .= "	`".$DataBase."`.`tblticket`.`colStatus` AS `colStatus`, ";
 $sqlFill .= "	`".$DataBase."`.`tblticket`.`colPrio` AS `colPrio`, ";
 $sqlFill .= "	`".$DataBase."`.`tblticket`.`colTimestamp` AS `colTimestamp`, ";
 $sqlFill .= "	`".$DataBase."`.`tblticket`.`colAssignedObjects` AS `colAssignedObjects`, ";
 $sqlFill .= "	`".$DataBase."`.`tblticket`.`colOwner` AS `colOwner`, ";
 $sqlFill .= "	`".$DataBase."`.`tblticket`.`colAckTime` AS `colAckTime`, ";
 $sqlFill .= "	`".$DataBase."`.`tblticket`.`colStartdate` AS `colStartdate` ,";
 $sqlFill .= "	`".$DataBase."`.`tblticket`.`colKat` AS `colKat` ,";
 $sqlFill .= "	`".$DataBase."`.`tblimage`.`colTicketId` AS `colTicketId` ,";
 $sqlFill .= "	`".$DataBase."`.`tblimage`.`colImage` AS `colImage` ,";
 $sqlFill .= "	`".$DataBase."`.`tblimage`.`colid` AS `colid` ";
 $sqlFill .= "FROM ";
 $sqlFill .= "(`".$DataBase."`.`tblticket` ";
 $sqlFill .= "left JOIN ";
 $sqlFill .= "`".$DataBase."`.`tblimage` ";
 $sqlFill .= "ON ";
 $sqlFill .= "(`".$DataBase."`.`tblticket`.`ticketId` = `".$DataBase."`.`tblimage`.`colTicketId`))";

$sql = $sqlFill;

//this is default sql where and group values, If you dont have any, leave empty
$sqlWhere  ="WHERE NOT colStatus ='2' AND NOT colStatus ='4' AND colKat ='0'";
$sqlGroup  ="GROUP BY ticketId, colTicketId";


//Declarations
$filters = array();
$search = array();
$data =array();
$nestedData =array();
$limit ="";
$offset ="";
$nrRows="";
$sqlOrder ="";

//Json
$jsonUrl = array();
$sort ="";
$searchSqlString = "";
$searchOperator="";
$sqlLimitOffset ="";

//decoding json and handle limit and offset
if (isset($_GET["request"])){    
   $jsonUrl = json_decode($_GET["request"]);
   $sqlLimitOffset =" LIMIT ".$jsonUrl->limit." OFFSET ".$jsonUrl->offset;
}else{
   $sqlLimitOffset ="";
}

//Searching
if (!isset($jsonUrl->searchLogic)) {
      $searchLogic ="";
   }else{
      $searchLogic =$jsonUrl->searchLogic;
}

if (!isset($jsonUrl->search)) {  
       $searchSqlString ="";
   }else{

      //Handles the different search parameters   
      foreach ($jsonUrl->search as $item) {
                  switch ($item->operator) {
                     case 'is':
                        //have to check date format here, w2grid does it wrong even if you set it in the config.
                        if ($item->type="date") {
                           $item->value = str_replace(".", "-", $item->value);
                           $searchOperator =$DataBase .".".$tableName.".".$item->field." "."LIKE CONCAT('%', '".$item->value."', '%')";
                        }else{  
                           $searchOperator =$DataBase .".".$tableName.".".$item->field." = '". $item->value."'";
                        }   
                     break;
                     case 'begins':
                           $searchOperator =$DataBase .".".$tableName.".".$item->field." "."LIKE CONCAT('%', '".$item->value."', '%')";
                     break;
                     case 'contains':
                           $searchOperator =$DataBase .".".$tableName.".".$item->field." "."LIKE CONCAT('%', '".$item->value."', '%')";
                     break;
                     case 'ends':
                           $searchOperator =$DataBase .".".$tableName.".".$item->field." "."LIKE CONCAT('%', '".$item->value."')";
                     break;
                     case 'between':
                           $searchOperator ="(" . $DataBase .".".$tableName.".".$item->field . " >= '" . $item->value[0] . "' AND " . $DataBase .".".$tableName.".".$item->field." <= '" . $item->value[1] . "' )";
                     break;
                     case 'less':
                           $searchOperator =$DataBase .".".$tableName.".".$item->field." < '".$item->value."' ";
                     break;
                     case 'more':
                         $searchOperator =$DataBase .".".$tableName.".".$item->field." > '".$item->value."' ";
                     break;
                  }   
          $searchSqlString  .= $searchOperator ." ";
            $searchSqlString  .= " ".$searchLogic." ";  
   }
   $searchSqlString =delLastWord($searchSqlString);
   $sqlWhere .= $searchLogic." ".$searchSqlString;
}


//Function to delete the last AND, OR in $searchLogic from loop result
   function delLastWord(string $stringstr){
      $str= preg_replace('/\W\w+\s*(\W*)$/', '$1', $stringstr);
      return (string) $str;
   }

 //Function to delete the first AND, OR in $searchLogic from loop result
 function delFirstWord(string $stringstr){
   $str =preg_replace('/(\s*)([^\s]*)(.*)/', '$3', $stringstr);
   return (string) $str;
}  

  
//Column Sorting
if (!isset($jsonUrl->sort)) {
   $sort ="";
      }else{
         foreach ($jsonUrl->sort as $item) {
            $sort  .= $item->field." ";
            $sort  .= $item->direction." ";
         } 
         $sqlOrder =  "ORDER BY ". $sort;
}


//Connect DB
$conn = new mysqli($host, $user, $pass, $DataBase);
   if ($conn->connect_error) {
   die("Connection failed: " . $conn->connect_error);
} 

$conn ->set_charset('utf8');

//Clear up all where, and, or stuff
//Do it right from the beginning and we dont have to do this :)
if (strpos($sqlWhere, 'WHERE') !== true) {
   if (strpos($sqlWhere, 'AND') !== false) {
      $sqlWhere = delFirstWord($sqlWhere);
      $sqlWhere = "WHERE" .$sqlWhere;
   }
}

//The sql count clause, dont need limit and offset here
$sqlCount =  $sql ." ".$sqlWhere." ".$sqlGroup.";";

// Number of rows in the table 
   $result = $conn->query($sqlCount);
   $nrRows = mysqli_num_rows($result);

// Get data from DB
   $sql = $sql ." ".$sqlWhere." ".$sqlGroup." ".$sqlOrder." ".$sqlLimitOffset.";";
  
//echo "<br><br>".$sql."<br><br>";

$result = $conn->query($sql);

// Create the Json structure
      while( $row = mysqli_fetch_assoc($result) ) {

         $nestedData = array(
            $nestedData[] = 'recid' => $row["ticketId"], //Has to be named recid, i think..
            $nestedData[] = 'colAo' => $row["colAo"],
            $nestedData[] = 'colSb' => $row["colSb"],
            $nestedData[] = 'colStatus' => $row["colStatus"],
            $nestedData[] = 'colPrio' => $row["colPrio"],
            $nestedData[] = 'colAssignedObjects' => $row["colAssignedObjects"],
            $nestedData[] = 'colOwner' => $row["colOwner"],
            $nestedData[] = 'colAckTime' => $row["colAckTime"],
            $nestedData[] = 'colTimestamp' => $row["colTimestamp"]
                     );
        $data[] = $nestedData;
      }

//Close connection
$conn->close();

//Output Json
echo(json_encode(["total"=>$nrRows, "records"=>$data]));

?>
