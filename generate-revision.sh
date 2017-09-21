#!/bin/sh
REV=$((git rev-list HEAD --max-count=1))
echo "<?php" > revision.inc.php
echo "define('SRC_REVISION', '"$REV"');" >> revision.inc.php
