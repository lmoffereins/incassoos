<?php

/**
 * Incassoos XML Exporter abstract class
 *
 * @package Incassoos
 * @subpackage Export
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Include dependencies
if ( ! class_exists( 'Incassoos_File_Exporter', false ) ) {
	require_once( incassoos()->includes_dir . 'classes/abstract-incassoos-file-exporter.php' );
}

if ( ! class_exists( 'Incassoos_XML_Exporter' ) ) :
/**
 * The Incassoos XML Exporter abstraction
 *
 * @since 1.0.0
 */
abstract class Incassoos_XML_Exporter extends Incassoos_File_Exporter {

	/**
	 * Holds the file type
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $file_type = 'inc_xml_file';

	/**
	 * Holds the file extension
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $file_extension = 'xml';

	/**
	 * Holds the filename
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $filename = 'incassoos-file.xml';

	/**
	 * Holds the XML document object
	 *
	 * @since 1.0.0
	 * @var DOMDocument object
	 */
	protected $xml;

	/**
	 * Holds the XML root tag name that contains all children
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $root_tag_name = 'Document';

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Init XML object
		$this->xml = new DOMDocument( '1.0', 'UTF-8' );
		$this->xml->formatOutput = true;
	}

	/** Validation ******************************************************/

	/**
	 * Validate file components and register any errors
	 *
	 * @since 1.0.0
	 *
	 * @return bool Is validation successfull?
	 */
	abstract public function validate_file();

	/** Export **********************************************************/

	/**
	 * Return the export file contents
	 *
	 * @since 1.0.0
	 *
	 * @return string File contents or False when invalid.
	 */
	public function get_file() {
		return $this->xml->saveXML();
	}

	/** Structure *******************************************************/

	/**
	 * Return the root tag's xmlns property value
	 *
	 * @since 1.0.0
	 *
	 * @return string Root tag xmlns
	 */
	public function get_xmlns() {
		return '';
	}

	/**
	 * Create the XML file structure
	 *
	 * @since 1.0.0
	 */
	public function build_file() {
		$this->setup_root_tag();
		$this->setup_file_tags();
	}

	/**
	 * Setup the file content tags
	 *
	 * @since 1.0.0
	 */
	abstract public function setup_file_tags();

	/**
	 * Return the file's root tag name
	 *
	 * @since 1.0.0
	 *
	 * @return string Root tag name. Defaults to 'Document'
	 */
	public function get_root_tag_name() {
		return $this->root_tag_name;
	}

	/**
	 * Setup the file's root tag
	 *
	 * @since 1.0.0
	 */
	public function setup_root_tag( $xmlns = '' ) {

		// Create root tag
		$root = $this->xml->createElement( $this->get_root_tag_name() );

		// Set xmlns properties
		if ( $xmlns = $this->get_xmlns() ) {
			$root->setAttribute( 'xmlns',     $xmlns );
			$root->setAttribute( 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance' );
		}

		// Add root to xml
		$this->xml->appendChild( $root );
	}

	/**
	 * Append new tags to an existing DOMElement
	 *
	 * @since 1.0.0
	 *
	 * @param array $args List of tags to append as $tag_name => $content
	 * @param DOMElement $root Element root for the new tags
	 */
	public function append_tags( $args = array(), $root = null ) {

		// Bail when the root tag is not a DOMElement
		if ( ! is_a( $root, 'DOMElement' ) )
			return;

		// Walk the tag list
		foreach ( array_filter( (array) $args ) as $tag => $content ) {

			// Create tag
			$tag = $this->xml->createElement( $tag );

			// Parse sub-tags
			if ( is_array( $content ) ) {
				$keys = array_keys( $content );

				// Add attributes and content
				if ( is_numeric( $keys[0] ) ) {

					// Set tag content
					$tag->nodeValue = trim( $content[ $keys[0] ] );
					unset( $content[ $keys[0] ] );

					// Add attributes
					foreach ( $content as $attr => $value ) {
						if ( strlen( trim( $value ) ) ) {
							$tag->setAttribute( $attr, trim( $value ) );
						}
					}

				// Append child tags
				} else {
					$tag = $this->append_tags( $content, $tag );
				}

			// Parse tag content
			} elseif ( strlen( trim( $content ) ) ) {
				$tag->nodeValue = trim( $content );
			}

			// Add tag to root
			$root->appendChild( $tag );
		}

		return $root;
	}

	/**
	 * Append the tag to the XML document at the given path
	 *
	 * @since 1.0.0
	 *
	 * @param DOMElement $tag Element to append
	 * @param string $path Optional. Path to query beyond root.
	 */
	public function append_tag( $tag, $path = '' ) {

		// Define path at root/{path}
		$path = empty( $path ) ? '' : '/' . trim( $path, '/' );
		$path = '//' . $this->get_root_tag_name() . $path;

		// Query path, add tag
		$xpath = new DOMXPath( $this->xml );
		$xpath->query( $path )->item( 0 )->appendChild( $tag );
	}
}

endif; // class_exists
