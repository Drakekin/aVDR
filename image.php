<?php
	// Import copymerge function
	require 'copymerge.func.php';

	// Load variables from URL
	$user = alphanumericAndSpace($_GET['user']);
	$check = alphanumericAndSpace($_GET['time']);

	// Check if the image has already been generated
	if (file_exists($user . "-" . $check . ".png")) {
		header("Location: http://" . $_SERVER['SERVER_NAME'] . "/" . $user . "-" . $check . ".png");
	}

	// Connect to the rolls database
	$conn = mysql_connect("<<host>>", "<<database>>", "<<password>>") or die("Can't connect to DB");
	mysql_select_db("lifeind1_avdr", $conn) or die("Can't select DB" . mysql_error());

	// Load the filenames of the two dice
	$dice1 = 'die1.png';
	$dice2 = 'die2.png';

	// Search the database for a roll by the specified user with the ID $check
	$query = mysql_query("SELECT * FROM rolls WHERE uid='" . $user . "' AND rid='" . $check . "'") or die(mysql_error());

	// Make sure the database responded
	if ($query != false) {
		// Fetch the data sent
		$row = mysql_fetch_assoc($query);

		// Extract the dice rolled
		$request = $row['request'];
		$dice = explode("d", $request);

		// Fetch and extract the numbers rolled
		$rawresult = $row['result'];
		$result = explode(" ", $rawresult);
		array_walk($result, 'trimit');
		//var_dump($result);

		// Set width and height variables
		$width = 0;
		$realwidth = 0;
		$height = 45;

		// Set the width and height so you get rows of 4 dice
		$nodie = $dice[0];
		while ($nodie != 0) {
			$nodie--;
			if ($width < 160) {
				$width = $width + 45;
			} else {
				$height = $height + 45;
				$realwidth = $width;
				$width = 45;
			}
		}
		if ($realwidth != 0) {
			$width = $realwidth;
		}

		// Create an image with the specified dimensions
		$img = imagecreatetruecolor($width, $height);
		imagealphablending($img, true);
		$colourBlack = imageColorAllocate($img, 0, 0, 0);
		$colourWhite = imageColorAllocate($img, 255, 255, 255);
		imageFilledRectangle($img, 0, 0, $width - 1, $height - 1, $colourWhite);
		
		// Reset $nodie width and height
		$nodie = $dice[0];
		$imgw = $width;
		$imgh = $height;
		$width = 0;
		$height = 0;
		$dieno = 0;

		// Walk through $nodie, copying enough die images into the image
		while ($nodie != 0) {
			$nodie--;
			if ($row['target'] != "" and $row['overtarget'] != "") {
				if ($result[2 + $dieno] >= $row['target']) {
					$file = $dice1;
				} else {
					$file = $dice2;
				}
			} else {
				$file = $dice2;
			}

			if ($result[2 + $dieno] == $row['highest'] or $result[2 + $dieno] == $row['lowest']) {
				$file = $dice1;
			}
			
			// Open the image
			$die = imagecreatefrompng($file);
			imagealphablending($die, true);

			// Copy the die into the image
			imagecopymerge($img, $die, $width, $height, 0, 0, 45, 45, 100);

			// Write the number onto the die
			if (strlen($result[2 + $dieno]) == 1) {
				imageString($img, 5, $width + 17, $height + 13, $result[2 + $dieno], $colourBlack);
			} else if (strlen($result[2 + $dieno]) == 2) {
				imageString($img, 5, $width + 14, $height + 13, $result[2 + $dieno], $colourBlack);
			} else {
				imageString($img, 5, $width + 11, $height + 13, $result[2 + $dieno], $colourBlack);
			}
			//echo $result[3 + $dieno];
			$dieno++;

			// Move the pointer along
			$width = $width + 45;
			if ($width >= 180) {
				$width = 0;
				$height = $height + 45;
			}
			imagedestroy($die);
		}

		// Ensure transparency is conserved
		imagesavealpha($img, true);

		// Set the file type to PNG image
		header('Content-Type: image/png;');

		// Export image as a png
		imagepng($img, $user . "-" . $check . ".png");
		header("Location: http://" . $_SERVER['SERVER_NAME'] . "/" . $user . "-" . $check . ".png");
		imagedestroy($img);
	}
?>
