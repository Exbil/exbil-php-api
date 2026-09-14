<?php

namespace Exbil\ResellingAPI\CloudServices;

use Exbil\ResellingAPI\Client;
use Exbil\ResellingAPI\Exceptions\ApiException;
use GuzzleHttp\Exception\GuzzleException;

class Files
{
    private Client $client;
    private string $basePath = 'v1/products/cloudservices';

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * List files in a directory
     *
     * @param string $uuid Service UUID
     * @param string $dir Directory path
     *
     * @throws ApiException
     * @throws GuzzleException
     */
    public function list(string $uuid, string $dir = '/'): array
    {
        // Daemon expects `?dir=` (see ServerHandler in goagent —
        // anything else returns an empty listing). Earlier SDK
        // builds sent `?directory=` and the customer-facing file
        // manager rendered an empty grid for every directory.
        return $this->client->get("{$this->basePath}/{$uuid}/files/list", [
            'dir' => $dir,
        ]);
    }

    /**
     * Read file contents
     *
     * @param string $uuid Service UUID
     * @param string $file File path
     *
     * @throws ApiException
     * @throws GuzzleException
     */
    public function read(string $uuid, string $file): array
    {
        return $this->client->get("{$this->basePath}/{$uuid}/files/read", [
            'file' => $file,
        ]);
    }

    /**
     * Write content to a file
     *
     * @param string $uuid Service UUID
     * @param string $file File path
     * @param string $content File content
     *
     * @throws ApiException
     * @throws GuzzleException
     */
    public function write(string $uuid, string $file, string $content): array
    {
        return $this->client->post("{$this->basePath}/{$uuid}/files/write", [
            'file' => $file,
            'content' => $content,
        ]);
    }

    /**
     * Upload a file to the service
     *
     * @param string $uuid Service UUID
     * @param string $filePath Local file path to upload
     * @param string $dir Target directory on the service
     *
     * @throws ApiException
     * @throws GuzzleException
     */
    public function upload(string $uuid, string $filePath, string $dir = '/'): array
    {
        return $this->client->request('POST', "{$this->basePath}/{$uuid}/files/upload", [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => basename($filePath),
                ],
                [
                    'name' => 'directory',
                    'contents' => $dir,
                ],
            ],
        ]);
    }

    /**
     * Download a file from the service (returns a download URL / stream info)
     *
     * @param string $uuid Service UUID
     * @param string $file File path
     *
     * @throws ApiException
     * @throws GuzzleException
     */
    public function download(string $uuid, string $file): array
    {
        return $this->client->get("{$this->basePath}/{$uuid}/files/download", [
            'file' => $file,
        ]);
    }

    /**
     * Rename a file or directory without overwriting an existing entry.
     * Both paths must be absolute and within the same directory.
     *
     * @throws ApiException
     * @throws GuzzleException
     */
    public function rename(string $uuid, string $from, string $to): array
    {
        return $this->client->post("{$this->basePath}/{$uuid}/files/rename", [
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Delete files
     *
     * @param string $uuid Service UUID
     * @param array $files Array of file paths to delete
     *
     * @throws ApiException
     * @throws GuzzleException
     */
    public function delete(string $uuid, array $files): array
    {
        return $this->client->post("{$this->basePath}/{$uuid}/files/delete", [
            'files' => $files,
        ]);
    }

    /**
     * Compress files into an archive
     *
     * @param string      $uuid      Service UUID
     * @param array       $files     Array of file paths to compress
     * @param string|null $output    Optional output archive path
     * @param string|null $directory Optional working directory the files are relative to
     *
     * @throws ApiException
     * @throws GuzzleException
     */
    public function compress(string $uuid, array $files, ?string $output = null, ?string $directory = null): array
    {
        $data = ['files' => $files];
        if ($output !== null) {
            $data['output'] = $output;
        }
        if ($directory !== null) {
            $data['directory'] = $directory;
        }
        return $this->client->post("{$this->basePath}/{$uuid}/files/compress", $data);
    }

    /**
     * Decompress an archive
     *
     * @param string      $uuid      Service UUID
     * @param string      $file      Archive file path
     * @param string|null $target    Optional target directory
     * @param string|null $directory Optional working directory the archive is relative to
     *
     * @throws ApiException
     * @throws GuzzleException
     */
    public function decompress(string $uuid, string $file, ?string $target = null, ?string $directory = null): array
    {
        $data = ['file' => $file];
        if ($target !== null) {
            $data['target'] = $target;
        }
        if ($directory !== null) {
            $data['directory'] = $directory;
        }
        return $this->client->post("{$this->basePath}/{$uuid}/files/decompress", $data);
    }
}
