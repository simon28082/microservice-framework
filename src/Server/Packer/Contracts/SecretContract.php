<?php

namespace CrCms\Microservice\Server\Packer\Contracts;

/**
 * Interface SecretContract
 * @package CrCms\Microservice\Server\Packer\Contracts
 */
interface SecretContract
{
    /**
     * @param array $data
     * @return string
     */
    public function encrypt(array $data): string;

    /**
     * @param string $data
     * @param string $iv
     * @return array
     */
    public function decrypt(string $data, string $iv): array;

    /**
     * @return string
     */
    public function getIv(): string;
}