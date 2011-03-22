# CodeIgniter-CLI

Interact with the command line by accepting input options, parameters and output text.

## History

This library was originally written in 2009 and was... alright. Now it has been totally rewritten based on the FuelPHP CLI library I wrote, which was based on this. MADNESS!

## Requirements

* CodeIgniter 2.0.x

## Examples

	// Output "Hello World" to the CLI
	$this->cli->write('Hello World!');

	// Waits for any key press
	$this->cli->prompt();

	// Takes any input
	$color = $this->cli->prompt('What is your favorite color?');

	// Takes any input, but offers default
	$color = $this->cli->prompt('What is your favorite color?', 'white');

	// Will only accept the options in the array
	$ready = $this->cli->prompt('Are you ready?', array('y','n'));

	$this->cli->write('Loading...');
	$this->cli->wait(5, true);