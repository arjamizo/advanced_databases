<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >

<?php
$db = new PDO('mysql:host=localhost;dbname=sakila;charset=utf8', 'root', '');

if(isset($_REQUEST['roomid']) && ($id=(int)$_REQUEST['roomid'])>0) {
	//if(try_again: cond_validate && updateDB) else if (!cond_validate && goto tryagain);
	$stmt = $db->query($q='SELECT * FROM room_schedule where room_id='.$id);
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	ob_start();
	echo '<pre>';
	print_r($results);
	$out=ob_get_clean();
	//print $out;
	//print preg_replace('/(.*\[id\].*?)([0-9]+).*/', '${1}<a href="?scheduleid=${2}">${2}</a>', $out);
	echo "<table>";
	echo "<tr>";
	try {
		foreach ($results[0] as $kk => $vv) {
			echo "<td>$kk</td>";
		}
	} catch (Exception $e) {
	}
	echo "</tr>";
	foreach ($results as $k => $v) {
		echo "<tr>";
		foreach ($v as $kk => $vv) {
			echo "<td>$vv</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
} elseif (isset($_REQUEST['scheduleid']) && $id=(int)$_REQUEST['scheduleid']) {
	$stmt = $db->query($q='select av.*
-- ,sch.id as rid
-- ,ss.id as soldid
 , ss.id
 from available_seats av, sold_seat ss, room_schedule rsch
	-- left join room_schedule sch on sch.room_id=av.room_id
 --   left join sold_seat ss on ss.room_schedule_id=sch.id
-- where  
where 
av.room_id=rsch.room_id and ss.room_schedule_id=rsch.id -- polacz sold_seat z room schedule po room_id
and ss.available_seat_id=av.id
and rsch.id='.$id);
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if(0)
	foreach($results as $k  => $v) {
		print '<div style="position:absolute;top:'.($v['y']*45).';left:'.($v['x']*45).'">'
		.$v['id']
		.','
		.$v['seat_type_id']
		.'</div>'."\n";
	}
	else {
		echo '<pre>';
		echo $q;
		print_r($results);
	}
} else {
	$add='SELECT * FROM room ';
	if(isset($_REQUEST['getSchedulesCount'])) {
		$add='SELECT r.*,count(room_schedule.id) FROM room r
       left join room_schedule as sch on r.id=sch.room_id';
	   throw new Exception($add);
	}
	$stmt = $db->query($add);
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	ob_start();
	echo '<pre>';
	print_r($results);
	$out=ob_get_clean();
	//print $out;
	//print preg_replace('/(.*\[id\].*?)([0-9]+).*/', '${1}<a href="?roomid=${2}">${2}</a>', $out);
	echo "<table>";	
	echo "<tr>";
	foreach ($results[0] as $kk => $vv) {
		echo "<td>$kk</td>";
	}
	echo "<td><a href='?getSchedulesCount'>get count</a></td>";
	echo "</tr>";
	foreach ($results as $k => $v) {
		echo "<tr>";
		foreach ($v as $kk => $vv) {
			echo "<td>$vv</td>";
		}
		echo "<td><a href='?roomid=".$v['id']."'>see this room</a></td>";
		echo "</tr>";
	}
	echo "</table>";
}

//use $results