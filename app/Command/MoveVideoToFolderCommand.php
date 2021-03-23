<?php

namespace App\Command;

use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MoveVideoToFolderCommand extends Command {

  protected static $defaultName = 'video:move_to_folder';

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
      ->setDescription('Moves a video to a folder.')
      ->setHelp('This command allows you to move an uploaded video to the vimeo folder.');

    $this
      ->addArgument('video_id', InputArgument::REQUIRED, 'Vimeo Video ID')
      ->addArgument('folder_id', InputArgument::REQUIRED, 'Vimeo Folder/Project ID');
  }

  /**
   * @inheritDoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $video_id = $input->getArgument('video_id');
    $folder_id = $input->getArgument('folder_id');

    $url = '/me/projects/' . $folder_id . '/videos/' . $video_id;
    $this->vimeo->request($url, [], 'PUT');

    return Command::SUCCESS;
  }
}
