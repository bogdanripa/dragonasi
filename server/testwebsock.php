#!/usr/bin/env php
<?php

require_once('./websockets.php');

class echoServer extends WebSocketServer {
  //protected $maxBufferSize = 1048576; //1MB... overkill for an echo server, but potentially plausible for other applications.
  
  protected function process ($user, $message) {
    $message = json_decode($message);
    switch($message->command) {
      case 'connect' :{
	foreach($this->users as $auser) {
	  if (@$auser->group) {
	    $this->send($user, '{"command": "connected", "group": ' . $auser->group . '}');
	  }
	}
        break;
      }
      case 'setGroup': {
        $oldGroup = @$user->group;
	$user->group = $message->group;
	foreach($this->users as $auser) {
	  if ($oldGroup) {
	    $this->send($auser, '{"command": "disconnected", "group": ' . $oldGroup . '}');
	  }
	  $this->send($auser, '{"command": "connected", "group": ' . $message->group . '}');
	}
        break;
      }
      case 'ready':
        $user->playerReady = true;
	$this->checkPlay();
        break;
      case 'answer':
        if ($message->answer == $this->answer) {
          $this->answer = 'waiting for next question';
	  sleep(2);
	  $this->nextQuestion();
	}
        break;
    }
  }

  protected function checkPlay() {
    foreach($this->users as $auser) {
      if (!@$auser->playerReady) {
        return false;
      }
    }
	foreach($this->users as $auser) {
	    $this->send($auser, '{"command": "allPlayersReady"}');
	}
	sleep(1);
	$this->nextQuestion();
  }

  protected function addAnswer($arr, $val) {
    foreach($arr as $exist) {
      if ($exist == $val) return;
    }
    array_push($arr, $val);
  }

  protected function nextQuestion() {
    echo "Next!\n";
    $a = rand(1,9);
    $b = rand(1,9);
    $q = "$a + $b";
    $max = max($a, $b);
    $min = min($a, $b);
    $correct = $a+$b;

    $answers = array($correct);
    $this->addAnswer(&$answers, $a+$b+rand(1,9));
    $this->addAnswer(&$answers, $max - $min + 10);
    $this->addAnswer(&$answers, $a+rand(1, 9));
    $this->addAnswer(&$answers, $b+rand(1, 9));

    while(sizeof($answers) != 5) {
    	$this->addAnswer(&$answers, $a+$b+rand(-10,10));
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
	    $this->send($auser, $resp);
	}
	
  }
  
  protected function connected ($user) {
    // Do nothing: This is just an echo server, there's no need to track the user.
    // However, if we did care about the users, we would probably have a cookie to
    // parse at this step, would be looking them up in permanent storage, etc.
  }
  
  protected function closed ($user) {
    // Do nothing: This is where cleanup would go, in case the user had any sort of
    // open files or other objects associated with them.  This runs after the socket 
    // has been closed, so there is no need to clean up the socket itself here.
    echo "closed\n";
        $allLeft = true;
	foreach($this->users as $auser) {
	  if ($user != $auser && $user->group) {
	    $this->send($auser, '{"command": "disconnected", "group": ' . $user->group . '}');
	    $allLeft = false;
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
