<?php

namespace App\Command;

use GuzzleHttp\Client;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReportCompletedCommand extends Command {

  protected static $defaultName = 'report:completed';

  /**
   * GetVideoUrlCommand constructor.
   *
   * @param \Pimple\Container $container
   */
  public function __construct(Container $container) {
    parent::__construct();
  }

  /**
   * @inheritDoc
   */
  protected function configure() {
    $this
      ->setDescription('Reports back to Virtual Y.')
      ->setHelp('This command allows you to report back to Virtual Y.');

    $this
      ->addArgument('video_id', InputArgument::REQUIRED, 'Result Vimeo Video ID');
  }

  /**
   * @inheritDoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $client = new Client();

    $uri = getenv('CALLBACK_URL');
    $data = [
      'eventinstance_id' => getenv('EVENT_INSTANCE_ID'),
      'status' => 'started',
      'details' => [
        'videoId' => $input->getArgument('video_id'),
        'hostName' => getenv('VY_HOST_NAME'),
        'categories' => json_decode(getenv('VY_CATEGORIES')),
        'equipment' => json_decode(getenv('VY_EQUIPMENT')),
        'level' => getenv('VY_LEVEL'),
        'duration' => getenv('DURATION'),
      ],
    ];

    $client->post($uri, [
      'auth' => ['fivejars', 'fivejars'],
      'json' => $data
    ]);

    return Command::SUCCESS;
  }
}
