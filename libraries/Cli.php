<?php

/**
 * Cli class
 *
 * Interact with the command line by accepting input options, parameters and output text
 *
 * @package		CodeIgniter
 * @category	Libraries
 * @author		Phil Sturgeon
 * @link		http://fuelphp.com/docs/classes/cli.html
 */
class Cli {

	public $readline_support = false;

	public $wait_msg = 'Press any key to continue...';

	protected $_args = array();

	protected $foreground_colors = array(
		'black'			=> '0;30',
		'dark_gray'		=> '1;30',
		'blue'			=> '0;34',
		'light_blue'	=> '1;34',
		'green'			=> '0;32',
		'light_green'	=> '1;32',
		'cyan'			=> '0;36',
		'light_cyan'	=> '1;36',
		'red'			=> '0;31',
		'light_red'		=> '1;31',
		'purple'		=> '0;35',
		'light_purple'	=> '1;35',
		'brown'			=> '0;33',
		'yellow'		=> '1;33',
		'light_gray'	=> '0;37',
		'white'			=> '1;37',
	);

	protected $background_colors = array(
		'black'			=> '40',
		'red'			=> '41',
		'green'			=> '42',
		'yellow'		=> '43',
		'blue'			=> '44',
		'magenta'		=> '45',
		'cyan'			=> '46',
		'light_gray'	=> '47',
	);

	/**
	 * Constructor.	Parses all the CLI params.
	 */
	public function __construct()
	{
		for ($i = 1; $i < $_SERVER['argc']; $i++)
		{
			$arg = explode('=', $_SERVER['argv'][$i]);

			$this->_args[$i] = $arg[0];

			if (count($arg) > 1 || strncmp($arg[0], '-', 1) === 0)
			{
				$this->_args[ltrim($arg[0], '-')] = isset($arg[1]) ? $arg[1] : true;
			}
		}

		// Readline is an extension for PHP that makes interactive with PHP much more bash-like
		// http://www.php.net/manual/en/readline.installation.php
		$this->readline_support = extension_loaded('readline');
	}

	/**
	 * Returns the option with the given name.	You can also give the option
	 * number.
	 *
	 * Named options must be in the following formats:
	 * php index.php user -v --v -name=John --name=John
	 *
	 * @param	string|int	$name	the name of the option (int if unnamed)
	 * @return	string
	 */
	public function option($name, $default = null)
	{
		if ( ! isset($this->_args[$name]))
		{
			return $default;
		}
		return $this->_args[$name];
	}


	/**
	 * Get input from the shell, using readline or the standard STDIN
	 *
	 * Named options must be in the following formats:
	 * php index.php user -v --v -name=John --name=John
	 *
	 * @param	string|int	$name	the name of the option (int if unnamed)
	 * @return	string
	 */
	public function input($prefix = '')
	{
        if ($this->readline_support)
		{
			return readline($prefix);
		}

		echo $prefix;
		return fgets(STDIN);
	}


	/**
	 * Asks the user for input.  This can have either 1 or 2 arguments.
	 *
	 * Usage:
	 *
	 * // Waits for any key press
	 * $this->cli->prompt();
	 *
	 * // Takes any input
	 * $color = $this->cli->prompt('What is your favorite color?');
	 *
	 * // Takes any input, but offers default
	 * $color = $this->cli->prompt('What is your favourite color?', 'white');
	 *
	 * // Will only accept the options in the array
	 * $ready = $this->cli->prompt('Are you ready?', array('y','n'));
	 *
	 * @return	string	the user input
	 */
	public function prompt()
	{
		$args = func_get_args();

		$options = array();
		$output = '';
		$default = null;

		// How many we got
		$arg_count = count($args);

		// Is the last argument a boolean? True means required
		$required = end($args) === true;

		// Reduce the argument count if required was passed, we don't care about that anymore
		$required === true and --$arg_count;

		// This method can take a few crazy combinations of arguments, so lets work it out
		switch ($arg_count)
		{
			case 2:

				// E.g: $ready = $this->cli->prompt('Are you ready?', array('y','n'));
				if (is_array($args[1]))
				{
					list($output, $options)=$args;
				}

				// E.g: $color = $this->cli->prompt('What is your favourite color?', 'white');
				elseif (is_string($args[1]))
				{
					list($output, $default)=$args;
				}

			break;

			case 1:

				// No question (probably been asked already) so just show options
				// E.g: $ready = $this->cli->prompt(array('y','n'));
				if (is_array($args[0]))
				{
					$options = $args[0];
				}

				// Question without options
				// E.g: $ready = $this->cli->prompt('What did you do today?');
				elseif (is_string($args[0]))
				{
					$output = $args[0];
				}

			break;
		}

		// If a question has been asked with the read
		if ($output !== '')
		{
			$extra_output = '';

			if ($default !== null)
			{
				$extra_output = ' [ Default: "'.$default.'" ]';
			}

			elseif ($options !== array())
			{
				$extra_output = ' [ '.implode(', ', $options).' ]';
			}

			fwrite(STDOUT, $output.$extra_output.': ');
		}

		// Read the input from keyboard.
		($input = trim($this->input())) or $input = $default;

		// No input provided and we require one (default will stop this being called)
		if (empty($input) and $required === true)
		{
			$this->write('This is required.');
			$this->new_line();

			$input = call_user_func(array($this, 'prompt'), $args);
		}

		// If options are provided and the choice is not in the array, tell them to try again
		if ( ! empty($options) && ! in_array($input, $options))
		{
			$this->write('This is not a valid option. Please try again.');
			$this->new_line();

			$input = call_user_func_array(array($this, 'prompt'), $args);
		}

		return $input;
	}

