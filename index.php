<?php

require __DIR__ . "/vendor/autoload.php";

use TicTacDuc\Game;

$game = new Game( __DIR__ . '/game.json' );

if ($game->play(isset($_GET['play']) ? $_GET['play'] : false))
{
	$game->render();
	$game->reset();
}

$game->save();
$game->saveLearn();

$game->render();