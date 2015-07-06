<?php
/**
 * File containing the Convert Command which converts a URL to a single HTML resources
 *
 * @author Ron Rademaker
 * @since 1-jul-2015
 */
namespace RonRademaker\PagePacker\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RonRademaker\PagePacker\Converter\URLConverter;

/**
 * ConvertCommand is a symfony based command to convert a URL to a single HTML resource, this is useful for errorpages that shouldn't depend on anything
 *
 * @author Ron Rademaker
 * @since 1-jul-2015
 */
class ConvertCommand extends Command
{
	/**
	 * configure
	 *
	 * Sets the command variables and it's inputs
	 *
	 * @since 1-jul-2015
	 * @access protected
	 * @return void
	 **/
	protected function configure() {
		$this->setName('convert:page');
		$this->addArgument('url', InputArgument::REQUIRED, 'The URL (use FQDN) to convert');
	}

	/**
	 * execute
	 *
	 * Describe here what the function should do
	 *
	 * @since 1-jul-2015
	 * @access protected
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return void
	 **/
	protected function execute(InputInterface $input, OutputInterface $output) {
		$url = $input->getArgument('url');

		$converter = new URLConverter($url);

		$output->writeln($converter->getPackedHTML() );

	}
}
