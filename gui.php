<?php
	// Main GUI file
	// Should be imported
	$bgr1 = mt_rand(0,15);
	$bgg1 = mt_rand(0,15);
	$bgb1 = mt_rand(0,15);
	$bgr2 = mt_rand(0,15);
	$bgg2 = mt_rand(0,15);
	$bgb2 = mt_rand(0,15);
	$colour = "#" . dechex($bgr1) . dechex($bgr2) . dechex($bgg1) . dechex($bgg2) . dechex($bgb1) . dechex($bgb2);
?>
<html>
	<head>
		<title>aVDR - automagic Verifiable Dice Roller</title>
		<style type="text/css">
			body, html {
				background-color: <?=$colour?>;
				font-family: Georgia, Serif;
				text-align: center;
			}
			div#wrapper {
				width: 30em;
				text-align: center;
				background-color: #fff;
				margin: 5% auto;
				padding: 2%;
				font-size: 0.9em;
			}
			.disabled {
				color: #ccc;
			}
			h1 {
				margin: 0px;
				padding: 0px;
			}
			.small {
				font-size: 0.6em;
			}
			.big {
				font-size: 1.3em;
			}
			a {
				text-decoration: none;
				color: #99CCFF;
			}
		</style>
	</head>
	<body>
		<div id="wrapper">
			<h1>aVDR - automagic Verifiable Dice Roller</h1>
			<span class="small">A <a href="http://random.org">random.org</a> based verifiable dice roller</span>
			<p>aVDR is a verifiable (you can come back and check your rolls at any time, they don't change) dice roller. It uses <a href="http://random.org">random.org</a> to generate the random numbers using atmospheric noise. This means your dice rolls are truely random, unlike some other random number generators (such as PHP on Windows) which have a very low repeat rate. However, if our <a href="http://random.org">random.org</a> quota runs out, the dice roller will fall back to the built in linux random number generator, namely the PHP mt_rand() command.</p>
			<p>Input a username (used to distinguish your rolls from everyone elses) and a dice roll (in the format <em>x</em>D<em>y</em> where <em>x</em> is the number of dice and <em>y</em> is the type up to a maximum of 20d100). There are several options. Any greyed out option is either out of order or not working yet.</p> 
			<form action="index.php" method="GET">
				<strong class="big">Roll New Dice</strong><br />
				<strong>Required Fields:</strong><br />
				Username: <input type="text" name="user" /><br />
				Dice Roll: <input type="text" name="roll" /><br /><br />

				<strong>Optional Fields:</strong><br />
				<input type="checkbox" name="usetarget" value="true" /> Target Value: <input type="text" name="target" /><br />
				<input type="checkbox" name="usemodifier" value="true" /> Modifier: <input type="text" name="modifier" /><br />
				<input type="checkbox" name="usepickhighest" value="true" /> Pick the highest.<br />
				<input type="checkbox" name="usepickhighestdoubles" value="true" /> Pick the highest with doubles.<br />
				<input type="checkbox" name="usepicklowest" value="true" /> Pick the lowest.<br />
				<input type="checkbox" name="usererollabove" value="true" /> Re-roll dice above: <input type="text" name="rerollabove" /><br />
				<input type="checkbox" name="usererollbelow" value="true" /> Re-roll dice below: <input type="text" name="rerollbelow" /><br /><br />

				<input type="submit" value="Roll" />
			</form>

			<form action="index.php" method="GET">
				<strong class="big">Check Old Rolls</strong><br />
				<strong>Required Fields:</strong><br />
				Username: <input type="text" name="user" /><br />
				Roll Timestamp: <input type="text" name="time" /><br /><br />

				<strong>Optional Fields:</strong><br />
				<input type="checkbox" name="useold" value="true" checked="true" disabled="true" /> History Length: <input type="text" name="old" value="8" /><br /><br />

				<input type="submit" value="Check" />
			</form>

			<form action="index.php" method="GET">
				<strong class="big">Browse Rolls</strong><br />
				Enter the usernames you want to check. Seperate usernames with a comma (,).<br />
				<strong>Required Fields:</strong><br />
				Usernames: <input type="text" name="browse" /><br />

				<strong>Optional Fields:</strong><br />
				<input type="checkbox" name="browseall" value="true"/> Browse All<br />
				<input type="checkbox" name="useoffset" value="true" checked="true" disabled="true" /> Offset: <input type="text" name="offset" value="0" /><br /><br />

				<input type="submit" value="Check" />
			</form>
			<span class="small">aVDR v<?=$version?> - Copyright Connor Shearwood (a.k.a. Drake Andrews) 2010 - Code released under the GNU Public License. Your background colour today is <span style="color: <?=$colour?>;"><?=$colour?></span>. I have <?=$quota?> bits of random data and <?=$maxnoofdie?> dice rolls left. <?=gettotaldiceserved()?> rolls made and counting. Page generated in <?=round(microtime()-$starttime, 3)?> seconds.</span>
		</div>
	</body>
</html>
