<?php

namespace App\Command;

use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetVideoUrlCommand extends Command {

  protected static $defaultName = 'video:url';

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
      ->addArgument('video_id', InputArgument::REQUIRED, 'Vimeo Video ID');
  }

  /**
   * @inheritDoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $video_id = $input->getArgument('video_id');
    $api_url = '/videos/' . $video_id;

    $response = $this->vimeo->request($api_url, [], 'GET');
    if (empty($response['body']['files'])) {
      $output->writeln('Bad response');
      if (!empty($response['body']['error'])) {
        $output->writeln($response['body']['error']);
      }
      return Command::FAILURE;
    }

    $video_file = array_filter($response['body']['download'], function ($e) {
      return $e['type'] == 'video/mp4' && $e['height'] == 1080;
    });

    if (!$video_file) {
      $output->writeln('No FullHD video found');
      return Command::FAILURE;
    }

    $video_file = reset($video_file);
    $output->writeln($video_file['link']);

    return Command::SUCCESS;
  }
}
