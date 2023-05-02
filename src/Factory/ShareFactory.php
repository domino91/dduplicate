<?php

namespace App\Factory;

use Icewind\SMB\BasicAuth;
use Icewind\SMB\IShare;
use Icewind\SMB\ServerFactory;

class ShareFactory
{
    public function __construct(
        private readonly string $username,
        private readonly string $workgroup,
        private readonly string $password,
        private readonly string $host,
        private readonly string $share,
        private readonly string $defaultDir,
    ) {
    }

    public function create(): IShare
    {
        $serverFactory = new ServerFactory();
        $auth          = new BasicAuth($this->username, $this->workgroup, $this->password);
        $server        = $serverFactory->createServer($this->host, $auth);

        return $server->getShare($this->share);
    }

    public function getDefaultDir(): string
    {
        return $this->defaultDir;
    }
}