<?php

namespace App\Service;

use Minicli\App;
use Minicli\ServiceInterface;

class GithubService implements ServiceInterface
{
    private string $baseUrl;
    private array $headers;

    public function load(App $app)
    {
        $this->baseUrl = 'https://api.github.com/repos/';
        $this->headers[] = 'Accept: application/vnd.github.v3+json';
    }

    public function getCommitList(string $ownerName, string $repoName, int $page = 1, int $perPage = 30): ?array
    {
        $url = $this->baseUrl.$ownerName.'/'.$repoName.'/commits?page='.$page.'&per_page='.$perPage;
        $curlResource = curl_init();
        curl_setopt($curlResource, CURLOPT_URL, $url);
        curl_setopt($curlResource, CURLOPT_HEADER, $this->headers);
        curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlResource, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)');
        $output = curl_exec($curlResource);
        $info = curl_getinfo($curlResource);
        curl_close($curlResource);
        if (false === $output || $info['http_code'] !== 200) {
            return null;
        }
        return json_decode(substr($output, $info["header_size"]));
    }
}
