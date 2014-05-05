<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >

<?php
$db = new PDO('mysql:host=localhost;dbname=sakila;charset=utf8', 'root', '');

$sqlselectprefix=isset($_REQUEST['nocache'])?" SQL_NO_CACHE ":"";
if(strlen($sqlselectprefix)>0) echo "<b>executing with SQL_NO_CACHE</b><br/>";
$show=isset($_REQUEST['show'])?$_REQUEST['show']:-1;

$before = microtime(true);

if(isset($_REQUEST['fillSchedules'])) {

	$rooms=isset($_REQUEST['rooms'])?(1*$_REQUEST['rooms']):10;
	$with=isset($_REQUEST['with'])?$_REQUEST['with']:100;
	//$stmt = $db->exec($q='truncate room_schedule');
	print_r($stmt);
	$ord="order by rand()";
	$ord="";
	//$stmt = $db->exec
	($q='
insert into room_schedule (room_id, start, finish, film_id, language_id, initial_ticket_price, is_3d) (
select '.$sqlselectprefix.'
    r.id as room_id
    , (NOW()+INTERVAL f.film_id DAY) as start
    , DATE_ADD(CURDATE(),INTERVAL f.film_id DAY)+INTERVAL 5 HOUR as finish
    , f.film_id as film_id
    , 6 as language_id
    , 20 as initial_ticker_price
    , 0 as is_3d
from room r
    join
        (select id from room $ord limit '.(int)$rooms.') as temp
        using (id)
    left join
        (select film_id from film $ord limit '.(int)$with.') as f
        on 1=1
order by room_id, film_id
)');
	echo "<pre>";
	print_r($q);
	echo "</pre>";
	print_r($stmt);
	echo "filling with $with that many rooms $rooms, which give in total $rooms*$with rows";
} elseif(isset($_REQUEST['roomid']) && ($id=(int)$_REQUEST['roomid'])>0) {
	//if(try_again: cond_validate && updateDB) else if (!cond_validate && goto tryagain);
	$stmt = $db->query($q='SELECT * FROM room_schedule where room_id='.$id);
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	//ob_start();
	//echo '<pre>';
	//print_r($results);
	//$out=ob_get_clean();
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
	if(0)
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
		$add='SELECT '.$sqlselectprefix.' r.*
,(select count(*) from room_schedule as sch where r.id=sch.room_id ) as count
FROM room r';
		if($show>0) $add.=" limit ".$show;
	   //throw new Exception($add);
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
$after = microtime(true);
echo "insertion took ".(($after-$before)*1000)." ms = ".($after-$before). "us (10^-6s) \n";

//use $results