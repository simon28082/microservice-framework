<?php

namespace CrCms\Microservice\Tests\Server\Packer;

use CrCms\Microservice\Server\Packer\Packer;
use CrCms\Microservice\Testing\TestCase;

/**
 * Class PackerTest.
 */
class PackerTest extends TestCase
{
    /**
     * @var Packer
     */
    protected $packer;

    public function setUp()
    {
        parent::setUp();

        config([
//            'app.secret' => '#1#2@!##',
//            'app.secret_cipher' => 'AES-256-CFB',
            'app.encryption' => true,
            'app.key'        => 'base64:TdhCcU88SAr4/omVl4ckItqqKUx+whSwQ4gVEtlm+xk=',
        ]);

        $this->packer = $this->app->make(Packer::class);
    }

    public function testEmptyValuePack()
    {
        $data = ['call' => 'test', 'data'=>['x'=>1]];
        $result = $this->packer->pack($data);

        $this->assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testEmptyValuePack
     */
    public function testEmptyValueUnpack(string $data)
    {
        $data = $this->packer->unpack($data);
        $this->assertArrayHasKey('call', $data);
        $this->assertEquals('test', $data['call']);
        $this->assertEquals(['x'=>1], $data['data']);
    }

    public function testNotEmptyPack()
    {
        $data = ['call' => 'test', 'data' => ['key' => 'value']];
        $value = $this->packer->pack($data, true);
        $this->assertNotEmpty($value);

        return $value;
    }

    /**
     * @depends testNotEmptyPack
     */
    public function testNotEmptyValueUnpack(string $value)
    {
        $data = $this->packer->unpack($value, true);
        $this->assertArrayHasKey('call', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('test', $data['call']);
        $this->assertEquals('value', $data['data']['key']);
    }

    public function testNotEncryptPack()
    {
        $data = ['call' => 'test', 'data' => ['key' => 'value']];
        $value = $this->packer->pack($data, false);
        $this->assertNotEmpty($value);

        return $value;
    }

    /**
     * @depends testNotEncryptPack
     *
     * @param string $value
     */
    public function testNotEncryptUnpack(string $value)
    {
        $data = $this->packer->unpack($value, false);
        $this->assertArrayHasKey('call', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('test', $data['call']);
        $this->assertEquals('value', $data['data']['key']);
    }

    public function testEmptyNotEncryptPack()
    {
        $data = ['call' => 'test'];
        $value = $this->packer->pack($data, false);
        $this->assertNotEmpty($value);

        return $value;
    }

    /**
     * @depends testEmptyNotEncryptPack
     *
     * @param $value
     */
    public function testEmptyNotEncryptUnpack($value)
    {
        $data = $this->packer->unpack($value, false);
        $this->assertArrayHasKey('call', $data);
        $this->assertEquals(1, count($data));
        $this->assertEquals(['call' => 'test'], $data);
    }
}
