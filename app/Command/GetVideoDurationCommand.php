<?php

namespace App\Command;

use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetVideoDurationCommand extends Command {

  protected static $defaultName = 'video:duration';

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
      ->setDescription('Extracts video duration by the Vimeo video ID.')
      ->setHelp('This command allows you to extract video duration for a given Vimeo video by its identifier.');

    $this
      ->addArgument('video_id', InputArgument::REQUIRED, 'Vimeo Video ID');
  }

  /**
   * @inheritDoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $video_id = $input->getArgument('video_id');
    $api_url = '/videos/' . $video_id;

    $response = $this->vimeo->request($api_url, [], 'GET');
    if (empty($response['body']['duration'])) {
      if (!empty($response['body']['error'])) {
        $output->writeln($response['body']['error']);
      }
      else {
        $output->writeln('Wrong response');
      }
      return Command::FAILURE;
    }

    $output->writeln($response['body']['duration']);
    return Command::SUCCESS;
  }
}
