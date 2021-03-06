<?php
require_once "config.php";
$json_arr = array();
if(!array_key_exists('q', $_GET)) {
	$json_arr['error'] = 'no param';
	echo json_encode($json_arr);
	exit();
}
$q = $_GET['q'];

$con = mysqli_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PASS, 'stromzaehler');
if(mysqli_connect_errno()) {
	$json_arr['error'] = mysqli_error();
	echo json_encode($json_arr);
	exit;
}

switch($q) {
	case 'addCounter':
		if(array_key_exists('name', $_POST) && array_key_exists('mode', $_POST)) {
			$name = trim($_POST['name']);
			$mode = $_POST['mode'];
			$active = 'activeLieferung';
			if($mode == 'Bezug') $active = 'activeBezug';
			mysqli_query($con, "UPDATE zaehler SET ".$active." = false;");
			mysqli_query($con, "INSERT INTO zaehler (`name`, `offset`, `".$active."`) VALUES ('".$name."', '".$offset."', true) ");
			exit;
		}
		break;
	case 'yearly':
		$data = array();
		$preyear_value = 0;
		$res = mysqli_query($con, "
SELECT DATE_FORMAT(`time`, '%Y') as `year`, `offset`, zaehlerstand FROM 
(
	SELECT MAX(`time`) as maxTime
	FROM `leistung` as l
	GROUP BY DATE_FORMAT(`time`, '%Y') 
) AS x
INNER JOIN
(
		SELECT `time`, `offset`, leistung as zaehlerstand
			FROM leistung AS l
				INNER JOIN zaehler as z on z.ID = l.zaehlerid
			) AS y ON y.time = x.maxTime ORDER BY `time` ASC;
");
		while($row = mysqli_fetch_array($res)) {
			$db_year = $row['year'];
			$value = $row['zaehlerstand'] - $preyear_value + $row['offset'];
			//echo "Zaehlerstand: ".$row['zaehlerstand']."-".$preyear_value."+".$row['offset']."=".$value."<br/>";
			$data['labels'][] = $db_year;
			$data['series'][] = round($value);
			$preyear_value = $row['zaehlerstand']+$row['offset'];
		}
		echo json_encode($data);
		exit;
	break;
	case 'yearlybezug':
		$data = array();
		$preyear_value = 0;
		$res = mysqli_query($con, "
SELECT DATE_FORMAT(`time`, '%Y') as `year`, `offset`, zaehlerstand FROM 
(
	SELECT MAX(`time`) as maxTime
	FROM leistung_bezug as l
	GROUP BY DATE_FORMAT(`time`, '%Y') 
) AS x
INNER JOIN
(
		SELECT `time`, `offset`, leistung as zaehlerstand
			FROM leistung_bezug AS l
				INNER JOIN zaehler as z on z.ID = l.zaehlerid
			) AS y ON y.time = x.maxTime ORDER BY `time` ASC;
");
		while($row = mysqli_fetch_array($res)) {
			$db_year = $row['year'];
			$value = $row['zaehlerstand'] - $preyear_value + $row['offset'];
			//echo "Zaehlerstand: ".$row['zaehlerstand']."-".$preyear_value."+".$row['offset']."=".$value."<br/>";
			$data['labels'][] = $db_year;
			$data['series'][] = round($value);
			$preyear_value = $row['zaehlerstand']+$row['offset'];
		}
		echo json_encode($data);
		exit();
	break;
	case 'year': 
		if(!array_key_exists('y', $_GET)) {
			
			$years = array();
			$res = mysqli_query($con, "SELECT DISTINCT(YEAR(`time`)) as `year` FROM leistung ORDER by `time` ASC;");
			while($row = mysqli_fetch_array($res)) $years[] = $row['year'];
			$datas = array();
			$value_lastmonth = 0;
			foreach ($years as $year) {
				$data = array();
				for($i = 1; $i < 13; $i++) {
					$sql = "SELECT DATE_FORMAT(`time`, '%M') as month, leistung, `offset` FROM `leistung`, `zaehler` WHERE DATE_FORMAT(`time`, '%Y-%m') = '".$year."-".($i<10 ? "0".$i : $i)."' AND zaehlerid = ID ORDER BY `time` DESC LIMIT 1;";
					$res = mysqli_query($con, $sql);
					$row = mysqli_fetch_array($res);
					if(count($row) == 0) continue;
					$month = $row['month'];
					$value = $row['leistung'] + $row['offset'] - $value_lastmonth;
					$value_lastmonth = $row['leistung'] + $row['offset'];
					$data['labels'][] = $month;
					$data['series'][] = round($value);
				}
					$datas[] = array('year' => $year, 'data' => $data);
			}
				echo json_encode($datas);
				exit;
			
		}
		$year = $_GET['y'];
		if($year < 2008 || $year > date('Y')) {
			$json_arr['error'] = 'year too small or big';
			break;
		}
		
		$res = mysqli_query($con, "SELECT DATE_FORMAT(`time`, '%M') as `month`, leistung, `offset` FROM `leistung`, `zaehler` WHERE DATE_FORMAT(`time`, '%Y-%m-%d') = LAST_DAY('". ($year-1) ."-12-01') AND zaehlerid = ID ORDER BY `time` DESC LIMIT 1; ");
		$row = mysqli_fetch_array($res);

		$value_lastmonth = $row['leistung'] + $row['offset'];
		
		$data = array();
		for($i = 1; $i < 13; $i++) {
			$sql = "SELECT DATE_FORMAT(`time`, '%M') as month, leistung, `offset` FROM `leistung`, `zaehler` WHERE DATE_FORMAT(`time`, '%Y-%m') = '".$year."-".($i<10 ? "0".$i : $i)."' AND zaehlerid = ID ORDER BY `time` DESC LIMIT 1;";
			$res = mysqli_query($con, $sql);
			$row = mysqli_fetch_array($res);
			if(count($row) == 0) continue;
			$month = $row['month'];
			$value = $row['leistung'] + $row['offset'] - $value_lastmonth;
			$value_lastmonth = $row['leistung'] + $row['offset'];
			$data['labels'][] = $month;
			$data['series'][] = round($value);
		}
		echo json_encode($data);
		exit;
        break;
        case 'yearall':
                if(!array_key_exists('y', $_GET)) {
                  $json_arr['error'] = 'missing param y';
                  break;
                }
                $data = array();
                $year = $_GET['y'];
                $res = mysqli_query($con, "SELECT DISTINCT(DATE_FORMAT(time, \"%M\")) as month FROM leistung WHERE YEAR(time) = '".$year."' ORDER BY time ASC;");
                while($row = mysqli_fetch_array($res)) {
                  $data['labels'][] = $row['month'];
                }

                $sql = "SELECT DATE_FORMAT(time, \"%M\") as `month`, DATE_FORMAT(`time`, \"%Y-%m-%d\") as `time`, `leistung`, `offset` FROM `leistung`, `zaehler` WHERE `leistung`.`zaehlerid` = `zaehler`.`ID` AND YEAR(`time`) = '".$year."' ORDER BY `time` ASC;";
                //echo $sql;
                  
                $res = mysqli_query($con, $sql);
                $oldmonth = "";
                $i = 0;
                $last_leistung = -1;
                while($row = mysqli_fetch_array($res)) {
                  if($last_leistung == -1) {
                    $sql = "SELECT leistung, `offset` FROM leistung, zaehler WHERE zaehlerid = ID AND date_format(time, \"%Y-%m-%d\") = DATE_SUB('".$row['time']."', INTERVAL 1 DAY);";
                    //echo $sql;
                    $res2 = mysqli_query($con, $sql);
                    if(mysqli_num_rows($res2) > 0) {
                      $row2 = mysqli_fetch_array($res2);
                      $last_leistung = $row2['leistung'] + $row2['offset'];
                    } else $last_leistung = 0;
                  }  
                  if($oldmonth != $row['month']) {
                   $i++;
                  }
                  $leistung = $row['leistung'] + $row['offset'];
                  $data['series'][$i][] = $leistung-$last_leistung;
                  $oldmonth = $row['month'];
                  $last_leistung = $leistung;
                }   
                echo json_encode($data);
                exit;
        break;
        case 'yl':
                $data = array();
                $res = mysqli_query($con, "SELECT DISTINCT(YEAR(`time`)) as year FROM leistung WHERE DATE_FORMAT(`time`, '%m-%d') = '01-01' OR DATE_FORMAT(`time`, '%m-%d') = '12-31' ORDER BY `time` ASC;");
                while($row = mysqli_fetch_array($res)) {
                  $data['series'][] = $row['year'];
                }
                echo json_encode($data);
                exit;
        break;
        case 'yb':
                $data = array();
                $res = mysqli_query($con, "SELECT DISTINCT(YEAR(`time`)) as year FROM leistung_bezug WHERE DATE_FORMAT(`time`, '%m-%d') = '01-01' OR DATE_FORMAT(`time`, '%m-%d') = '12-31' ORDER BY `time` ASC;");
                while($row = mysqli_fetch_array($res)) {
                  $data['series'][] = $row['year'];
                }
                echo json_encode($data);
                exit;
        break;
        case 'c':
	
		$data = array();
		$data[] = array("zaehlerid" => 1, "leistung" => exec("python ../python/aktlieferung.py"));
		$data[] = array("zaehlerid" => 0, "leistung" => exec("python ../python/aktbezug.py"));
		echo json_encode($data);
		exit;
        break;
	case 'w':
		$data = array();
		$res = mysqli_query($con, "SELECT DATE_FORMAT(`time`, '%W') as `weekday`, leistung, `offset` FROM leistung, zaehler WHERE zaehlerid = ID ORDER BY `time` DESC LIMIT 8;");
		$revdata = array();
		while($row = mysqli_fetch_array($res)) {
			$revdata[] = array('weekday' => $row['weekday'], 'leistung' => $row['leistung'], 'offset' => $row['offset']);
		}
		$revdata = array_reverse($revdata);
		$value_before = 0; 
		$firstday = true;
		foreach($revdata as $row) {
			$weekday = $row['weekday'];
			$value = $row['leistung'] - $value_before + $row['offset'];
			$value_before = $row['leistung'] + $row['offset'];
			if($firstday) {
				$firstday = false;
				continue;
			}
			//echo "vb " . $value_before . " + val " . $value . " + wd " . $weekday . "<br/>";
			//data fill
			$data['labels'][] = $weekday;
			$data['series'][] = $value;
		}
		echo json_encode($data);
		exit;
	break;
	default: 
		$json_arr['error'] = 'wrong parameter';
}

if($json_arr['error'] != null) {
	echo json_encode($json_arr);
	exit;
}

mysqli_close($con);
exit();
?>
