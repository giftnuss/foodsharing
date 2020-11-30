<?php

namespace Flourish;

use Exception;

/**
 * Provides low-level debugging, error and exception functionality.
 *
 * @copyright  Copyright (c) 2007-2011 Will Bond, others
 * @author     Will Bond [wb] <will@flourishlib.com>
 * @author     Will Bond, iMarc LLC [wb-imarc] <will@imarc.net>
 * @author     Nick Trew [nt]
 * @author     Kevin Hamer [kh] <kevin@imarc.net>
 * @author     Jeff Turcotte [jt] <jeff@imarc.net>
 * @license    http://flourishlib.com/license
 *
 * @see       http://flourishlib.com/fCore
 *
 * @version    1.0.0b26
 * @changes    1.0.0b26  Added handle_fatal_errors flag to enableErrorHandling [jt, 2013-06-10]
 * @changes    1.0.0b25  exposing ->generateContext() [kh, 2012-12-18]
 * @changes    1.0.0b24  Backwards Compatibility Break - moved ::detectOpcodeCache() to fLoader::hasOpcodeCache() [wb, 2011-08-26]
 * @changes    1.0.0b23  Backwards Compatibility Break - changed the email subject of error/exception emails to include relevant file info, instead of the timestamp, for better email message threading [wb, 2011-06-20]
 * @changes    1.0.0b22  Fixed a bug with dumping arrays containing integers [wb, 2011-05-26]
 * @changes    1.0.0b21  Changed ::startErrorCapture() to allow "stacking" it via multiple calls, fixed a couple of bugs with ::dump() mangling strings in the form `int(1)`, fixed mispelling of `occurred` [wb, 2011-05-09]
 * @changes    1.0.0b20  Backwards Compatibility Break - Updated ::expose() to not wrap the data in HTML when running via CLI, and instead just append a newline [wb, 2011-02-24]
 * @changes    1.0.0b19  Added detection of AIX to ::checkOS() [wb, 2011-01-19]
 * @changes    1.0.0b18  Updated ::expose() to be able to accept multiple parameters [wb, 2011-01-10]
 * @changes    1.0.0b17  Fixed a bug with ::backtrace() triggering notices when an argument is not UTF-8 [wb, 2010-08-17]
 * @changes    1.0.0b16  Added the `$types` and `$regex` parameters to ::startErrorCapture() and the `$regex` parameter to ::stopErrorCapture() [wb, 2010-08-09]
 * @changes    1.0.0b15  Added ::startErrorCapture() and ::stopErrorCapture() [wb, 2010-07-05]
 * @changes    1.0.0b14  Changed ::enableExceptionHandling() to only call fException::printMessage() when the destination is not `html` and no callback has been defined, added ::configureSMTP() to allow using fSMTP for error and exception emails [wb, 2010-06-04]
 * @changes    1.0.0b13  Added the `$backtrace` parameter to ::backtrace() [wb, 2010-03-05]
 * @changes    1.0.0b12  Added ::getDebug() to check for the global debugging flag, added more specific BSD checks to ::checkOS() [wb, 2010-03-02]
 * @changes    1.0.0b11  Added ::detectOpcodeCache() [nt+wb, 2009-10-06]
 * @changes    1.0.0b10  Fixed ::expose() to properly display when output includes non-UTF-8 binary data [wb, 2009-06-29]
 * @changes    1.0.0b9   Added ::disableContext() to remove context info for exception/error handling, tweaked output for exceptions/errors [wb, 2009-06-28]
 * @changes    1.0.0b8   ::enableErrorHandling() and ::enableExceptionHandling() now accept multiple email addresses, and a much wider range of emails [wb-imarc, 2009-06-01]
 * @changes    1.0.0b7   ::backtrace() now properly replaces document root with {doc_root} on Windows [wb, 2009-05-02]
 * @changes    1.0.0b6   Fixed a bug with getting the server name for error messages when running on the command line [wb, 2009-03-11]
 * @changes    1.0.0b5   Fixed a bug with checking the error/exception destination when a log file is specified [wb, 2009-03-07]
 * @changes    1.0.0b4   Backwards compatibility break - ::getOS() and ::getPHPVersion() removed, replaced with ::checkOS() and ::checkVersion() [wb, 2009-02-16]
 * @changes    1.0.0b3   ::handleError() now displays what kind of error occurred as the heading [wb, 2009-02-15]
 * @changes    1.0.0b2   Added ::registerDebugCallback() [wb, 2009-02-07]
 * @changes    1.0.0b    The initial implementation [wb, 2007-09-25]
 */