	/**
	 * Outputs a string to the cli.	 If you send an array it will implode them
	 * with a line break.
	 *
	 * @param	string|array	$text	the text to output, or array of lines
	 */
	public function write($text = '', $foreground = null, $background = null)
	{
		if (is_array($text))
		{
			$text = implode(PHP_EOL, $text);
		}

		if ($foreground OR $background)
		{
			$text = $this->color($text, $foreground, $background);
		}

		fwrite(STDOUT, $text.PHP_EOL);
	}

	/**
	 * Outputs an error to the CLI using STDERR instead of STDOUT
	 *
	 * @param	string|array	$text	the text to output, or array of errors
	 */
	public function error($text = '', $foreground = 'light_red', $background = null)
	{
		if (is_array($text))
		{
			$text = implode(PHP_EOL, $text);
		}

		if ($foreground OR $background)
		{
			$text = $this->color($text, $foreground, $background);
		}

		fwrite(STDERR, $text.PHP_EOL);
	}

	/**
	 * Beeps a certain number of times.
	 *
	 * @param	int $num	the number of times to beep
	 */
	public function beep($num = 1)
	{
		echo str_repeat("\x07", $num);
	}

	/**
	 * Waits a certain number of seconds, optionally showing a wait message and
	 * waiting for a key press.
	 *
	 * @param	int		$seconds	number of seconds
	 * @param	bool	$countdown	show a countdown or not
	 */
	public function wait($seconds = 0, $countdown = false)
	{
		if ($countdown === true)
		{
			$time = $seconds;

			while ($time > 0)
			{
				fwrite(STDOUT, $time.'... ');
				sleep(1);
				$time--;
			}
			$this->write();
		}

		else
		{
			if ($seconds > 0)
			{
				sleep($seconds);
			}
			else
			{
				$this->write($this->wait_msg);
				$this->read();
			}
		}
	}


	/**
	 * if oprerating system === windows
	 */
 	public function is_windows()
 	{
 		return 'win' === strtolower(substr(php_uname("s"), 0, 3));
 	}

	/**
	 * Enter a number of empty lines
	 *
	 * @param	integer	Number of lines to output
	 * @return	void
	 */
	function new_line($num = 1)
	{
        // Do it once or more, write with empty string gives us a new line
        for($i = 0; $i < $num; $i++)
		{
			$this->write();
		}
    }

	/**
	 * Clears the screen of output
	 *
	 * @return	void
	 */
    function clear_screen()
    {
		$this->is_windows()

			// Windows is a bit crap at this, but their terminal is tiny so shove this in
			? $this->new_line(40)

			// Anything with a flair of Unix will handle these magic characters
			: fwrite(STDOUT, chr(27)."[H".chr(27)."[2J");
	}

	/**
	 * Returns the given text with the correct color codes for a foreground and
	 * optionally a background color.
	 *
	 * @param	string	$text		the text to color
	 * @param	atring	$foreground the foreground color
	 * @param	string	$background the background color
	 * @return	string	the color coded string
	 */
	public function color($text, $foreground, $background = null)
	{
		if ($this->is_windows())
		{
			return $text;
		}

		if ( ! array_key_exists($foreground, $this->foreground_colors))
		{
			throw new Exception('Invalid CLI foreground color: '.$foreground);
		}

		if ( $background !== null and ! array_key_exists($background, $this->background_colors))
		{
			throw new Exception('Invalid CLI background color: '.$background);
		}

		$string = "\033[".$this->foreground_colors[$foreground]."m";

		if ($background !== null)
		{
			$string .= "\033[".$this->background_colors[$background]."m";
		}

		$string .= $text."\033[0m";

		return $string;
	}

}

/* End of file cli.php */