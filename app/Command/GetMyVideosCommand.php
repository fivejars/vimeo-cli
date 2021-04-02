<?php

namespace App\Command;

use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetMyVideosCommand extends Command {

  protected static $defaultName = 'video:my';

  /**
   * @var \Vimeo\Vimeo $vimeo
   *
   * The Vimeo client.
   */
  private $vimeo;

  /**
   * GetVideoUrlCommand constructor.
   *
   * @param \Pimple\Container $container
   */
  public function __construct(Container $container) {
    parent::__construct();

    $this->vimeo = $container['vimeo.client'];
  }

  /**
   * @inheritDoc
   */
  protected function configure() {
    $this
      ->setDescription('Extracts video URL by the Vimeo video id.')
      ->setHelp('This command allows you to extract video file URL for a given Vimeo video by its identifier.');

    $this
      ->addArgument('query', InputArgument::REQUIRED, 'Query');
  }

  /**
   * @inheritDoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $query = $input->getArgument('query');
    $api_url = '/me/videos';

    $response = $this->vimeo->request($api_url, [
      'query' => $query,
      'per_page' => 10,
      'sort' => 'date',
      'direction' => 'desc',
    ], 'GET');

//    $output->writeln(print_r($response['body'], 1));
    $output->writeln(print_r($response, 1));

    return Command::SUCCESS;
  }
}
