<?php

require __DIR__ . "/vendor/autoload.php";

use TicTacDuc\Game;

$game = new Game( __DIR__ . '/game.json' );

$index = 0;

for($i=0;$i<100000000;$i++)
{
	 do {
	    $cords = mt_rand(0, 2) . 'x' . mt_rand(0, 2);
	} while (isset($game->current[$cords]));

	if ( $game->play($cords) )
	{
		$game->current = [];
	}

	$index++;

	if ($index == 10000)
	{
		$index = 0; $game->saveLearn();
		echo 'rounds played: '.$i. "\n";
	}
}
