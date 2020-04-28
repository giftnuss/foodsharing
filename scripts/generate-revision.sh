#!/bin/sh
REV=$1
echo "<?php" > revision.inc.php
echo "define('SRC_REVISION', '$REV');" >> revision.inc.php
