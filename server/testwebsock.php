#!/usr/bin/env php
<?php

require_once('./websockets.php');
require 'vendor/autoload.php';
 
use Parse\ParseClient;
use Parse\ParseQuery;
use Parse\ParseObject;
 
ParseClient::initialize('A07dbFl8rsyaKkBC3WPWRPVhGHmkC1ifoqmM6ZpT', 'qDRNlvfUEN2tLdVOFfg66PUPLjjdGwJCbh7ZAzvY', 'dqNVf1anhhlfxZpN8ROksf1SGlDP4FNciZoFpguR');

$query = new ParseQuery("Intrebari");
$query->limit(1000);
$results = $query->find();
echo "Successfully retrieved " . count($results) . " questions.\n";
$total_questions = count($results);

class echoServer extends WebSocketServer {
  //protected $maxBufferSize = 1048576; //1MB... overkill for an echo server, but potentially plausible for other applications.
  protected $playerLocations = array();
  protected $robotLocation = array("x" => 0, "y" => 0);
  protected $radius = 2;
  protected $minStep = 0.07;

  protected $step = array();

  protected function restart() {
echo "Restart\n";
  	$this->playerLocations = array();
	$this->robotLocation = array("x" => 0, "y" => 0);
	$this->step = array();

	foreach($this->users as $auser) {
	  $this->disconnect($auser->socket);
	}
  }
  
  protected function process ($user, $message) {
    echo ($message . "\n");
    $message = json_decode($message);
    switch($message->command) {
      case 'restart':
        $this->restart();
        break;
      case 'robot':
        $this->robot = $user;
	$user->robot = true;
      case 'ready':
        $user->playerReady = true;
	$this->checkPlay();
        break;
      case 'moved':
        $this->nextQuestion();
        break;
      case 'connect' :
        $user->score = 0;
	foreach($this->users as $auser) {
	  if (!@$auser->robot && @$auser->group) {
	    $this->send($user, '{"command": "connected", "group": ' . $auser->group . '}');
	  }
        }
        break;
      case 'setGroup': 
        $user->score = 0;
        $oldGroup = @$user->group;
	$user->group = $message->group;
	$n = 0;
	foreach($this->users as $auser) {
	  if (!@$auser->robot) {
	    if (@$auser->group) {
	      $n++;
	    }
	  }
	}
	foreach($this->users as $auser) {
	  if (!@$auser->robot) {
	    if ($oldGroup) {
	      $this->send($auser, '{"command": "disconnected", "group": ' . $oldGroup . '}');
	    }
	    $this->send($auser, '{"command": "connected", "group": ' . $message->group . '}');
	  }
	  if (@$auser->group) {
	    $i = $auser->group - 1;
	    $this->playerLocations[$i] = array(
	      "x" => (cos($i*360/$n * M_PI / 180) * $this->radius),
	      "y" => (sin($i*360/$n * M_PI / 180) * $this->radius)
	    );
	    if(!@$this->step[$i]) {
	      $this->step[$i] = $this->minStep;
	    }
	  }
	}
        break;
      case 'answer':
        if ($message->answer == $this->answer) {
	  $user->score++;
	  $i = $user->group-1;
	  $x = $this->playerLocations[$i]['x'] - $this->robotLocation['x'];
	  $y = $this->playerLocations[$i]['y'] - $this->robotLocation['y'];
	  if ($x == 0) {
	    $alpha = $y>=0?0:180;
	  } else {
	   $alpha = atan($y/$x)/M_PI*180;
	   if ($x<0) $alpha += 180;
	   if ($alpha > 180) $alpha -= 360;
	  }
	  $xd = cos($alpha * M_PI / 180) * $this->step[$i];
	  $yd = sin($alpha * M_PI / 180) * $this->step[$i];

	  $this->robotLocation['x'] += $xd;
	  $this->robotLocation['y'] += $yd;
	  $reached = false;
	  $distFromCenter = sqrt($this->robotLocation['x']*$this->robotLocation['x']+$this->robotLocation['y']*$this->robotLocation['y']);

	  if ($distFromCenter > $this->radius) {
echo "distFromCenter: $distFromCenter\n";

	    $xp = $this->playerLocations[$i]['x'] - ($this->robotLocation['x'] - $xd);
	    $yp = $this->playerLocations[$i]['y'] - ($this->robotLocation['y'] - $yd);

            $dist = sqrt($xp*$xp + $yp*$yp);
            $this->robotLocation['x'] = $this->playerLocations[$i]['x'];
            $this->robotLocation['y'] = $this->playerLocations[$i]['y'];
            $reached = true;
	  } else {
	    $dist = $this->step[$i];
	  }
print_r($this->robotLocation);

	  $this->step[$i] += $this->minStep;
          $alpha = intval($alpha);

	  if (@$this->robot) {
            $this->send($this->robot, '{"command": "rotate", "angle": ' . $alpha . '}');
            $this->send($this->robot, '{"command": "move", "distance": ' . $dist . '}');
	  }

	  if ($reached) {
	    if (@$this->robot) {
              $this->send($this->robot, '{"command": "finish"}');
	    }
	    $this->restart();
	    break;
	  }
	  echo "Rotate to: $alpha\n";
	  echo "Move: $dist\n";
          $this->answer = 'waiting for next question';
	  if (!@$this->robot) {
	    sleep(2);
	    $this->nextQuestion();
	  }
	}
        break;
      case 'disconnect':
      	$this->disconnect($user->socket, true);
	break;
    }
  }

