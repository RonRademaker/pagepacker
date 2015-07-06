<?php
namespace RonRademaker\PagePacker\Converter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\Response;
use RonRademaker\PagePacker\Exceptions\ContentTypeNotSupportedException;

/**
 * Converter that takes a URL as input
 *
 * @author Ron Rademaker
 * @since 1-jul-2015
 */
class URLConverter implements ConverterInterface {
	/**
	 * The URL to download the page from
	 *
	 * @since 1-jul-2015
	 * @access private
	 * @var string
	 */
	private $url;

	/**
	 * __construct
	 *
	 * Constructs a converter for $input, which should be a $url where we can download the page to pack
	 *
	 * @since 1-jul-2015
	 * @access public
	 * @param $input
	 * @return void
	 **/
	public function __construct($input) {
		$this->url = $input;

	}

	/**
	 * getPackedHTML
	 *
	 * Packs the input in a single HTML page
	 *
	 * @since 1-jul-2015
	 * @access public
	 * @return void
	 **/
	public function getPackedHTML() {
		$content = $this->getContentFromURL($this->url);
		$converter = $this->getConverter($content->getBody()->getContents(), $content->getHeader('Content-Type') );
		return $converter->getPackedHTML();
	}

	/**
	 * getContentFromURL
	 *
	 * Downloads URL into a variable
	 *
	 * @since 1-jul-2015
	 * @access private
	 * @return Response
	 **/
	private function getContentFromURL($url) {
		try {
			$client = new Client();
			$response = $client->get($url);
			return $response;
		}
		catch (ClientException $e) {
			return $e->getResponse();
		}
	}

	/**
	 * getConverter
	 *
	 * Gets a converter for $type to convert $content
	 *
	 * @since 1-jul-2015
	 * @access private
	 * @param string $content
	 * @param string $type
	 * @return ConverterInterface
	 **/
	private function getConverter($content, $type) {
		switch ($type) {
			case "text/html":
				$converter = new HTMLConverter($content);
				$converter->setBaseURL(parse_url($this->url, PHP_URL_SCHEME) . "://" . parse_url($this->url, PHP_URL_HOST) );
				return $converter;
				break;
			default:
				throw new ContentTypeNotSupportedException("Content of type '{$type}', downloaded from '{$this->url}' is not supported");
		}
	}
}
