<?php 
	$data_file = fopen('data.html', 'r');
	$result = '';
	$callback = $_GET['callback'];

	while (!feof($data_file)) {
	   $result .= fgets($data_file);
	}
	fclose($data_file);

	$data = array( 'data' => $result, 'count' => time()%100 );
	echo $callback.'('.json_encode($data).')';
?>