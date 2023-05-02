<?php

namespace App\MessageHandler;

use App\Entity\FileHash;
use App\Entity\Job;
use App\Exception\ScanFileException;
use App\Factory\ShareFactory;
use App\Message\ScanMessage;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Icewind\SMB\IFileInfo;
use Icewind\SMB\IShare;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

use function fclose;
use function in_array;

#[AsMessageHandler]
class ScanHandler
{
    private IShare $share;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ShareFactory $shareFactory
    ) {
    }

    public function __invoke(ScanMessage $message)
    {
        /** @var Job $job */
        $job = $this->entityManager->getRepository(Job::class)->find($message->getJobId());
        if (!$job) {
            throw new InvalidArgumentException('Job not found!');
        }
        try {
            $job->setStatus(Job::STATUS_IN_PROGRESS);
            $this->entityManager->flush();

            $this->share = $this->shareFactory->create();
            $this->scan($job->getPath());

            $job->setStatus(Job::STATUS_DONE);
            $this->entityManager->flush();
        } catch (Throwable $throwable) {
            $job->setStatus(Job::STATUS_ERROR);
            $job->setErrorLog($throwable);
            $this->entityManager->flush();
        }
    }

    public function scan(string $path): void
    {
        $content = $this->share->dir($path);

        foreach ($content as $fileInfo) {
            if (!$this->isValid($fileInfo)) {
                continue;
            }

            $fh = $this->share->read($fileInfo->getPath());

            try {
                $content = fread($fh, $fileInfo->getSize());
                $hash    = md5($content);
                $this->persistHash($hash, $fileInfo);
            } catch (Throwable $throwable) {
                throw new ScanFileException(
                    $fileInfo->getName(),
                    $throwable->getMessage(),
                    $throwable->getCode(),
                    $throwable
                );
            } finally {
                fclose($fh);
            }
        }
    }

    private function persistHash(string $hash, IFileInfo $fileInfo)
    {
        $path = $fileInfo->getPath();
        $size = $fileInfo->getSize();

        $fileHash = new FileHash();

        $fileHash->setHash($hash);
        $fileHash->setPath($path);
        $fileHash->setSize($size);
        $fileHash->setCreatedAt(new DateTimeImmutable());

        $this->entityManager->persist($fileHash);
        $this->entityManager->flush();
    }

    private function isValid(IFileInfo $fileInfo): bool
    {
        if ($fileInfo->isSystem()) {
            return false;
        }
        if ($fileInfo->isDirectory()) {
            $this->scan($fileInfo->getPath());

            return false;
        }

        $fileName = $fileInfo->getName();
        if (
            !in_array($this->getExtension($fileName), ['jpeg', 'jpg', 'png', 'gif', 'tiff', 'ico', 'svg', 'bmp']
            )
        ) {
            return false;
        }

        if ($fileInfo->getSize() == 0) {
            return false;
        }

        return true;
    }

    private function getExtension(string $fileName)
    {
        $fileName = strtolower($fileName);
        $n        = strrpos($fileName, ".");
        if ($n === false) {
            return "";
        }

        return substr($fileName, $n + 1);
    }
}