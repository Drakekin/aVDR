<?php
	// Results GUI file
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
			.medium {
				font-size: 0.85em;
			}
		</style>
	</head>
	<body>
		<div id="wrapper">
			<h1>aVDR - automagic Verifiable Dice Roller</h1>
			<span class="small">A <a href="http://random.org">random.org</a> based verifiable dice roller</span>

			<?=$message?>

			<p>Back to the <a href="?">Main Page</a></p>
			<span class="small">aVDR v<?=$version?> - Copyright Connor Shearwood (a.k.a. Drake Andrews) 2010 - Code released under the GNU Public License. Your background colour today is <span style="color: <?=$colour?>;"><?=$colour?></span>. Link to this page: <a href="http://<?=($_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])?>"><?=($_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'])?></a>. I have <?=$quota?> bits of random data and <?=$maxnoofdie?>  dice rolls left. <?=gettotaldiceserved()?> rolls made and counting. Page generated in <?=round(microtime()-$starttime, 3)?> seconds.</span>
		</div>
	</body>
</html>
