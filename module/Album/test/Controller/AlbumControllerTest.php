<?php

namespace AlbumTest\Controller;

use Album\Controller\AlbumController;
use Album\Model\Album;
use Album\Model\AlbumTable;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class AlbumControllerTest extends AbstractHttpControllerTestCase
{
    use ProphecyTrait;

    protected $traceError = true;

    protected $albumTable;

    protected function setUp(): void
    {
        // The module configuration should still be applicable for tests.
        // You can override configuration here with test case specific values,
        // such as sample view templates, path stacks, module_listener_options,
        // etc.
        $configOverrides = [];

        $this->setApplicationConfig(ArrayUtils::merge(
            // Grabbing the full application configuration:
            include __DIR__ . '/../../../../config/application.config.php',
            $configOverrides
        ));
        parent::setUp();

        $this->configureServiceManager($this->getApplicationServiceLocator());
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->albumTable->fetchAll()->willReturn([]);

        $this->dispatch('/album');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Album');
        $this->assertControllerName(AlbumController::class);
        $this->assertControllerClass('AlbumController');
        $this->assertMatchedRouteName('album');
    }

    public function testAddActionRedirectsAfterValidPost()
    {
        $this->albumTable
            ->saveAlbum(Argument::type(Album::class))
            ->shouldBeCalled();

        $postData = [
            'title'  => 'Led Zeppelin III',
            'artist' => 'Led Zeppelin',
            'id'     => '',
        ];
        $this->dispatch('/album/add', 'POST', $postData);
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/album');
    }

    public function testNonPostToAdd()
    {
        $this->albumTable
            ->saveAlbum(Argument::type(Album::class))
            ->shouldNotBeCalled();

        $postData = [
            'title'  => 'Led Zeppelin III',
            'artist' => 'Led Zeppelin',
            'id'     => '',
        ];
        $this->dispatch('/album/add', 'GET', $postData);
        $this->assertResponseStatusCode(200);
        $this->assertXpathQuery( '//input[@name="title"][@value=""]' );
        $this->assertXpathQuery( '//input[@name="artist"][@value=""]' );
        $this->assertNotXpathQuery( '//ul[@class="help-block"]/li');
    }

    public function testEmptyData()
    {
        $this->albumTable
            ->saveAlbum(Argument::type(Album::class))
            ->shouldNotBeCalled();

        $postData = [];
        $this->dispatch('/album/add', 'POST', $postData);
        $this->assertResponseStatusCode(200);
        $this->assertXpathQuery( '//input[@name="title"][@value=""]' );
        $this->assertXpathQuery( '//input[@name="artist"][@value=""]' );
        $this->assertXpathQuery( '//ul[@class="help-block"]/li');
        $this->assertXpathQuery( '//ul[@class="help-block"][2]/li');
    }

    public function testRedirectIfNoIdentifier()
    {
        $this->albumTable
            ->saveAlbum(Argument::type(Album::class))
            ->shouldNotBeCalled();

        $this->dispatch('/album/delete', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/album');

        $this->dispatch('/album/edit', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/album');
    }

    // Test that an invalid identifier passed to editAction() will redirect to the album landing page.
    // Test that non-POST requests to editAction() and deleteAction() display forms.

    protected function configureServiceManager(ServiceManager $services)
    {
        $services->setAllowOverride(true);

        $services->setService('config', $this->updateConfig($services->get('config')));
        $services->setService(AlbumTable::class, $this->mockAlbumTable()->reveal());

        $services->setAllowOverride(false);
    }

    protected function updateConfig($config)
    {
        $config['db'] = [];
        return $config;
    }

    protected function mockAlbumTable()
    {
        $this->albumTable = $this->prophesize(AlbumTable::class);
        return $this->albumTable;
    }
}