class fCore
{
	// The following constants allow for nice looking callbacks to static methods
	const backtrace = 'Flourish\\fCore::backtrace';
	const call = 'Flourish\\fCore::call';
	const callback = 'Flourish\\fCore::callback';
	const configureSMTP = 'Flourish\\fCore::configureSMTP';
	const disableContext = 'Flourish\\fCore::disableContext';
	const dump = 'Flourish\\fCore::dump';
	const enableDynamicConstants = 'Flourish\\fCore::enableDynamicConstants';
	const enableErrorHandling = 'Flourish\\fCore::enableErrorHandling';
	const enableExceptionHandling = 'Flourish\\fCore::enableExceptionHandling';
	const expose = 'Flourish\\fCore::expose';
	const handleError = 'Flourish\\fCore::handleError';
	const handleFatalError = 'Flourish\\fCore::handleFatalError';
	const handleException = 'Flourish\\fCore::handleException';
	const reset = 'Flourish\\fCore::reset';
	const sendMessagesOnShutdown = 'Flourish\\fCore::sendMessagesOnShutdown';
	const startErrorCapture = 'Flourish\\fCore::startErrorCapture';
	const stopErrorCapture = 'Flourish\\fCore::stopErrorCapture';

	/**
	 * The nesting level of error capturing.
	 *
	 * @var int
	 */
	private static $captured_error_level = 0;

	/**
	 * A stack of regex to match errors to capture, one string per level.
	 *
	 * @var array
	 */
	private static $captured_error_regex = array();

	/**
	 * A stack of the types of errors to capture, one integer per level.
	 *
	 * @var array
	 */
	private static $captured_error_types = array();

	/**
	 * A stack of arrays of errors that have been captured, one array per level.
	 *
	 * @var array
	 */
	private static $captured_errors = array();

	/**
	 * A stack of the previous error handler, one callback per level.
	 *
	 * @var array
	 */
	private static $captured_errors_previous_handler = array();

	/**
	 * If the context info has been shown.
	 *
	 * @var bool
	 */
	private static $context_shown = false;

	/**
	 * If dynamic constants should be created.
	 *
	 * @var bool
	 */
	private static $dynamic_constants = false;

	/**
	 * Error destination.
	 *
	 * @var string
	 */
	private static $error_destination = 'html';

	/**
	 * An array of errors to be send to the destination upon page completion.
	 *
	 * @var array
	 */
	private static $error_message_queue = array();

	/**
	 * Exception destination.
	 *
	 * @var string
	 */
	private static $exception_destination = 'html';

	/**
	 * Exception handler callback.
	 *
	 * @var mixed
	 */
	private static $exception_handler_callback = null;

	/**
	 * Exception handler callback parameters.
	 *
	 * @var array
	 */
	private static $exception_handler_parameters = array();

	/**
	 * The message generated by the uncaught exception.
	 *
	 * @var string
	 */
	private static $exception_message = null;

	/**
	 * If this class is handling errors.
	 *
	 * @var bool
	 */
	private static $handles_errors = false;

	/**
	 * If this class is handling exceptions.
	 *
	 * @var bool
	 */
	private static $handles_exceptions = false;

