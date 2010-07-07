<?php
	// Set start time
	$starttime = microtime();

	// uncomment to break
	// echo $_SERVER['argv'][0];

	// Set the spam timeout
	$timeout = 30;

	// Connect to the rolls database
	$conn = mysql_connect("localhost", "lifeind1_avdr", "mcitf") or die("Can't connect to DB");
	mysql_select_db("lifeind1_avdr", $conn) or die("Can't select DB" . mysql_error());
	
	// Start the RNG
	require 'RandDotOrg.class.php';
	require 'copymerge.func.php';
	$rand_org = new RandDotOrg('automagicVerifiedDiceRoller- ' . $_SERVER['SERVER_NAME'] . ' - drake@lifein2d.co.uk (Using the phpRandDotOrg library)');
	
	// Load variables from the URL
	$check = $_GET['time'];
	$request = $_GET['roll'];
	$user = $_GET['user'];
	$browse = $_GET['browse'];
	$browse = $browse . ",";
	$offset = $_GET['offset'];
	$background = $_GET['old'];
	$image = $_GET['image'];
	if ($_GET['usetarget']) {
		$target = $_GET['target'];
	}
	$browseall = $_GET['browseall'];
	$findhighest = $_GET['usepickhighest'];
	$findcombo = $_GET['usepickhighestdoubles'];
	$findlowest = $_GET['usepicklowest'];
	$dorerollabove = $_GET['usererollabove'];
	$rerollabove = $_GET['rerollabove'];
	$dorerollbelow = $_GET['usererollbelow'];
	$rerollbelow = $_GET['rerollbelow'];
	$usemodifier = $_GET['usemodifier'];
	$modifier = $_GET['modifier'];
	
	// Set the version
	$version = "1.0";

	// Sanitise the variables
	$rerollabove = alphanumericAndSpace($rerollabove);
	$rerollbelow = alphanumericAndSpace($rerollbelow);
	$check = alphanumericAndSpace($check);
	$request = alphanumericAndSpace($request);
	$user = alphanumericAndSpace($user);
	$background = alphanumericAndSpace($background);
	if ($_GET['usetarget']) {
		$target = alphanumericAndSpace($target);
	}
	$offset = alphanumericAndSpace($offset);
	$browse = explode(",", $browse);
	foreach ($browse as &$names) {
		$names = alphanumericAndSpace($names);
	}
	$browseall = alphanumericAndSpace($browseall);
	if ($offset == "") {
		$offset = 0;
	}

	// Checking the quota
	$rawquota = $rand_org->quota();
	$quota = $rawquota[0];

	// Allow for debugging
	if ($_GET['debug']) {
		$quota = 0;
	}
	$maxnoofdie = floor($quota / 8);
	$useinternal = false;
	
	// Make sure that we can get a new random number
	if ($quota < 1) {
		$useinternal = true;
	}

	// If we've used our quota, fall back to PHP's mt_rand()
	function roll($x, $y, $z) {
		while ($x != 0) {
			$array[] = mt_rand($y, $z);
			$x--;
		}
		return $array;
	}
	
	// Check to see if the user is checking and old roll or making a new one
	if ($check == "" and $request != "") {
		// Making a new roll
		// Make sure they're not spamming.
		$spamsql = "SELECT rid FROM rolls WHERE uid='" . $user . "' ORDER BY rid DESC LIMIT 1";
		$spamresult = mysql_query($spamsql) or die(mysql_error());
		if (mysql_num_rows($spamresult) > 0) {
			$spamrow = mysql_fetch_assoc($spamresult);
			if (($_SERVER['REQUEST_TIME'] - $spamrow['rid']) < $timeout) {
				$message = "<p><strong class='big'>You are trying to send too many requests.</strong><br />Please wait " . $timeout . " seconds between rolls.<br /><span class='small'>You waited " . ($_SERVER['REQUEST_TIME'] - $spamrow['rid']) . " seconds, only " . ($timeout - ($_SERVER['REQUEST_TIME'] - $spamrow['rid'])) . " seconds left.<br /> Your browser should refrsh when you have waited long enough.</span></p>";
				header("Refresh: " . (2 + ($timeout - ($_SERVER['REQUEST_TIME'] - $spamrow['rid']))));
				require 'results.php';
				die();
			}
		}
		
		// Explode the request into number and type of dice
		$dice = explode("d", $request);
		if ($dice[0] > 20) {
			$dice[0] = 20;
		}
		if ($dice[1] > 100) {
			$dice[1] = 100;
		}
		$request = $dice[0] . "d" . $dice[1];
		
		// Fetch the dice rolls from random.org
		if ($useinternal) {
			$rolls = roll($dice[0], 1, $dice[1]);
		} else {
			$rolls = $rand_org->get_integers($dice[0], 1, $dice[1]);
		}
		
		// Parse through the results
		if ($usemodifier) {
			if ($modifier > 0) {
				$result = $request . "+" . $modifier . " - ";
			} else {
				$result = $request . $modifier . " - ";
			}
		} else {
			$result = $request . " - ";
		}
		$first = true;
		$highest = 0;
		$lowest = 0;
		$highestcombo;
		if ($_GET['usetarget']) {
			$overtarget = 0;
		}
		foreach ($rolls as &$roll) {
			if ($usemodifier) {
				$roll = $roll + $modifier;
			}
			if ($dorerollabove) {
				if ($roll > $rerollabove) {
					$temp = roll(1, 1, $dice[1]);
					$roll = $temp[0];
					if ($usemodifier) {
						$roll = $roll + $modifier;
					}
				}
			}
			if ($dorerollbelow) {
				if ($roll > $rerollbelow) {
					$temp = roll(1, 1, $dice[1]);
					$roll = $temp[0];
					if ($usemodifier) {
						$roll = $roll + $modifier;
					}
				}
			}
			if (!$first) {
				$result = $result . ", ";
			} else {
				if ($findlowest) {
					$lowest = $roll;
				}
			}
			if ($findlowest) {
				if ($roll < $lowest) {
					$lowest = $roll;
				}
			}
			if ($findhighest) {
				if ($roll > $highest) {
					$highest = $roll;
				}
			}
			if ($findcombo) {
				$highestcombo[$roll] = $highestcombo[$roll] + $roll;
			}
			$first = false;
			$result = $result . $roll;
			if ($_GET['usetarget']) {
				if ($roll >= $target) {
					$overtarget++;
				}
			}
		}
		if ($findcombo) {
			$highestcombo[1] = 0;
			$highestdie = 0;
			$highestdiecombo = 0;
			foreach ($highestcombo as $key=>$value) {
				if ($value > $highestdiecombo) {
					$highestdiecombo = $value;
					$highestdie = $key;
				}
			}
		}
				
		// Add the roll to the database
		if ($_GET['usetarget']) {
			$sql_extra  = " ,`target` ,`overtarget`";
			$sql_extra2 = ", '" . $target . "', '" . $overtarget . "'";
		} else {
			$sql_extra = "";
			$sql_extra2 = "";
		}
		if ($findhighest) {
			$sql_extra  .= " ,`highest`";
			$sql_extra2 .= ", '" . $highest . "'";
		} else if ($findcombo) {
			$sql_extra  .= " ,`highest`";
			$sql_extra2 .= ", '" . $highestdie . "'";
			$sql_extra  .= " ,`highestdoubles`";
			$sql_extra2 .= ", '" . $highestdiecombo . "'";
		}
		if ($findlowest) {
			$sql_extra  .= " ,`lowest`";
			$sql_extra2 .= ", '" . $lowest . "'";
		}
		
		$query = mysql_query("INSERT INTO `rolls` (`uid` ,`rid` ,`result` ,`request`" . $sql_extra . ") VALUES ('" .  $user. "',  '" . $_SERVER['REQUEST_TIME'] . "',  '" . $result . "', '" . $request . "'" . $sql_extra2 . ")");
		if ($query == false) {
			die("Couldn't add roll to database " . mysql_error());
		}
		
		// Forward back to the page to display the roll
		header("Location: http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . "?time=" . $_SERVER['REQUEST_TIME'] . "&user=" . $user);
		
	} else if ($check != "") {
		// Checking an old roll
		// Check to see if the user wants an image
		if ($image) {
			header("Location: http://" . $_SERVER['SERVER_NAME'] . "image.php?time=" . $_SERVER['REQUEST_TIME'] . "&user=" . $user);
		}
		
		// Search the database for a roll by the specified user with the ID $check
		$query = mysql_query("SELECT * FROM rolls WHERE uid='" . $user . "' AND rid='" . $check . "'");

		// Make sure the database responded
		if ($query != false) {
			$row = mysql_fetch_assoc($query);

			// Print the result
			$message = "<p class='big'>";
			if ($row['target'] != "" and $row['overtarget'] != "") {
				if ($row['overtarget'] == 1) {
					$extras = "";
				} else {
					$extras = "s";
				}
				$message .= "You (" . $row['uid'] . ") rolled " . " <strong>" . $row['result'] . "</strong> on " . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . " with " . $row['overtarget'] . " roll" . $extras . " over " . $row['target'] . ".";
			} else if ($row['highestdoubles'] != "") {
				$message .= "You (" . $row['uid'] . ") rolled " . " <strong>" . $row['result'] . "</strong> on " . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . " and the highest combo was " . $row['highestdoubles'] . " with " . $row['highest'] . "s.";
			} else if ($row['highest'] != "") {
				$message .= "You (" . $row['uid'] . ") rolled " . " <strong>" . $row['result'] . "</strong> on " . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . " with a highest of " . $row['highest'] . ".";
			} else if ($row['lowest'] != "") {
				$message .= "You (" . $row['uid'] . ") rolled " . " <strong>" . $row['result'] . "</strong> on " . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . " with a lowest of " . $row['lowest'] . ".";
			} else {
				$message .= "You (" . $row['uid'] . ") rolled " . " <strong>" . $row['result'] . "</strong> on " . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . ".";
			}
			$message .= "</p>";

			// Print BBCode
			$message .= "<p>To use this roll on a forum, use one of the following BBCodes:</p>";
			$message .= "<p>For forums that support the [dice] tag (ask an admin):<br /><code>[dice=" . $row['uid'] . "]" . $row['rid'] . "[/dice]</code><br /><br />";
			$message .= "For any other forum:<br /><code>[url=http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . "]<br />[img]http://" . $_SERVER['SERVER_NAME'] . "/image.php?" . $_SERVER['argv'][0] . "[/img][/url]</code><br /><br />";
			$message .= "This produces the following:<br />";
			$message .= "<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . "\"><img src=\"http://" . $_SERVER['SERVER_NAME'] . "/image.php?" . $_SERVER['argv'][0] . "\" alt=\"aVDR Dice Roller\" /></a>";

			// Store the time
			$time = $row['rid'];

			// Get number of hours to display old dice rolls and set it
			if (!is_numeric($background)) {
				$background = 8;
			}
			$time2 = $time - 60*60*$background;

			// If $background is 1, don't add an 's' to hours
			if ($background != 1 ) {
				$adds2 = "s";
			} else {
				$adds2 = "";
			}

			// Search the database for any rolls in the past eight hours
			$query2 = mysql_query("SELECT * FROM rolls WHERE uid='" . $user . "' AND rid<'" . $time . "' AND rid>'" . ($time2) . "' ORDER BY rid DESC");

			// Make sure the database has sent data
			if ($query2 != false) {
				// Check the number of rolls it has sent
				$numrolls = mysql_num_rows($query2);

				// If the number of rolls is 1, don't put an 's' on rolls
				if ($numrolls != 1 ) {
					$adds = "s";
				} else {
					$adds = "";
				}

				// Print the results
				$message .= "<p>Your last " . $numrolls . " roll" . $adds . " in the past " . $background . " hour" . $adds2 . "</p>";
				$message .= "<p class='medium'>";

				// Walk through the results
				while ($row = mysql_fetch_assoc($query2)) {
					if ($row['target'] != "" and $row['overtarget'] != "") {
						if ($row['overtarget'] == 1) {
							$extras = "";
						} else {
							$extras = "s";
						}
						$message .= $row['uid'] . " rolled " . " <strong>" . $row['result'] . "</strong> on " . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . " with " . $row['overtarget'] . " roll" . $extras . " over " . $row['target'] . ".<br />";
					} else if ($row['highestdoubles'] != "") {
						$message .= $row['uid'] . " rolled " . " <strong>" . $row['result'] . "</strong> on " . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . " and the highest combo was " . $row['highestdoubles'] . " with " . $row['highest'] . "s.<br />";
					} else if ($row['highest'] != "") {
						$message .= $row['uid'] . " rolled " . " <strong>" . $row['result'] . "</strong> on " . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . " with a highest of " . $row['highest'] . ".<br />";
					} else if ($row['lowest'] != "") {
						$message .= $row['uid'] . " rolled " . " <strong>" . $row['result'] . "</strong> on " . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . " with a lowest of " . $row['lowest'] . ".<br />";
					} else {
						$message .= $row['uid'] . " rolled " . " <strong>" . $row['result'] . "</strong> on " . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . ".<br />";
					}
				}
				$message .= "</p>";
			} else {
				// If no results are found, print an error
				$message = "<p>No other rolls by this user</p>";
			}
		} else {
			// If no result is found, print an error
			$messgae = "<p>No die roll found</p>";
		}
		// Print the link and brought to you message.
		require 'results.php';
	} else if ($browse[0] != "" or $browseall) {
		$sql = "SELECT * FROM rolls WHERE ";
		$first = true;
		$names = $browse;
		foreach ($browse as $name) {
			if (!$first) {
				$sql .= "OR ";
			}
			$sql .= "uid='" . $name . "' ";
			$first = false;
		}

		if ($browseall) {
			$sql = "SELECT * FROM rolls";
		}
		
		$sql2 = $sql;
		$sql .= " ORDER BY rid DESC LIMIT 10 OFFSET " . $offset;
		
		$result = mysql_query($sql) or die(mysql_error());
		$result2 = mysql_query($sql2);
		$totalresults = mysql_num_rows($result2);
		
		$message = "<p><strong class='big'>Roll Browser</strong><br />";
		$message .= "<span class='medium'>Browsing ";
		if ($browseall) {
			$message .= "all";
		} else {
			$first = true;
			foreach ($names as $uid) {
				$test = "";
				$test .= $uid;
				if ($test != "Array") {
					if (!$first) {
						$message .= ", ";
					}
					$message .= $uid;
					$first = false;
				}
			}
		}
		$message .= "<br />";
		$upperlimit = $offset + mysql_num_rows($result);
		$message .= "Showing records " . $offset . " to " . $upperlimit . " of " . $totalresults . "</span></p>";
		if ($offset != 0) {
			$message .= "<p><a href=\"?browse=" . $_GET['browse'] . "&offset=" . ($offset - 10) . "&browseall=" . $browseall . "\">Previous 10 Records</a></p>";
		}
		$message .= "<p>";
		while ($row = mysql_fetch_assoc($result)) {
			if ($row['target'] != "" and $row['overtarget'] != "") {
				if ($row['overtarget'] == 1) {
					$extras = "";
				} else {
					$extras = "s";
				}
				$message .= $row['uid'] . " rolled " . " <strong>" . $row['result'] . "</strong> on <a href=\"?time=" . $row['rid'] . "&user=" . $row['uid'] . "\">" . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . "</a> with " . $row['overtarget'] . " roll" . $extras . " over " . $row['target'] . ".<br />";
			} else if ($row['highestdoubles'] != "") {
						$message .= $row['uid'] . " rolled " . " <strong>" . $row['result'] . "</strong> on <a href=\"?time=" . $row['rid'] . "&user=" . $row['uid'] . "\">" . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . "</a> and the highest combo was " . $row['highestdoubles'] . " with " . $row['highest'] . "s.<br />";
					} else if ($row['highest'] != "") {
						$message .= $row['uid'] . " rolled " . " <strong>" . $row['result'] . "</strong> on <a href=\"?time=" . $row['rid'] . "&user=" . $row['uid'] . "\">" . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . "</a> with a highest of " . $row['highest'] . ".<br />";
					} else if ($row['lowest'] != "") {
						$message .= $row['uid'] . " rolled " . " <strong>" . $row['result'] . "</strong> on <a href=\"?time=" . $row['rid'] . "&user=" . $row['uid'] . "\">" . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . "</a> with a lowest of " . $row['lowest'] . ".<br />";
					} else {
				$message .= $row['uid'] . " rolled " . " <strong>" . $row['result'] . "</strong> on <a href=\"?time=" . $row['rid'] . "&user=" . $row['uid'] . "\">" . date("l j F Y", $row['rid']) . " at " . date("G:i.s", $row['rid']) . "</a>.<br />";
			}
		}
		$message .= "</p>";
		if (mysql_num_rows($result) + $offset != $totalresults) {
			$message .= "<p><a href=\"?browse=" . $_GET['browse'] . "&offset=" . ($offset + 10) . "&browseall=" . $browseall . "\">Next 10 Records</a></p>";
		}
		require 'results.php';
	} else {
		require 'gui.php';
	}
?>
