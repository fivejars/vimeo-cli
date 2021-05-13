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
   * ReportCompletedCommand constructor.
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
    $video_id = $input->getArgument('video_id');
    $this->waitForProcessing($video_id, intval(getenv('DURATION')));

    $uri = getenv('CALLBACK_URL');
    $data = [
      'token' => getenv('AUTH_TOKEN'),
      'eventinstance_id' => getenv('EVENT_INSTANCE_ID'),
      'status' => 'completed',
      'details' => [
        'videoId' => $video_id,
        'videoName' => getenv('VIDEO_NAME'),
        'hostName' => getenv('VY_HOST_NAME'),
        'categories' => json_decode(getenv('VY_CATEGORIES')),
        'equipment' => json_decode(getenv('VY_EQUIPMENT')),
        'level' => getenv('VY_LEVEL'),
        'duration' => getenv('DURATION'),
      ],
    ];

    $options = [ 'json' => $data ];
    if (getenv('AUTH_USER') || getenv('AUTH_PASS')) {
      $options['auth'] = [getenv('AUTH_USER'), getenv('AUTH_PASS')];
    }
    $client->post($uri, $options);

    return Command::SUCCESS;
  }

  /**
   * Waits for the video to be processed on the Vimeo side.
   *
   * @param int $video_id
   *   The ID of a Vimeo video.
   * @param int $timeout
   *   The timeout in seconds.
   */
  private function waitForProcessing($video_id, $timeout) {
    $start = microtime(TRUE);
    while (!$this->verifyOembed($video_id)) {
      // Do not spend more than $timeout seconds.
      if (microtime(TRUE) - $start >= $timeout) {
        break;
      }
      sleep(60);
    }
  }

  /**
   * Verifies that Vimeo oembed endpoint returns value.
   *
   * @param int $video_id
   *   The ID of a Vimeo video.
   *
   * @return bool
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function verifyOembed($video_id) {
    $client = new Client();
    $url = 'https://vimeo.com/' . $video_id;
    $response = $client->get('https://vimeo.com/api/oembed.json', [
      'query' => ['url' => $url],
      'http_errors' => FALSE,
      'timeout' => 10,
    ]);
    return $response->getStatusCode() == 200;
  }

}
