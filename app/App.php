<?php

namespace App;

use App\Command\GetVideoDurationCommand;
use App\Command\GetVideoUrlCommand;
use Pimple\Container;
use Symfony\Component\Console\Application;

/**
 * Class App
 *
 * @package App
 */
class App {

  /**
   * @var \Pimple\Container $container
   *
   * The Dependency Injection container.
   */
  private $container;

  /**
   * @var \Vimeo\Vimeo $vimeo
   *
   * The Vimeo client.
   */
  private $vimeo;

  /**
   * App constructor.
   */
  public function __construct() {
    $this->container = new Container();
    $this->container->register(new ServiceProvider());

    $this->application = new Application();
    $this->application->add(new GetVideoUrlCommand($this->container));
    $this->application->add(new GetVideoDurationCommand($this->container));

    $this->vimeo = $this->container['vimeo.client'];
  }

  public function run() {
    $this->application->run();
  }

}
