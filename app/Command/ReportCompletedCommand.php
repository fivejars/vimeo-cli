<?php

namespace App\Command;

use GuzzleHttp\Client;
use Pimple\Container;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReportCompletedCommand extends Command {

  protected static $defaultName = 'report:completed';
  protected static $vimeoOembedEndpoint = 'https://vimeo.com/api/oembed.json';

  /**
   * The OEmbed data.
   *
   * @var mixed $oEmbedData
   */
  private $oEmbedData = NULL;

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
    $this->waitForProcessing($video_id);

    $uri = getenv('CALLBACK_URL');
    $data = [
      'token' => getenv('AUTH_TOKEN'),
      'eventinstance_id' => getenv('EVENT_INSTANCE_ID'),
      'status' => 'completed',
      'details' => [
        'videoId' => $video_id,
        'videoName' => getenv('VIDEO_NAME'),
        'videoDate' => getenv('EVENT_DATE'),
        'thumbnailType' => getenv('THUMBNAIL_TYPE'),
        'thumbnailUrl' => $this->getThumbnailUrl(),
        'hostName' => getenv('VY_HOST_NAME'),
        'categories' => json_decode(getenv('VY_CATEGORIES')),
        'equipment' => json_decode(getenv('VY_EQUIPMENT')),
        'level' => getenv('VY_LEVEL'),
        'duration' => $this->oEmbedData['duration'],
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
   * Returns the thumbnail URL.
   *
   * @return mixed
   *   The thumbnail URL or false.
   */
  private function getThumbnailUrl() {
    switch (getenv('THUMBNAIL_TYPE')) {
      case 'generate':
      case 'vimeo':
        $result = getenv('THUMBNAIL_URL') ?: FALSE;
        break;

      default:
        $result = FALSE;
        break;
    }
    return $result;
  }

  /**
   * Waits for the video to be processed on the Vimeo side.
   *
   * @param int $video_id
   *   The ID of a Vimeo video.
   * @param int $timeout
   *   The timeout in seconds.
   */
  private function waitForProcessing($video_id, $timeout = 7200) {
    $start = microtime(TRUE);
    while (!$this->getVideoOembedData($video_id)) {
      // Do not spend more than $timeout seconds.
      if (microtime(TRUE) - $start >= $timeout) {
        break;
      }
      sleep(60);
    }
  }

  /**
   * Requests Vimeo oEmbed endpoint and returns the response.
   *
   * @param int $video_id
   *   The ID od a Vimeo video.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   */
  private function getVideoOembedResponse($video_id): ResponseInterface {
    $client = new Client();
    $url = 'https://vimeo.com/' . $video_id;
    return $client->request('GET', static::$vimeoOembedEndpoint, [
      'query' => ['url' => $url],
      'http_errors' => FALSE,
      'timeout' => 10,
    ]);
  }

  /**
   * Returns the Vimeo video oEmbed data.
   *
   * @param int $video_id
   *   The ID od a Vimeo video.
   *
   * @return mixed
   *   The response array or NULL.
   */
  private function getVideoOembedData($video_id) {
    if (!$this->oEmbedData) {
      $response = $this->getVideoOembedResponse($video_id);
      if ($response->getStatusCode() != 200) {
        return NULL;
      }
      if (!$content = json_decode($response->getBody()->getContents(), TRUE)) {
        return NULL;
      }
      $this->oEmbedData = $content;
    }

    return $this->oEmbedData;
  }

}
