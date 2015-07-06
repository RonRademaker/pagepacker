<?php
/**
 * File containing a converter which accepts HTML as input
 *
 * @author Ron Rademaker
 * @since 1-jul-2015
 */
namespace RonRademaker\PagePacker\Converter;

use DOMDocument;
use DOMElement;
use DOMNode;
use GuzzleHttp\Client;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;


/**
 * Description of newPHPClass
 *
 * @author Ron Rademaker
 * @since 1-jul-2015
 */
class HTMLConverter implements ConverterInterface {
	/**
	 * Input HTML
	 *
	 * @var string
	 * @access private
	 * @since 1-jul-2015
	 */
	private $html;

	/**
	 * Base URL to use for relative URLs in the HTML
	 *
	 * @var string
	 */
	private $baseUrl = "";

	/**
	 * Dom with the HTML to pack
	 *
	 * @var DOMDocument
	 */
	private $dom;

	/**
	 * Array with nodes to delete, do this at the end because it interferes with iterations otherwise
	 *
	 * @var array
	 */
	private $deleteNodes = [];

	/**
	 * String with all CSS to apply
	 *
	 * @var string
	 */
	private $css = "";

	/**
	 * __construct
	 *
	 * Constructs a converter for $input, which should be a full HTML page in a string
	 *
	 * @since 1-jul-2015
	 * @access public
	 * @param $input
	 * @return void
	 **/
	public function __construct($input) {
		$this->html = $input;
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
		$this->dom = new DOMDocument();
		$this->dom->loadHTML($this->html);
		$this->pack($this->dom);
		return $this->applyCSS($this->dom->saveHTML(), $this->css);
	}

	/**
	 * applyCSS
	 *
	 * Describe here what the function should do
	 *
	 * @since 1-jul-2015
	 * @access private
	 * @return applyCSS
	 **/
	private function applyCSS($html, $css) {
		$cssToInlineStyles = new CssToInlineStyles();

		$cssToInlineStyles->setHTML($html);
		$cssToInlineStyles->setCSS($css);

		return $cssToInlineStyles->convert();
	}


	/**
	 * pack
	 *
	 * Packs the HTML in $dom into a single DOM
	 *
	 * @since 1-jul-2015
	 * @access private
	 * @param DOMDocument $dom
	 * @return void
	 **/
	private function pack(DOMDocument $dom) {
		foreach ($dom->childNodes as $childNode) {
			$this->packNode($childNode);
		}
		foreach ($this->deleteNodes as $deleteNode) {
			$deleteNode->parentNode->removeChild($deleteNode);
		}
	}

	/**
	 * packNode
	 *
	 * Packs a node
	 *
	 * @since 1-jul-2015
	 * @access private
	 * @param DOMNode $node
	 * @return void
	 **/
	private function packNode(DOMNode $node) {
		if ($node instanceof DOMElement) { // nothing to pack for other nodes
			switch (strtolower($node->tagName) ) {
				case "link":
					$this->packLinkNode($node);
					break;
				case "img":
					$this->packImageNode($node);
					break;
			}

			foreach ($node->childNodes as $childNode) {
				$this->packNode($childNode);
			}
		}
	}

	/**
	 * packImageNode
	 *
	 * Packs a img node by putting the base64 inline
	 *
	 * @since 1-jul-2015
	 * @access private
	 * @param DOMElement $node
	 * @return void
	 **/
	private function packImageNode(DOMElement $node) {
		if ($node->hasAttribute("src") ) {
			$src = $node->getAttribute("src");
			if (strpos($src, "data:image") === false) {
				$client = new Client();
				$fullSrc = (strpos($src, "http") === 0) ? $src : $this->baseUrl . $src;
				$img = $client->get($fullSrc);
				$node->setAttribute("src", "data:" . $img->getHeader('Content-Type') . ';base64,' . base64_encode($img->getBody()->getContents() ) );
			}
		}
	}

	/**
	 * packLinkNode
	 *
	 * Packs a link node by replacing it with inline css
	 *
	 * @since 1-jul-2015
	 * @access private
	 * @param DOMElement $node
	 * @return void
	 **/
	private function packLinkNode(DOMElement $node) {
		if ($node->hasAttribute('href') && !$node->hasAttribute('media') ) {
			$href = $node->getAttribute('href');
			$client = new Client();
			$response = $client->get($this->baseUrl . $href);
			$cssConverter = new CSSConverter($response->getBody()->getContents() );
			$cssConverter->setBaseUrl($this->baseUrl);
			$this->css .= $cssConverter->getPackedHTML() . "\n";
			$this->deleteNodes[] = $node;
		}
	}
}

