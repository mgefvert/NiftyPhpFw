<?php 

// Used to find our 'app.blog' folder. If it was called just 'app', this line is unnecessary.
$niftyAppName = 'blog';

require '../../sys/init.php';

$Request->invoke();
$Response->send();