<?php

namespace App\Command;

use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UploadVideoCommand extends Command {

  protected static $defaultName = 'video:upload';

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
      ->setDescription('Uploads a video to the folder.')
      ->setHelp('This command allows you to upload a local video to the vimeo folder.');

    $this
      ->addArgument('path_to_file', InputArgument::REQUIRED, 'Path to video file')
      ->addArgument('video_name', InputArgument::OPTIONAL, 'Video name');
  }

  /**
   * @inheritDoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $path_to_file = $input->getArgument('path_to_file');
    $name = $input->getArgument('video_name');

    if (!file_exists($path_to_file)) {
      return Command::FAILURE;
    }

    $response =  $this->vimeo->upload($path_to_file, [
      'name' => $name ? $name : 'Automatically created video',
    ]);
    $video_id = str_replace('/videos/', '', $response);

    $output->writeln($video_id);

    return Command::SUCCESS;
  }
}
