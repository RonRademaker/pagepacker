<?php
namespace RonRademaker\PagePacker\Converter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Value\String;
use Sabberworm\CSS\Value\URL;

/**
 *V Converter to CSS, replaces images with base 64 versions
 *
 * @author Ron Rademaker
 * @since 1-jul-2015
 */
class CSSConverter implements ConverterInterface {
	private $css;

	/**
	 * Base URL to use for relative URLs in the HTML
	 *
	 * @var string
	 */
	private $baseUrl = "";

	public function __construct($input) {
		$this->css = $input;
	}

	/**
	 * setBaseURL
	 *
	 * Sets the base URL to use for relative URLs in the HTML
	 *
	 * @since 1-jul-2015
	 * @access public
	 * @param string $baseUrl
	 * @return void
	 **/
	public function setBaseURL($baseUrl) {
		$this->baseUrl = $baseUrl;
	}

	public function getPackedHTML() {
		$parser = new Parser($this->css);
		$cssDoc = $parser->parse();
		foreach($cssDoc->getAllValues() as $value) {
			if ($value instanceof URL) {
				$client = new Client();
				$url = trim($value->getUrl(), '"');
				$fullSrc = (strpos($url, "http") === 0) ? $url : ($this->baseUrl . $url);
				try {
					$response = $client->get($fullSrc);
					$value->setURL(new String('data:' . $response->getHeader('Content-Type') . ';base64,' . base64_encode($response->getBody()->getContents() ) ) );
				}
				catch (ClientException $e) {

				}
			}
		}
		return $cssDoc->render();
	}
}
