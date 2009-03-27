<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @author Philip Sturgeon
 * @created 11 Dec 2008
 */

class CLI {
	
    private $CI;    // CodeIgniter instance
    
    var $wait_msg = 'Press any key to continue...';
    
    function CLI() {
        $this->CI =& get_instance();
        log_message('debug', 'CLI Class Initialized');
    }
    
    // Output a line (or lines) to the command line
    function write($output = '') {
        
        // If there are multiple lines, seperate them by newlines
        if(is_array($output)) {
            $output = implode("\n", $output);
        }
        
        // Output the lot
        fwrite(STDOUT, $output."\n");
        
        return $this;
    }

    // Read in a variable from the command line
    function read() {
        
        // Work out whats what based on what params are given
        $args = func_get_args();
        
        // Ask question with options
        if(count($args) == 2) {
            list($output, $options)=$args;
        
        // No question (probably been asked already) so just show options
        } elseif(count($args) == 1 && is_array($args[0])) {
            $output = '';
            $options = $args[0];
        
        // Question without options
        } elseif(count($args) == 1 && is_string($args[0])) {
            $output = $args[0];
            $options = array();
        
        // Run out of ideas, EPIC FAIL!
        } else {
            $output = '';
            $options = array();
        }
        
        // If a question has been asked with the read
        if(!empty($output)) {
            
            $options_output = '';
            if(!empty($options)) {
                $options_output = ' [ '.implode(', ', $options).' ]';
            }           

            fwrite(STDOUT, $output.$options_output.': ');
        }
        
        // Read the input from keyboard.
        $input = trim(fgets(STDIN));
        
        // If options are provided and the choice is not in the array, tell them to try again
        if(!empty($options) && !in_array($input, $options)) {
            $this->write('This is not a valid option. Please try again.');
            $this->new_line();
            
            $input = $this->read($output, $options);
        }
        
        // Read the input
        return $input;
    }
    
    function new_line($lines = 1) {
        // Do it once or more, write with empty string gives us a new line
        for($i = 0; $i < $lines; $i++) $this->write();
        
        return $this;
    }
    
    function clear_screen()
    {
        # xterm / konsole: array_map(create_function('$a', 'print chr($a);'), array(27, 91, 72, 27, 91, 50, 74));
        # bash: passthru('clear');
        /* unix: if ($proc = popen("(cls)2>&1","r")){
            $result = '';
            while (!feof($proc)) $result .= fgets($proc, 1000);
            pclose($proc);
            return $result;
        } */
        
        // works, ugly
        $this->new_line(100);
        
        /* $clearscreen = chr(27)."[H".chr(27)."[2J";
        print $clearscreen; */
        
        /*echo "Starting Iteration" . "\n\r";
        for ($i=0;$i<10000;$i++) {
            echo "\r";
        }
        echo "Ending Iteration" . "\n\r";*/
  }

    // Thanks to Daniel Morris (danielxmorris.com)
    function beep($beeps = 1) {
        
        $string_beeps = '';
        
        // Output the correct number of beep characters
        for ($i = 0; $i < $beeps; $i++) $string_beeps .= "\x07";
        
        print $string_beeps;
        
        return $this;
    }
        
    function wait($seconds = 0, $countdown = FALSE) {
        
        // Diplay the countdown
        if($countdown == TRUE) {
            $i = $seconds;
            while ( $i > 0 ) {
               fwrite(STDOUT, "\r".$i.'...');
               sleep(1);
               $i--;
            }
        
        // No countdown timer please
        } else {
            
            // Set number of seconds?
            if($seconds > 0) {
                sleep($seconds);
            
            // No seconds mentioned, lets wait for user input    
            } else {
                $this->write($this->wait_msg);
                $this->read();
            }
        }

        return $this;
    }

}
// END CLI Class

/* End of file CLI.php */
/* Location: ./application/libraries/CLI.php */
?>		