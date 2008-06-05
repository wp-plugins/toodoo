<?php

    $url = "http://toodoo.ru/ajax/snap/".$_GET['url'];

    if (function_exists('curl_init')) {
      	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
    	curl_setopt($ch, CURLOPT_TIMEOUT, 5 );
    	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

    	$content = curl_exec( $ch );

    	curl_close($ch);
    }
    else {
        $content = file_get_contents($url);
    }
	echo $content;

?>
