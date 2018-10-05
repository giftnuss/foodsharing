<?php
$func = '';

if (isset($_POST['action']) && $_POST['action'] == 'upload') {
	session_start();

	if (isset($_FILES['uploadpic'])) {
		$error = 0;
		$datei = $_FILES['uploadpic']['tmp_name'];
		$datein = $_FILES['uploadpic']['name'];
		$datein = strtolower($datein);
		$datein = str_replace('.jpeg', '.jpg', $datein);
		$dateiendung = strtolower(substr($datein, strlen($datein) - 4, 4));
		if (is_allowed($_FILES['uploadpic'])) {
			$file = str_replace('/', '', $_SESSION['upload_name'] . $dateiendung);
			move_uploaded_file($datei, '../tmp/' . $file);

			$image = new fImage('../tmp/' . $file);
			$image->resize(550, 0);
			$image->saveChanges();

			$func = 'parent.fotoupload(\'' . $file . '\');';
		} else {
			$func = 'parent.pic_error(\'Deine Datei schein nicht in Ordnung zu sein, nimm am besten ein normales jpg Bild\');';
		}
	}
} elseif (isset($_POST['action']) && $_POST['action'] == 'crop') {
	$file = str_replace('/', '', $_POST['file']);
	if ($img = cropImage($file, $_POST['x'], $_POST['y'], $_POST['w'], $_POST['h'])) {
		$func = 'parent.picFinish(\'' . $img . '\');';
	} else {
		$func = 'alert(\'Es ist ein Fehler aufgetreten, Sorry, probiers nochmal\');';
	}
}

function cropImage($bild, $x, $y, $w, $h)
{
	$targ_w = 467;
	$targ_h = 600;
	$jpeg_quality = 100;

	$ext = explode('.', $bild);
	$ext = end($ext);
	$ext = strtolower($ext);
	switch ($ext) {
		case 'gif':
			$img_r = imagecreatefromgif('../tmp/' . $bild);
			break;
		case 'jpg':
			$img_r = imagecreatefromjpeg('../tmp/' . $bild);
			break;
		case 'png':
			$img_r = imagecreatefrompng('../tmp/' . $bild);
			break;
	}

	$dst_r = imagecreatetruecolor($targ_w, $targ_h);

	imagecopyresampled($dst_r, $img_r, 0, 0, $x, $y, $targ_w, $targ_h, $w, $h);

	@unlink('../tmp/crop_' . $bild);

	switch ($ext) {
		case 'gif':
			imagegif($dst_r, '../tmp/crop_' . $bild);
			break;
		case 'jpg':
			imagejpeg($dst_r, '../tmp/crop_' . $bild, $jpeg_quality);
			break;
		case 'png':
			imagepng($dst_r, '../tmp/crop_' . $bild, 0);
			break;
	}
	if (file_exists('../tmp/crop_' . $bild)) {
		copy('../tmp/crop_' . $bild, '../tmp/thumb_crop_' . $bild);

		$image = new fImage('../tmp/thumb_crop_' . $bild);
		$image->resize(150, 0);
		$image->saveChanges();

		return 'thumb_crop_' . $bild;
	}

	return false;
}

function is_allowed($img)
{
	$img['name'] = strtolower($img['name']);
	$img['type'] = strtolower($img['type']);

	$allowed = array('jpg' => true, 'jpeg' => true, 'png' => true, 'gif' => true);

	$filename = $img['name'];
	$parts = explode('.', $filename);
	$ext = end($parts);

	$allowed_mime = array('image/gif' => true, 'image/jpeg' => true, 'image/png' => true);

	if (!isset($allowed[$ext])) {
		return false;
	}

	if (isset($allowed_mime[$img['type']])) {
		return true;
	}

	return false;
}

?>
<html>
<head><title>Upload</title></head>
<body onload="<?php echo $func; ?>"></body>
</html>