	/**
	 * If the context info should be shown with errors/exceptions.
	 *
	 * @var bool
	 */
	private static $show_context = true;

	/**
	 * An array of the most significant lines from error and exception backtraces.
	 *
	 * @var array
	 */
	private static $significant_error_lines = array();

	/**
	 * Creates a nicely formatted backtrace to the the point where this method is called.
	 *
	 * @param  int $remove_lines  The number of trailing lines to remove from the backtrace
	 * @param  array   $backtrace     A backtrace from [http://php.net/backtrace `debug_backtrace()`] to format - this is not usually required or desired
	 *
	 * @return string  The formatted backtrace
	 */
	public static function backtrace($remove_lines = 0, $backtrace = null)
	{
		if ($remove_lines !== null && !is_numeric($remove_lines)) {
			$remove_lines = 0;
		}

		settype($remove_lines, 'integer');

		$doc_root = realpath($_SERVER['DOCUMENT_ROOT']);
		$doc_root .= (substr($doc_root, -1) != DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '';

		if ($backtrace === null) {
			$backtrace = debug_backtrace();
		}

		while ($remove_lines > 0) {
			array_shift($backtrace);
			--$remove_lines;
		}

		$backtrace = array_reverse($backtrace);

		$bt_string = '';
		$i = 0;
		foreach ($backtrace as $call) {
			if ($i) {
				$bt_string .= "\n";
			}
			if (isset($call['file'])) {
				$bt_string .= str_replace($doc_root, '{doc_root}' . DIRECTORY_SEPARATOR, $call['file']) . '(' . $call['line'] . '): ';
			} else {
				$bt_string .= '[internal function]: ';
			}
			if (isset($call['class'])) {
				$bt_string .= $call['class'] . $call['type'];
			}
			if (isset($call['class']) || isset($call['function'])) {
				$bt_string .= $call['function'] . '(';
				$j = 0;
				if (!isset($call['args'])) {
					$call['args'] = array();
				}
				foreach ($call['args'] as $arg) {
					if ($j) {
						$bt_string .= ', ';
					}
					if (is_bool($arg)) {
						$bt_string .= ($arg) ? 'true' : 'false';
					} elseif (is_null($arg)) {
						$bt_string .= 'NULL';
					} elseif (is_array($arg)) {
						$bt_string .= 'Array';
					} elseif (is_object($arg)) {
						$bt_string .= 'Object(' . get_class($arg) . ')';
					} elseif (is_string($arg)) {
						// Shorten the UTF-8 string if it is too long
						if (strlen(utf8_decode($arg)) > 18) {
							// If we can't match as unicode, try single byte
							if (!preg_match('#^(.{0,15})#us', $arg, $short_arg)) {
								preg_match('#^(.{0,15})#s', $arg, $short_arg);
							}
							$arg = $short_arg[0] . '...';
						}
						$bt_string .= "'" . $arg . "'";
					} else {
						$bt_string .= (string)$arg;
					}
					++$j;
				}
				$bt_string .= ')';
			}
			++$i;
		}

		return $bt_string;
	}

	/**
	 * Performs a [http://php.net/call_user_func call_user_func()], while translating PHP 5.2 static callback syntax for PHP 5.1 and 5.0.
	 *
	 * Parameters can be passed either as a single array of parameters or as
	 * multiple parameters.
	 *
	 * {{{
	 * #!php
	 * // Passing multiple parameters in a normal fashion
	 * fCore::call('Class::method', TRUE, 0, 'test');
	 *
	 * // Passing multiple parameters in a parameters array
	 * fCore::call('Class::method', array(TRUE, 0, 'test'));
	 * }}}
	 *
	 * To pass parameters by reference they must be assigned to an
	 * array by reference and the function/method being called must accept those
	 * parameters by reference. If either condition is not met, the parameter
	 * will be passed by value.
	 *
	 * {{{
	 * #!php
	 * // Passing parameters by reference
	 * fCore::call('Class::method', array(&$var1, &$var2));
	 * }}}
	 *
	 * @param  callback $callback    The function or method to call
	 * @param  array    $parameters  The parameters to pass to the function/method
	 *
	 * @return mixed  The return value of the called function/method
	 */
	public static function call($callback, $parameters = array())
	{
		// Fix PHP 5.0 and 5.1 static callback syntax
		if (is_string($callback) && strpos($callback, '::') !== false) {
			$callback = explode('::', $callback);
		}

		$parameters = array_slice(func_get_args(), 1);
		if (sizeof($parameters) == 1 && is_array($parameters[0])) {
			$parameters = $parameters[0];
		}

		return call_user_func_array($callback, $parameters);
	}

	/**
	 * Translates a Class::method style static method callback to array style for compatibility with PHP 5.0 and 5.1 and built-in PHP functions.
	 *
	 * @param  callback $callback  The callback to translate
	 *
	 * @return array  The translated callback
	 */
	public static function callback($callback)
	{
		if (is_string($callback) && strpos($callback, '::') !== false) {
			return explode('::', $callback);
		}

		return $callback;
	}

	/**
	 * Checks an error/exception destination to make sure it is valid.
	 *
	 * @param  string $destination  The destination for the exception. An email, file or the string `'html'`.
	 *
	 * @return string|bool  `'email'`, `'file'`, `'html'` or `FALSE`
	 */
	private static function checkDestination($destination)
	{
		if ($destination == 'html') {
			return 'html';
		}

		if (preg_match('~^(?:                                                                         # Allow leading whitespace
						   (?:[^\x00-\x20\(\)<>@,;:\\\\"\.\[\]]+|"[^"\\\\\n\r]+")                     # An "atom" or a quoted string
						   (?:\.[ \t]*(?:[^\x00-\x20\(\)<>@,;:\\\\"\.\[\]]+|"[^"\\\\\n\r]+"[ \t]*))*  # A . plus another "atom" or a quoted string, any number of times
						  )@(?:                                                                       # The @ symbol
						   (?:[a-z0-9\\-]+\.)+[a-z]{2,}|                                              # Domain name
						   (?:(?:[01]?\d?\d|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d?\d|2[0-4]\d|25[0-5])    # (or) IP addresses
						  )
						  (?:\s*,\s*                                                                  # Any number of other emails separated by a comma with surrounding spaces
						   (?:
							(?:[^\x00-\x20\(\)<>@,;:\\\\"\.\[\]]+|"[^"\\\\\n\r]+")
							(?:\.[ \t]*(?:[^\x00-\x20\(\)<>@,;:\\\\"\.\[\]]+|"[^"\\\\\n\r]+"[ \t]*))*
						   )@(?:
							(?:[a-z0-9\\-]+\.)+[a-z]{2,}|
							(?:(?:[01]?\d?\d|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d?\d|2[0-4]\d|25[0-5])
						   )
						  )*$~xiD', $destination)) {
			return 'email';
		}

		$path_info = pathinfo($destination);
		$dir_exists = file_exists($path_info['dirname']);
		$dir_writable = ($dir_exists) ? is_writable($path_info['dirname']) : false;
		$file_exists = file_exists($destination);
		$file_writable = ($file_exists) ? is_writable($destination) : false;

		if (!$dir_exists || ($dir_exists && ((!$file_exists && !$dir_writable) || ($file_exists && !$file_writable)))) {
			return false;
		}

		return 'file';
	}

	/**
	 * Composes text using fText if loaded.
	 *
	 * @param  string  $message    The message to compose
	 * @param  mixed   $component  A string or number to insert into the message
	 * @param  mixed   ...
	 *
	 * @return string  The composed and possible translated message
	 */
	private static function compose($message)
	{
		$args = array_slice(func_get_args(), 1);

		return vsprintf($message, $args);
	}

	/**
	 * Creates a string representation of any variable using predefined strings for booleans, `NULL` and empty strings.
	 *
	 * The string output format of this method is very similar to the output of
	 * [http://php.net/print_r print_r()] except that the following values
	 * are represented as special strings:
	 *
	 *  - `TRUE`: `'{true}'`
	 *  - `FALSE`: `'{false}'`
	 *  - `NULL`: `'{null}'`
	 *  - `''`: `'{empty_string}'`
	 *
	 * @param  mixed $data  The value to dump
	 *
	 * @return string  The string representation of the value
	 */
	public static function dump($data)
	{
		if (is_bool($data)) {
			return ($data) ? '{true}' : '{false}';
		} elseif (is_null($data)) {
			return '{null}';
		} elseif ($data === '') {
			return '{empty_string}';
		} elseif (is_array($data) || is_object($data)) {
			ob_start();
			var_dump($data);
			$output = ob_get_contents();
			ob_end_clean();

			// Make the var dump more like a print_r
			$output = preg_replace('#=>\n(  )+(?=[a-zA-Z]|&)#m', ' => ', $output);
			$output = str_replace('string(0) ""', '{empty_string}', $output);
			$output = preg_replace('#=> (&)?NULL#', '=> \1{null}', $output);
			$output = preg_replace('#=> (&)?bool\((false|true)\)#', '=> \1{\2}', $output);
			$output = preg_replace('#(?<=^|\] => )(?:float|int)\((-?\d+(?:.\d+)?)\)#', '\1', $output);
			$output = preg_replace('#string\(\d+\) "#', '', $output);
			$output = preg_replace('#"(\n(  )*)(?=\[|\})#', '\1', $output);
			$output = preg_replace('#((?:  )+)\["(.*?)"\]#', '\1[\2]', $output);
			$output = preg_replace('#(?:&)?array\(\d+\) \{\n((?:  )*)((?:  )(?=\[)|(?=\}))#', "Array\n\\1(\n\\1\\2", $output);
			$output = preg_replace('/object\((\w+)\)#\d+ \(\d+\) {\n((?:  )*)((?:  )(?=\[)|(?=\}))/', "\\1 Object\n\\2(\n\\2\\3", $output);
			$output = preg_replace('#^((?:  )+)}(?=\n|$)#m', "\\1)\n", $output);
			$output = substr($output, 0, -2) . ')';

			// Fix indenting issues with the var dump output
			$output_lines = explode("\n", $output);
			$new_output = array();
			$stack = 0;
			foreach ($output_lines as $line) {
				if (preg_match('#^((?:  )*)([^ ])#', $line, $match)) {
					$spaces = strlen($match[1]);
					if ($spaces && $match[2] == '(') {
						$stack += 1;
					}
					$new_output[] = str_pad('', ($spaces) + (4 * $stack)) . $line;
					if ($spaces && $match[2] == ')') {
						$stack -= 1;
					}
				} else {
					$new_output[] = str_pad('', ($spaces) + (4 * $stack)) . $line;
				}
			}

			return join("\n", $new_output);
		} else {
			return (string)$data;
		}
	}

	/**
	 * Disables including the context information with exception and error messages.
	 *
	 * The context information includes the following superglobals:
	 *
	 *  - `$_SERVER`
	 *  - `$_POST`
	 *  - `$_GET`
	 *  - `$_SESSION`
	 *  - `$_FILES`
	 *  - `$_COOKIE`
	 */
	public static function disableContext()
	{
		self::$show_context = false;
	}

	/**
	 * Turns on a feature where undefined constants are automatically created with the string value equivalent to the name.
	 *
	 * This functionality only works if ::enableErrorHandling() has been
	 * called first. This functionality may have a very slight performance
	 * impact since a `E_STRICT` error message must be captured and then a
	 * call to [http://php.net/define define()] is made.
	 */
	public static function enableDynamicConstants()
	{
		if (!self::$handles_errors) {
			throw new fProgrammerException(
				'Dynamic constants can not be enabled unless error handling has been enabled via %s',
				__CLASS__ . '::enableErrorHandling()'
			);
		}
		self::$dynamic_constants = true;
	}

	/**
	 * Prints the ::dump() of a value.
	 *
	 * The dump will be printed in a `<pre>` tag with the class `exposed` if
	 * PHP is running anywhere but via the command line (cli). If PHP is
	 * running via the cli, the data will be printed, followed by a single
	 * line break (`\n`).
	 *
	 * If multiple parameters are passed, they are exposed as an array.
	 *
	 * @param  mixed $data  The value to show
	 * @param  mixed ...
	 */
	public static function expose($data)
	{
		$args = func_get_args();
		if (count($args) > 1) {
			$data = $args;
		}
		if (PHP_SAPI != 'cli') {
			echo '<pre class="exposed">' . htmlspecialchars((string)self::dump($data), ENT_QUOTES) . '</pre>';
		} else {
			echo self::dump($data) . "\n";
		}
	}

	/**
	 * Generates some information about the context of an error or exception.
	 *
	 * @return string  A string containing `$_SERVER`, `$_GET`, `$_POST`, `$_FILES`, `$_SESSION` and `$_COOKIE`
	 */
	public static function generateContext()
	{
		return self::compose('Context') . "\n-------" .
			"\n\n\$_SERVER: " . self::dump($_SERVER) .
			"\n\n\$_POST: " . self::dump($_POST) .
			"\n\n\$_GET: " . self::dump($_GET) .
			"\n\n\$_FILES: " . self::dump($_FILES) .
			"\n\n\$_SESSION: " . self::dump((isset($_SESSION)) ? $_SESSION : null) .
			"\n\n\$_COOKIE: " . self::dump($_COOKIE);
	}

	/**
	 * Handles an error, creating the necessary context information and sending it to the specified destination.
	 *
	 * @internal
	 *
	 * @param  int $error_number   The error type
	 * @param  string  $error_string   The message for the error
	 * @param  string  $error_file     The file the error occurred in
	 * @param  int $error_line     The line the error occurred on
	 * @param  array   $error_context  A references to all variables in scope at the occurence of the error
	 */
	public static function handleError($error_number, $error_string, $error_file = null, $error_line = null, $error_context = null)
	{
		if (self::$dynamic_constants && $error_number == E_NOTICE) {
			if (preg_match("#^Use of undefined constant (\w+) - assumed '\w+'\$#D", $error_string, $matches)) {
				define($matches[1], $matches[1]);

				return;
			}
		}

		$capturing = (bool)self::$captured_error_level;
		$level_match = (bool)(error_reporting() & $error_number);

		if (!$capturing && !$level_match) {
			return;
		}

		$doc_root = realpath($_SERVER['DOCUMENT_ROOT']);
		$doc_root .= (substr($doc_root, -1) != '/' && substr($doc_root, -1) != '\\') ? '/' : '';

		$backtrace = self::backtrace(1);

		// Remove the reference to handleError
		$backtrace = preg_replace('#: fCore::handleError\(.*?\)$#', '', $backtrace);

		$error_string = preg_replace('# \[<a href=\'.*?</a>\]: #', ': ', $error_string);

		switch ($error_number) {
			case E_WARNING:		   $type = self::compose('Warning'); break;
			case E_NOTICE:			$type = self::compose('Notice'); break;
			case E_USER_ERROR:		$type = self::compose('User Error'); break;
			case E_USER_WARNING:	  $type = self::compose('User Warning'); break;
			case E_USER_NOTICE:	   $type = self::compose('User Notice'); break;
			case E_STRICT:			$type = self::compose('Strict'); break;
			case E_RECOVERABLE_ERROR: $type = self::compose('Recoverable Error'); break;
			case E_DEPRECATED:		$type = self::compose('Deprecated'); break;
			case E_USER_DEPRECATED:   $type = self::compose('User Deprecated'); break;
			case E_CORE_ERROR:		$type = self::compose('Fatal Error'); break;
			case E_ERROR:			 $type = self::compose('Fatal Error'); break;
		}

		if ($capturing) {
			$type_to_capture = (bool)(self::$captured_error_types[self::$captured_error_level] & $error_number);
			$string_to_capture = !self::$captured_error_regex[self::$captured_error_level] || (self::$captured_error_regex[self::$captured_error_level] && preg_match(self::$captured_error_regex[self::$captured_error_level], $error_string));
			if ($type_to_capture && $string_to_capture) {
				self::$captured_errors[self::$captured_error_level][] = array(
					'number' => $error_number,
					'type' => $type,
					'string' => $error_string,
					'file' => str_replace($doc_root, '{doc_root}/', $error_file),
					'line' => $error_line,
					'backtrace' => $backtrace,
					'context' => $error_context
				);

				return;
			}

			// If the old handler is not this method, then we must have been trying to match a regex and failed
			// so we pass the error on to the original handler to do its thing
			if (self::$captured_errors_previous_handler[self::$captured_error_level] != array('fCore', 'handleError')) {
				if (self::$captured_errors_previous_handler[self::$captured_error_level] === null) {
					return false;
				}

				return call_user_func(self::$captured_errors_previous_handler[self::$captured_error_level], $error_number, $error_string, $error_file, $error_line, $error_context);

				// If we get here, this method is the error handler, but we don't want to actually report the error so we return
			} elseif (!$level_match) {
				return;
			}
		}

		$error = $type . "\n" . str_pad('', strlen($type), '-') . "\n" . $backtrace . "\n" . $error_string;

		$backtrace_lines = explode("\n", $backtrace);

		self::sendMessageToDestination('error', $error, end($backtrace_lines));
	}

	/**
	 * Resets the configuration of the class.
	 *
	 * @internal
	 */
	public static function reset()
	{
		if (self::$handles_errors) {
			restore_error_handler();
		}
		if (self::$handles_exceptions) {
			restore_exception_handler();
		}

		if (is_array(self::$captured_errors)) {
			restore_error_handler();
		}

		self::$captured_error_level = 0;
		self::$captured_error_regex = array();
		self::$captured_error_types = array();
		self::$captured_errors = array();
		self::$captured_errors_previous_handler = array();
		self::$context_shown = false;
		self::$dynamic_constants = false;
		self::$error_destination = 'html';
		self::$error_message_queue = array();
		self::$exception_destination = 'html';
		self::$exception_handler_callback = null;
		self::$exception_handler_parameters = array();
		self::$exception_message = null;
		self::$handles_errors = false;
		self::$handles_exceptions = false;
		self::$significant_error_lines = array();
		self::$show_context = true;
	}

	/**
	 * Sends an email or writes a file with messages generated during the page execution.
	 *
	 * This method prevents multiple emails from being sent or a log file from
	 * being written multiple times for one script execution.
	 *
	 * @internal
	 */
	public static function sendMessagesOnShutdown()
	{
		$messages = array();

		if (self::$error_message_queue) {
			$message = join("\n\n", self::$error_message_queue);
			$messages[self::$error_destination] = $message;
		}

		if (self::$exception_message) {
			if (isset($messages[self::$exception_destination])) {
				$messages[self::$exception_destination] .= "\n\n";
			} else {
				$messages[self::$exception_destination] = '';
			}
			$messages[self::$exception_destination] .= self::$exception_message;
		}

		$hash = md5(join('', self::$significant_error_lines), true);
		$hash = strtr(base64_encode($hash), '/', '-');
		$hash = substr(rtrim($hash, '='), 0, 8);

		$first_file_line = preg_replace(
			'#^.*[/\\\\](.*)$#',
			'\1',
			reset(self::$significant_error_lines)
		);

		$subject = self::compose(
			'[%1$s] %2$s error(s) beginning at %3$s {%4$s}',
			isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : php_uname('n'),
			count($messages),
			$first_file_line,
			$hash
		);

		foreach ($messages as $destination => $message) {
			if (self::$show_context) {
				$message .= "\n\n" . self::generateContext();
			}

			if (self::checkDestination($destination) == 'email') {
				mail($destination, $subject, $message);
			} else {
				$handle = fopen($destination, 'a');
				fwrite($handle, $subject . "\n\n");
				fwrite($handle, $message . "\n\n");
				fclose($handle);
			}
		}
	}

	/**
	 * Handles sending a message to a destination.
	 *
	 * If the destination is an email address or file, the messages will be
	 * spooled up until the end of the script execution to prevent multiple
	 * emails from being sent or a log file being written to multiple times.
	 *
	 * @param  string $type              If the message is an error or an exception
	 * @param  string $message           The message to send to the destination
	 * @param  string $significant_line  The most significant line from an error or exception backtrace
	 */
	private static function sendMessageToDestination($type, $message, $significant_line)
	{
		$destination = ($type == 'exception') ? self::$exception_destination : self::$error_destination;

		if ($destination == 'html') {
			if (self::$show_context && !self::$context_shown) {
				self::expose(self::generateContext());
				self::$context_shown = true;
			}
			self::expose($message);

			return;
		}

		static $registered_function = false;
		if (!$registered_function) {
			register_shutdown_function(self::callback(self::sendMessagesOnShutdown));
			$registered_function = true;
		}

		if ($type == 'error') {
			self::$error_message_queue[] = $message;
		} else {
			self::$exception_message = $message;
		}

		self::$significant_error_lines[] = $significant_line;
	}

	/**
	 * Temporarily enables capturing error messages.
	 *
	 * @param  int $types  The error types to capture - this should be as specific as possible - defaults to all (E_ALL | E_STRICT)
	 * @param  string  $regex  A PCRE regex to match against the error message
	 */
	public static function startErrorCapture($types = null, $regex = null)
	{
		if ($types === null) {
			$types = E_ALL | E_STRICT;
		}

		++self::$captured_error_level;

		self::$captured_error_regex[self::$captured_error_level] = $regex;
		self::$captured_error_types[self::$captured_error_level] = $types;
		self::$captured_errors[self::$captured_error_level] = array();
		self::$captured_errors_previous_handler[self::$captured_error_level] = set_error_handler(self::callback(self::handleError));
	}

	/**
	 * Stops capturing error messages, returning all that have been captured.
	 *
	 * @param  string $regex  A PCRE regex to filter messages by
	 *
	 * @return array  The captured error messages
	 */
	public static function stopErrorCapture($regex = null)
	{
		$captures = self::$captured_errors[self::$captured_error_level];

		--self::$captured_error_level;

		self::$captured_error_regex = array_slice(self::$captured_error_regex, 0, self::$captured_error_level, true);
		self::$captured_error_types = array_slice(self::$captured_error_types, 0, self::$captured_error_level, true);
		self::$captured_errors = array_slice(self::$captured_errors, 0, self::$captured_error_level, true);
		self::$captured_errors_previous_handler = array_slice(self::$captured_errors_previous_handler, 0, self::$captured_error_level, true);

		restore_error_handler();

		if ($regex) {
			$new_captures = array();
			foreach ($captures as $capture) {
				if (!preg_match($regex, $capture['string'])) {
					continue;
				}
				$new_captures[] = $capture;
			}
			$captures = $new_captures;
		}

		return $captures;
	}

	/**
	 * Forces use as a static class.
	 *
	 * @return fCore
	 */
	private function __construct()
	{
	}
}

/*
 * Copyright (c) 2007-2011 Will Bond <will@flourishlib.com>, others
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
