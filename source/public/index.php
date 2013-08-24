<?php

require '../sys/init.php';

NF::request()->invoke();
NF::response()->send();
