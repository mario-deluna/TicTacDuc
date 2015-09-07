<?php

namespace TicTacDuc;

class Game
{
    public $current = [];
    public $log = [];

    public $learn = [];

    public $storage = null;

    public function __construct($storage)
    {
        $this->storage = $storage;

        if (!file_exists($storage)) {
            file_put_contents($storage, '{"game":[], "log": []}');
        }

        $data = json_decode(file_get_contents($storage), true);

        $this->current = $data['game'];
        $this->log = $data['log'];

        // load the learning
        $this->learn = json_decode(file_get_contents(__DIR__  . '/../learning.json'), true);
    }

    protected function think()
    {
    	$situation = $this->situationKey($this->current);

    	// do we know this situation?
    	if (isset($this->learn[$situation]))
    	{
    		$possibleMoves = [];

    		for($i1=0;$i1<3;$i1++)
    		{
    			for($i2=0;$i2<3;$i2++)
	    		{
	    			$possibleMoves[$i1.'x'.$i2] = 1;
	    		}
    		}

    		// remove already played moves
    		foreach ($this->current as $cords => $player) {
    			unset($possibleMoves[$cords]);
    		}

    		// update probability
    		foreach ($possibleMoves as $cords => $probability) {
    			if (isset($this->learn[$situation][$cords]))
    			{
    				$possibleMoves[$cords] = $this->learn[$situation][$cords];
    			}
    		}

    		$sum = 0;

    		// multiply the probabilty
    		foreach($possibleMoves as $cords => $probability)
    		{
    			$sum += $probability;
    		}

            asort($possibleMoves);

            $random = (mt_rand() / mt_getrandmax()) *  $sum;

            $cur = 0;

            foreach ($possibleMoves as $cord => $probability) {
                $cur += $probability;

                if ($random <= $cur) {
                    $winner = $cord; break;
                }
            }

    		//var_dump($possibleMoves);
    		return $this->cpuMove($winner);
    	}

    	// other wise random
        do {
            $cords = mt_rand(0, 2) . 'x' . mt_rand(0, 2);
        } while (isset($this->current[$cords]));

        return $this->cpuMove($cords);
    }

    protected function situationKey($situation)
    {
    	ksort($situation);
    	
    	$key = '';

    	foreach($situation as $cords => $player)
    	{
    		$key .= $cords . '-' . $player . ':';
    	}

    	return $key;
    }

    protected function cpuMove($cords)
    {
    	$this->log[$this->situationKey($this->current)] = $cords;
    	$this->current[$cords] = 'o';
    }

    protected function learn($positive = false)
    {
    	$this->log = array_slice($this->log, -1);
    	
    	foreach($this->log as $key => $move)
    	{
    		if (!isset($this->learn[$key][$move]))
    		{
    			$this->learn[$key][$move] = 1;
    		}

    		// reduce or increse the probability of this move.
    		if ($positive)
    		{
    			//echo "Hell Yeah\n";
    			$this->learn[$key][$move] *= 2;

    			// fix memory issues
    			if ($this->learn[$key][$move] > 16) {
    				$this->learn[$key][$move] = 16;
    			}
    		}
    		else
    		{
    			//echo "Dammit!!\n";
    			$this->learn[$key][$move] /= 2;
    		}
    	}
    	
    	//$this->saveLearn();
    }

    protected function gameIsDone()
    {
        $winMoves =
        [
            ['0x0', '0x1', '0x2'],
            ['1x0', '1x1', '1x2'],
            ['2x0', '2x1', '2x2'],

            ['0x0', '1x0', '2x0'],
            ['0x1', '1x1', '2x1'],
            ['0x2', '1x2', '2x2'],
        ];

        $playerWin = false;

        if (count($this->current) === 9)
        {
        	//$this->learn(true);
        	return '=';
        }

        foreach (['x', 'o'] as $player) {

            foreach ($winMoves as $moves) {
                $hasWon = true;

                foreach ($moves as $cord) {
                    if (!($hasWon && isset($this->current[$cord]) && $this->current[$cord] === $player)) {
                        $hasWon = false;
                    }
                }

                if ($hasWon) {
                    $playerWin = $player;
                }
            }
        }

        if ($playerWin === 'o')
        {
        	$this->learn(true);
        }
        elseif($playerWin === 'x')
        {
        	$this->learn();
        }

        return $playerWin;
    }

    public function play($cords)
    {
        // only if its empty we can play it
        if (!$cords || isset($this->current[$cords])) {
            return;
        }

        $this->current[$cords] = 'x'; 

        // check if we won?
        if (!$winner = $this->gameIsDone()) {
        	// let the computer play
            $this->think();

            // check if he won
            $winner = $this->gameIsDone();
        }

        // if we have a winner stop the game
        if ($winner)
        {
        	return true;
        }
    }

    public function reset()
    {
        $this->current = []; $this->save();
    }

    public function save()
    {
    	file_put_contents($this->storage, json_encode(['game' => $this->current, 'log' => $this->log]));
    }

    public function saveLearn()
    {
    	file_put_contents(__DIR__  . '/../learning.json', json_encode($this->learn));
    }

    public function render()
    {
        echo Field::render($this->current);
    }
}
