<?php
namespace RonRademaker\PagePacker\Converter;

/**
 * Interface to define the structure of converters
 *
 * A converter should accept some input format
 *
 * @author Ron Rademaker
 */
interface ConverterInterface {
	/**
	 * __construct
	 *
	 * Constructs a converter for $input
	 *
	 * @since 1-jul-2015
	 * @access public
	 * @return void
	 **/
	public function __construct($input);

	/**
	 * getPackedHTML
	 *
	 * Packs the input in a single HTML page
	 *
	 * @since 1-jul-2015
	 * @access public
	 * @return void
	 **/
	public function getPackedHTML();
}