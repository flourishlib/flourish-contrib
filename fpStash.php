<?php
/**
 * Adds a ::stash() method to fActiveRecord that will store the record in the
 * session and redirect to a URL. fpStash::unstash() retrieves the record from
 * the session.
 * 
 * @copyright  Copyright (c) 2010 Will Bond
 * @author     Will Bond [wb] <will@flourishlib.com>
 * @license    http://flourishlib.com/license
 *
 * @requires Flourish r912
 * @requires Moor 1.0.0b7
 * 
 * @version    1.0.0
 * @changes    1.0.0  The initial implementation [wb, 2010-09-22]
 */
class fpStash
{
	/**
	 * Adds the fActiveRecord::stash() method
	 * 
	 * @param  mixed $class  The class name or fActiveRecord object 
	 * @return void
	 */
	static public function extend()
	{
		fORM::registerActiveRecordMethod(
			'*',
			'stash',
			'fpStash::stash'
		);
		
		fORM::registerReflectCallback(
			'*', 
			'fpStash::reflect'
		);
	}
	
	
	/**
	 * Returns the user to the page where the record was stashed before arriving
	 * at the current page
	 * 
	 * @return void
	 */
	static public function getOrigin()
	{
		return fSession::delete('fpStash::return_url::' . Moor::getActiveCallback());
	}
	
	
	/**
	 * Saves the current record and redirect to a Moor callback
	 * 
	 * @internal
	 * 
	 * @param  fActiveRecord $object            The fActiveRecord instance
	 * @param  array         &$values           The current values
	 * @param  array         &$old_values       The old values
	 * @param  array         &$related_records  Any records related to this record
	 * @param  array         &$cache            The cache array for the record
	 * @param  string        $method_name       The method that was called
	 * @param  array         $parameters        The parameters passed to the method
	 * @return string  The URL requested
	 */
	static public function stash($object, &$values, &$old_values, &$related_records, &$cache, $method_name, $parameters)
	{
		$callback = $parameters[0];
		
		$expanded_callback = $callback;
		if (strpos($callback, '*::') === 0) {
			$expanded_callback = Moor::getActiveClass() . substr($callback, 1);
		}
		if (strpos($callback, '*\\') === 0 || preg_match('/^\*_[A-Z][A-Za-z0-9]*::/', $callback)) {
			$expanded_callback = Moor::getActiveNamespace() . substr($callback, 1);
		}
		
		fSession::set('fpStash::record::' . Moor::getActiveCallback(), $object);
		fSession::set('fpStash::return_url::' . $expanded_callback, fURL::getWithQueryString());
		fURL::redirect(Moor::linkTo($callback));
	}
	
	
	/**
	 * Adjusts the fActiveRecord::reflect() signatures of columns that have been added by this class
	 * 
	 * @internal
	 * 
	 * @param  string  $class                 The class to reflect
	 * @param  array   &$signatures           The associative array of `{method name} => {signature}`
	 * @param  boolean $include_doc_comments  If doc comments should be included with the signature
	 * @return void
	 */
	static public function reflect($class, &$signatures, $include_doc_comments)
	{
		$signature = '';
		if ($include_doc_comments) {
			$signature .= "/**\n";
			$signature .= " * Saves the record in the session and redirects a user to a Moor callback\n";
			$signature .= " * \n";
			$signature .= " * @param  string \$callback_string  A callback string of a Moor route to redirect\n";
			$signature .= " * @return void\n";
			$signature .= " */\n";
		}
		$signature .= 'public function stash($callback_string)';
		
		$signatures['stash'] = $signature;
	}
	
	
	/**
	 * Returns a stashed record if present
	 * 
	 * @return fActiveRecord|NULL  The stashed record
	 */
	static public function unstash()
	{
		return fSession::delete('fpStash::record::' . Moor::getActiveCallback());
	}
	
	
	/**
	 * Forces use as a static class
	 * 
	 * @return fpStash
	 */
	private function __construct() { }
}


/**
 * Copyright (c) 2010 Will Bond <will@flourishlib.com>
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