  protected function checkPlay() {
    foreach($this->users as $auser) {
      if (!@$auser->playerReady) {
        return false;
      }
    }
    if (!@$this->robot) return false;

	foreach($this->users as $auser) {
	  if (!@$auser->robot) {
	    $this->send($auser, '{"command": "allPlayersReady"}');
	  }
	}
	sleep(1);
	$this->nextQuestion();
  }

  protected function addAnswer(&$arr, $val) {
    foreach($arr as $exist) {
      if ($exist == $val) return;
    }
    array_push($arr, $val);
  }

  protected function nextQuestion() {
    switch(rand(1,10)) {
      case 1:
        $this->sum1(9,9);
	break;
      case 2:
        $this->sum1(19,19);
	break;
      case 3:
        $this->dif1(9,9);
	break;
      case 4:
        $this->dif1(19,19);
	break;
      default:
        $this->cloudQ();
        break;
    }
  }

  protected function cloudQ() {
    $i = rand(0, $GLOBALS['total_questions']-1);

    $q = $GLOBALS['results'][$i]->get('Intrebare');

    $answers = array();
    array_push($answers, $GLOBALS['results'][$i]->get('Raspuns_Corect'));
    array_push($answers, $GLOBALS['results'][$i]->get('Raspuns_2'));
    array_push($answers, $GLOBALS['results'][$i]->get('Raspuns_3'));
    array_push($answers, $GLOBALS['results'][$i]->get('Raspuns_4'));
    array_push($answers, $GLOBALS['results'][$i]->get('Raspuns_5'));

    for ($i=0;$i<count($answers);$i++) {
      if ($answers[$i] == '') $answers[$i] = "0";
    }

    $correct = $answers[0];

    shuffle($answers);

    $ansStr = '[';
    $first = true;
    foreach($answers as $answer) {
      if (!$first) $ansStr .= ', ';
      $ansStr .= '"' . $answer . '"';
      $first = false;
    }
    $ansStr .= ']';

    $resp = '{"command": "question", "question": "'.$q.'", "answers": '.$ansStr.', "correct": "'.$correct.'"}';
    $this->answer = $correct;
    foreach($this->users as $auser) {
      if (!@$auser->robot) {
        $this->send($auser, $resp);
      }
    }
  }


  protected function dif1($max1, $max2) {
    echo "Next!\n";
    $a = rand(1,$max1);
    $c = rand(1,$max2);
    $b = min($a, $c);
    $a = max($a, $c);
    $q = "$a - $b";
    $correct = $a-$b;

    $answers = array($correct);
    $this->addAnswer($answers, abs($a-$b+rand(-1,$max2)));
    $this->addAnswer($answers, $a - $b + rand(1,$max1));
    $this->addAnswer($answers, abs($a-rand(1, $max2)));
    $this->addAnswer($answers, $b-rand(1, $max1));

    while(sizeof($answers) != 5) {
        $this->addAnswer($answers, $a-$b+rand(-$max2,$max1));
    }

    shuffle($answers);

    $ansStr = '[';
    $first = true;
    foreach($answers as $answer) {
      if (!$first) $ansStr .= ', ';
      $ansStr .= '"' . $answer . '"';
      $first = false;
    }
    $ansStr .= ']';

    $resp = '{"command": "question", "question": "'.$q.'", "answers": '.$ansStr.', "correct": "'.$correct.'"}';
    $this->answer = $correct;
        foreach($this->users as $auser) {
	  if (!@$auser->robot) {
            $this->send($auser, $resp);
	  }
        }

  }


  protected function sum1($max1=9, $max2=9) {
    echo "Next!\n";
    $a = rand(1,$max1);
    $b = rand(1,$max2);
    $q = "$a + $b";
    $max = max($a, $b);
    $min = min($a, $b);
    $correct = $a+$b;

    $answers = array($correct);
    $this->addAnswer($answers, $a+$b+rand(1,$max1));
    $this->addAnswer($answers, $max - $min + rand($min-5, $max+5));
    $this->addAnswer($answers, $a+rand(1, $max2));
    $this->addAnswer($answers, $b+rand(1, $max1));

    while(sizeof($answers) != 5) {
    	$this->addAnswer($answers, $a+$b+rand(-$max1,$max2));
    }

    shuffle($answers);
    
    $ansStr = '[';
    $first = true;
    foreach($answers as $answer) {
      if (!$first) $ansStr .= ', ';
      $ansStr .= '"' . $answer . '"';
      $first = false;
    }
    $ansStr .= ']';

    $resp = '{"command": "question", "question": "'.$q.'", "answers": '.$ansStr.', "correct": "'.$correct.'"}';
    $this->answer = $correct;
        foreach($this->users as $auser) {
	  if (!@$auser->robot) {
	    $this->send($auser, $resp);
	  }
	}
	
  }

  
  protected function connected ($user) {
    echo "Users: " . count($this->users) . "\n";
  }
  
  protected function closed ($user) {
    echo "Users: " . count($this->users) . "\n";
        if (@$user->robot) {
	  $this->robot = false;
	}
        $allLeft = true;
	foreach($this->users as $auser) {
	  if (!@$auser->robot) {
	    if ($user != $auser && $user->group) {
	      $this->send($auser, '{"command": "disconnected", "group": ' . $user->group . '}');
	      $allLeft = false;
	    }
	  }
	}
  }
}

$echo = new echoServer("0.0.0.0","9000");

try {
  $echo->run();
}
catch (Exception $e) {
  $echo->stdout($e->getMessage());
}
